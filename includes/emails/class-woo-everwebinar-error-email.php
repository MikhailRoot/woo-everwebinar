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
 * Class Woo_EverWebinar_Error_Email
 *
 * @extends \WC_Email
 */
class Woo_EverWebinar_Error_Email extends WYSIWYG_Email {

	/**
	 * Woo_EverWebinar_Error_Email constructor.
	 */
	public function __construct() {

		/**
		 * Id for our custom email type.
		 */
		$this->id = 'woo_everwebinar__error_email';

		$this->customer_email = false;

		$this->title = __( 'Registered to Webinar Error Email', 'woo-everwebinar' );

		$this->description = __( 'Email which is sent to administrator if registration to EverWebinar fails', 'woo-everwebinar' );

		$this->template_base = PluginDirPath . '/templates/';

		$this->template_html = 'emails/everwebinar-error-email.php';

		$this->template_plain = 'emails/plain/everwebinar-error-email.php';

		$this->placeholders = array(
			'{user_email}'              => '',
			'{order_billing_full_name}' => '',
			'{order_id}'                => '',
			'{errors_html}'             => '',
			'{errors_text}'             => '',
			'{arguments_json}'          => '',
			'{webinar_object_json}'     => '',
			'{webinar_name}'            => '',
			'{date}'                    => '',
			'{product_id}'              => '',
			'{product_name}'            => '',
		);

		/**
		 * Set default recipient as admin_email.
		 */
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

		add_action( 'woo_everwebinar_user_registration_failed', array( $this, 'trigger' ), 10, 7 );

		parent::__construct();

	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'ERROR in Order #{order_id}: {user_email} has failed to register to webinar {webinar_name}', 'woo-everwebinar' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return 'Error while registering to {webinar_name}';
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int               $order_id The order ID.
	 * @param \WC_Order         $order where error happened.
	 * @param \WC_Product       $product product which contains webinar where error happened.
	 * @param string            $webinar_name webinar name where error happened.
	 * @param object| \WP_Error $webinar_obj webinar details object.
	 * @param \WP_Error         $error object as a result of trial to register user to webinar.
	 * @param array             $data Array of Variables used to register user.
	 */
	public function trigger( $order_id, $order, $product, $webinar_name, $webinar_obj, $error, $data ) {
		$this->setup_locale();

		if ( $order_id ) {
			$order = wc_get_order( $order_id );
		}

		if ( $order instanceof \WC_Order ) {

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->object                                    = $order;
				$this->placeholders['{order_id}']                = $order_id;
				$this->placeholders['{order_billing_full_name}'] = $this->object->get_formatted_billing_full_name();
				$this->placeholders['{user_email}']              = $this->object->get_billing_email();

				$full_error = new \WP_Error();

				if ( is_wp_error( $webinar_obj ) ) {
					$full_error->add( $webinar_obj->get_error_code(), $webinar_obj->get_error_message() );
				}

				$full_error->add( $error->get_error_code(), $error->get_error_message() );

				$errors_html = '<ul>';
				$errors_text = '';
				foreach ( $error->get_error_messages() as $message ) {
					$errors_html .= '<li>' . $message . '</li>';
					$errors_text .= $message . "\n";
				}
				$errors_html .= '</ul>';

				$this->placeholders['{errors_html}'] = $errors_html;
				$this->placeholders['{errors_text}'] = $errors_text;

				$this->placeholders['{arguments_json}'] = wp_json_encode( $data, JSON_PRETTY_PRINT );

				$this->placeholders['{webinar_object_json}'] = wp_json_encode( $webinar_obj, JSON_PRETTY_PRINT );

				$this->placeholders['{webinar_name}'] = $webinar_name;
				$this->placeholders['{date}']         = gmdate( 'Y-m-d H:i:s' );
				$this->placeholders['{product_id}']   = $product->get_id();
				$this->placeholders['{product_name}'] = $product->get_title();

				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
			'recipient'          => array(
				'title'       => __( 'Recipient(s)', 'woocommerce' ),
				'type'        => 'text',
				/* translators: %s: admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'woocommerce' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
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
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'main_content'  => $this->format_string( $this->get_option( 'main_content', $this->get_default_main_content_html() ) ),
				'sent_to_admin' => true,
				'plain_text'    => false,
				'email'         => $this,
			),
			'woocommerce/',
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
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'main_content'  => $this->format_string( $this->get_option( 'main_content_plain', $this->get_default_main_content_plain() ) ),
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'         => $this,
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
		include PluginDirPath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'default-email-contents' . DIRECTORY_SEPARATOR . 'error.php';
		return ob_get_clean();
	}

	/**
	 * Loads default template for Plain text version of email.
	 *
	 * @return string
	 */
	private function get_default_main_content_plain() {
		ob_start();
		include PluginDirPath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'default-email-contents' . DIRECTORY_SEPARATOR . 'error.plain.php';
		return ob_get_clean();
	}

}
