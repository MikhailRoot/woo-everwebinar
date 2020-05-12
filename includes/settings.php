<?php
/**
 * Settings layout template.
 *
 * @package woo-everwebinar;
 */

defined( 'ABSPATH' ) || exit;

$default_email_template       = file_get_contents( plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR .'email-templates' . DIRECTORY_SEPARATOR . 'default.php' );
$default_admin_email_template = file_get_contents( plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'email-templates' . DIRECTORY_SEPARATOR . 'default-admin.php' );
?>
<style>
	input[type=text] {width:100%;}
	textarea{width:100%; min-height: 150px}
</style>
<div class="wrap">
	<h2>Webinar Jam sell webinars with WooCommerce settings</h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field( 'update-options' ); ?>

		<table class="form-table" style="width:100%">
			<tr valign="top">
				<th scope="row">Ever Webinar API key</th>
				<td><input type="text" name="everwebinar_api_key" value="<?php echo esc_attr( get_option( 'everwebinar_api_key', '' ) ); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row">Notify Client via email on successfull webinar  user registration?</th>
				<td><input type="checkbox" name="everwebinar_notify_client_on_successfull_registration" <?php echo get_option( 'everwebinar_notify_client_on_successfull_registration', false ) === 'on' ? 'checked' : ''; ?> /></td>
			</tr>
			<tr valign="top">
				<th scope="row">Email subject for successfully paid client notification </th>
				<td><input type="text" name="everwebinar_paid_successfully_email_subject" value="<?php echo esc_attr( get_option( 'everwebinar_paid_successfully_email_subject', 'Successfull webinar registration' ) ); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row">Email template for client purchased webinar with links to participate</th>
				<td>
					<?php wp_editor( get_option( 'everwebinar_paid_successfully_email_template', $default_email_template ), 'everwebinar_paid_successfully_email_template', array( 'textarea_name' => 'everwebinar_paid_successfully_email_template' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Notify admin via email on successfull webinar  user registration?</th>
				<td><input type="checkbox" name="everwebinar_notify_admin_on_successfull_registration" <?php echo get_option( 'everwebinar_notify_admin_on_successfull_registration', false ) === 'on' ? 'checked' : ''; ?> /></td>
			</tr>
			<tr valign="top">
				<th scope="row">Admin notification Email template</th>
				<td>
					<?php wp_editor( get_option( 'everwebinar_paid_successfully_admin_email_template', $default_admin_email_template ), 'everwebinar_paid_successfully_admin_email_template', array( 'textarea_name' => 'everwebinar_paid_successfully_admin_email_template' ) ); ?>
				</td>
			</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options"  value="everwebinar_api_key,everwebinar_paid_successfully_email_template,everwebinar_paid_successfully_email_subject,everwebinar_notify_admin_on_successfull_registration,everwebinar_paid_successfully_admin_email_template,everwebinar_notify_client_on_successfull_registration" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
		</p>

	</form>
</div>
