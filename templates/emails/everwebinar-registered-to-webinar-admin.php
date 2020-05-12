<?php
/**
 * Template sent to Admin when user gets registered to webinar with all webinar details.
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/everwebinar-registered-to-webinar-admin.php.
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

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
