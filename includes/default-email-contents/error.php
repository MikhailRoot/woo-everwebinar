<?php
/**
 * Error default email template as html.
 *
 * @package woo-everwebinar;
 */

defined( 'ABSPATH' ) || exit;
?>
<h2>Error while registering Paid user to webinar {webinar_name}</h2>
<table border="1">
	<tr>
		<td>User email</td>
		<td>{user_email}</td>
	</tr>
	<tr>
		<td>Time of error</td>
		<td>{date}</td>
	</tr>
	<tr>
		<td>Order ID </td>
		<td>{order_id}</td>
	</tr>
	<tr>
		<td>Product ID</td>
		<td>{product_id}</td>
	</tr>
	<tr>
		<td>Product Name </td>
		<td>{product_name}</td>
	</tr>
	<tr>
		<td>ERRORS:</td>
		<td>{errors_html}</td>
	</tr>
	<tr>
		<td>Arguments:</td>
        <td>
            <pre>
{arguments_json}
            </pre>
        </td>
	</tr>
    <tr>
        <td>Webinar Object:</td>
        <td>
            <pre>
{webinar_object_json}
            </pre>
        </td>
    </tr>
</table>
