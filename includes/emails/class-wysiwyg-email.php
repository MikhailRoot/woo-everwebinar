<?php
/**
 * Email Class which implements wysiwyg option field.
 *
 * @package woo-everwebinar
 */

namespace Woo_EverWebinar\Emails;

/**
 * Class WYSIWYG_Email
 *
 * @package woo-everwebinar
 */
class WYSIWYG_Email extends \WC_Email {

	/**
	 * Generate WYSIWYG HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 *
	 * @return string
	 */
	public function generate_wysiwyg_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>">
					<?php echo wp_kses_post( $data['title'] ); ?>
					<?php echo $this->get_tooltip_html( $data ); // phpcs:ignore ?>
				</label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<?php wp_editor( $this->get_option( $key ), $field_key, array( 'textarea_name' => $field_key ) ); ?>
					<?php echo $this->get_description_html( $data ); //phpcs:ignore ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

}
