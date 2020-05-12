<?php
/**
 * Template sent to Administrator when registration fails.
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/everwebinar-error-email.php.
 *
 * @package woo-everwebinar;
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
*/
do_action( 'woocommerce_email_header', $email_heading, $email );

/**
 * Output our main content from settings.
 */
echo $main_content;


/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
