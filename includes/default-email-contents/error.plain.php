<?php
/**
 * Error default email template as html.
 *
 * @package woo-everwebinar;
 */

defined( 'ABSPATH' ) || exit;
?>
Error while registering Paid user to webinar {webinar_name}

User email
{user_email}


Time of error
{date}

Order ID 
{order_id}

Product ID
{product_id}

Product Name 
{product_name}

ERRORS:
{errors_text}

Arguments:
{arguments_json}

Webinar Object:
{webinar_object_json}