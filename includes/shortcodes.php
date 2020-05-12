<?php
/**
 * Custom Shortcodes.
 *
 * @package woo-everwebinar;
 */

namespace Woo_EverWebinar;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode to echo webinar registration result data for particular order_id if none specified it gets last order with webinar for current user
 *
 * @param array  $atts  Default param to echo default is live_room_url there are also:
 *
 *               webinar_id, user_id, name, email, schedule, date, timezone, live_room_url , replay_room_url, thank_you_url
 *
 *               all those are everwebinar's data for example user_id and webinar_id are id's in everwebinar system not your WordPress|Woocommerce
 *
 *               You can also specify css classes passed in as class attribute as string
 *
 *               You can specify order_id too
 *
 *               Also it supports wrapping some other content for example image or text for link, to place inside of it.
 *
 * @param string $content  Content inside shortcode.
 *
 * @return string
 */
function registration_result_link_and_content_shortcode( $atts, $content = '' ) {

	$defaults = array(
		'param'    => 'live_room_url',
		'order_id' => null,
		'class'    => 'everwebinar_reg_result',
	);

	$atts = wp_parse_args( $atts, $defaults );

	// add default wrapper class for shortcode.
	$atts['class'] = 'everwebinar_reg_result' !== $atts['class'] ?
			'everwebinar_reg_result ' . $atts['param'] . ' ' . $atts['class'] :
			$atts['param'] . ' ' . $atts['class'];

	// if order_id =null lets get current users last order and echo it's last webinar reg data.
	if ( is_null( $atts['order_id'] ) ) {
		$last_webinar_order_id = get_current_user_last_order_id_with_webinar();

		if ( is_null( $last_webinar_order_id ) ) {
			return '';
		}

		$atts['order_id'] = $last_webinar_order_id;
	}

	$reg_results = get_webinar_registration_results_from_order( intval( $atts['order_id'] ) );

	$result = '';

	$content = do_shortcode( $content );// we allow shortcodes inside our shortcode.

	foreach ( $reg_results as $reg_result ) {

			// lets make placeholder replacements in $content.
			$result_content = make_placeholder_replacements_for_webinar( $content, $reg_result );

			$param_to_echo = $reg_result->{$atts['param']};

		if ( $param_to_echo ) {
			// url and span.
			if ( strpos( $atts['param'], '_url' ) === false ) {

				$result = '<div class="' . $atts['class'] . '"><span class="everwebinar_value">' . $param_to_echo . '</span>' . $result_content . '</div>';

			} else {

				$result  = '<a class="' . $atts['class'] . '" href="' . $param_to_echo . '" >';
				$result .= strlen( $result_content ) > 0 ? $result_content : $param_to_echo;
				$result .= '</a>';
			}
		}
	}

	return $result;
}

add_shortcode( 'everwebinar', __NAMESPACE__ . '\registration_result_link_and_content_shortcode' );


/**
 * Same as Above but selects all ever bought webinars and outputs their parameters
 *
 * @param array  $atts  Shortcode attributes.
 * @param string $content Shortcode inner content.
 *
 * @return string
 */
function registration_result_links_and_content_list_shortcode( $atts, $content = '' ) {

	$defaults = array(
		'class' => 'everwebinar_reg_result_list',
	);

	$atts = wp_parse_args( $atts, $defaults );

	$result = '';
	// lets get orders with webinars.
	$orders = get_current_user_orders_with_webinars();
	// $content is a template to output our everwebinar item so lets set it's order_id as needed

	foreach ( $orders as $order ) {
		if ( $order instanceof \WP_Post ) {
			$webinar_item_shortcode_content = $content;
			$webinar_item_shortcode_content = preg_replace( '!(order_id=?[",\']?[\d]*[",\']?)!', ' order_id="' . $order->ID . '" ', $webinar_item_shortcode_content );
			$result                        .= do_shortcode( $webinar_item_shortcode_content );
		}
	}

	// wrap items in list wrapper for easy future stying if needed.
	$result = '<div class="' . esc_attr( $atts['class'] ) . '">' . $result . '</div>';
	return $result;
}

add_shortcode( 'everwebinar-list', __NAMESPACE__ . '\registration_result_links_and_content_list_shortcode' );
