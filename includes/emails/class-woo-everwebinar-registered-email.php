<?php
/**
 * Email Sent to customer when he gets registered to webinar.
 *
 * @package woo-everwebinar;
 */

namespace Woo_EverWebinar\Emails;

use function Woo_EverWebinar\get_webinar_registration_results_from_order;
use const Woo_EverWebinar\PluginDirPath;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( '\WC_Email', false ) ) {
	return;
}

/**
 * Class Woo_EverWebinar_Registered_Email
 *
 * @extends \WC_Email
 */
class Woo_EverWebinar_Registered_Email extends WYSIWYG_Email {

	/**
	 * Woo_EverWebinar_Registered_Email constructor.
	 */
	public function __construct() {

		/**
		 * Id for our custom email type.
		 */
		$this->id = 'woo_everwebinar__registered_to_webinar';

		$this->customer_email = true;

		$this->title = __( 'Registered to Webinar', 'woo-everwebinar' );

		$this->description = __( 'Email which is sent to customer with his access links to webinars after registration', 'woo-everwebinar' );

		$this->template_base = PluginDirPath . '/templates/';

		$this->template_html = 'emails/everwebinar-registered-to-webinar.php';

		$this->template_plain = 'emails/plain/everwebinar-registered-to-webinar.php';

		$this->placeholders = array(
			'{webinar_name}'    => '',
			'{date}'            => '',
			'{timezone}'        => '',
			'{live_room_url}'   => '',
			'{replay_room_url}' => '',
			'{thank_you_url}'   => '',
		);

		add_action( 'woo_everwebinar_user_registered', array( $this, 'trigger' ), 10, 2 );

		parent::__construct();

	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( '[{site_title}]: You have registered to webinar {webinar_name}', 'woo-everwebinar' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return '{webinar_name}';
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int             $order_id The order ID.
	 * @param \WC_Order|false $order Order object.
	 */
	public function trigger( $order_id, $order = false ) {
		$this->setup_locale();

		if ( $order_id && ! $order instanceof \WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( $order instanceof \WC_Order ) {
			$this->object                                    = $order;
			$this->recipient                                 = $this->object->get_billing_email();
			$this->placeholders['{order_date}']              = wc_format_datetime( $this->object->get_date_created() );
			$this->placeholders['{order_number}']            = $this->object->get_order_number();
			$this->placeholders['{order_billing_full_name}'] = $this->object->get_formatted_billing_full_name();

			if ( $this->is_enabled() && $this->get_recipient() ) {

				$reg_results = get_webinar_registration_results_from_order( $order );

				foreach ( $reg_results as $reg_result ) {
					foreach ( (array) $reg_result as $key => $value ) {
						$this->placeholders[ '{' . $key . '}' ] = $value;
					}

					$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
				}
			}
		}

		$this->restore_locale();
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		/* translators: %s: list of placeholders */
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
		$this->form_fields = array(
			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'woocommerce' ),
				'default' => 'yes',
			),
			'subject'            => array(
				'title'       => __( 'Subject', 'woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'            => array(
				'title'       => __( 'Email heading', 'woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'main_content'       => array(
				'title'       => __( 'Content of message', 'woo-everwebinar' ),
				'description' => __( 'Main email content.', 'woo-everwebinar' ) . ' ' . $placeholder_text,
				'css'         => 'height: 100px;',
				'placeholder' => '',
				'type'        => 'wysiwyg',
				'default'     => $this->get_default_main_content_html(),
				'desc_tip'    => true,
			),
			'main_content_plain' => array(
				'title'       => __( 'Content of message in Plain Text', 'woo-everwebinar' ),
				'description' => __( 'Main email content in form of Plain text', 'woo-everwebinar' ) . ' ' . $placeholder_text,
				'css'         => 'height: 100px;',
				'placeholder' => '',
				'type'        => 'textarea',
				'default'     => $this->get_default_main_content_plain(),
				'desc_tip'    => true,
			),
			'additional_content' => array(
				'title'       => __( 'Additional content', 'woocommerce' ),
				'description' => __( 'Text to appear below the main email content.', 'woocommerce' ) . ' ' . $placeholder_text,
				'css'         => 'width:400px; height: 150px;',
				'placeholder' => __( 'N/A', 'woocommerce' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_additional_content(),
				'desc_tip'    => true,
			),
			'email_type'         => array(
				'title'       => __( 'Email type', 'woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'main_content'       => $this->format_string( $this->get_option( 'main_content', $this->get_default_main_content_html() ) ),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			),
			'woocommerce/',
//			$this->template_base . $this->template_html,
			$this->template_base,
		);
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'main_content'       => $this->format_string( $this->get_option( 'main_content_plain', $this->get_default_main_content_plain() ) ),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			),
			'woocommerce/',
			$this->template_base,
		);
	}

	/**
	 * Loads default template for html version of email.
	 *
	 * @return string
	 */
	private function get_default_main_content_html() {
		ob_start();
		include PluginDirPath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'default-email-contents' . DIRECTORY_SEPARATOR . 'main-customer.php';
		return ob_get_clean();
	}

	/**
	 * Loads default template for Plain text version of email.
	 *
	 * @return string
	 */
	private function get_default_main_content_plain() {
		ob_start();
		include PluginDirPath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'default-email-contents' . DIRECTORY_SEPARATOR . 'main-customer.plain.php';
		return ob_get_clean();
	}

}
