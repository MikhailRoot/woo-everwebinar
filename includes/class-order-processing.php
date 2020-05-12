<?php

namespace Woo_EverWebinar;

/**
 * Class Order_Processing
 *
 * @package woo-everwebinar;
 */
class Order_Processing {

	/**
	 * Order_Processing constructor.
	 */
	public function __construct() {

		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'autocomplete_orders' ), 5, 2 );

		add_action( 'woocommerce_order_status_completed', array( $this, 'register_client_to_webinar' ), 50, 1 );
	}


	/**
	 * Autocomplete everwebinar orders as they are virtual products.
	 *
	 * @param string $order_status Current Order status.
	 * @param int    $order_id     Order id we process.
	 * @return string
	 */
	public function autocomplete_orders( $order_status, $order_id ) {

		$order = new \WC_Order( $order_id );
		if ( 'processing' === $order_status && ( 'on-hold' === $order->status || 'pending' === $order->status || 'failed' === $order->status ) ) {
			$virtual_order = null;
			if ( count( $order->get_items() ) > 0 ) {
				foreach ( $order->get_items() as $item ) {
					if ( 'line_item' === $item['type'] ) {
						$_product = $item->get_product();
						if ( ProductType === $_product->product_type ) {
							// send email here:) .
							// email is sent by woocommerce in other hook.
							return 'completed';
						}
					}
				}
			}
		}
		return $order_status;
	}


	/**
	 * Registers paid customer order to webinar.
	 *
	 * @param int $order_id  Order id to process.
	 */
	public function register_client_to_webinar( $order_id ) {

		$user_email      = '';
		$user_first_name = '';
		$user_last_name  = '';

		$order = new \WC_Order( $order_id );
		$user  = $order->get_user();

		if ( $user instanceof \WP_User && $user->ID > 0 ) {
			$user_email      = $user->user_email;
			$user_first_name = ! empty( $user->user_firstname ) ? $user->user_firstname : $user->display_name;
			$user_last_name  = ! empty( $user->user_lastname ) ? $user->last_name : '';
		} else {
			// get user data from order. Idea from Sam Krieg to support guests orders.
			$user_email      = $order->get_billing_email();
			$user_first_name = $order->get_billing_first_name();
			$user_last_name  = $order->get_billing_last_name();
		}

		$registration_results = array(); // array to store webinar registration results - in case we have multiple webinars bought in one order.

		$old_registration_results = json_decode( get_post_meta( $order->get_id(), 'everwebinar_registration_result', true ) );

		if ( ! empty( $old_registration_results ) ) {
			/**
			 * Triggers Email to send registered successfully notification.
			 *
			 * @see \Woo_EverWebinar\Emails\Woo_EverWebinar_Registered_Email::trigger();
			 * @see \Woo_EverWebinar\Emails\Woo_EverWebinar_Registered_Email_Admin::trigger();
			 */
			do_action( 'woo_everwebinar_user_registered', $order_id, $order );
			return; // do nothing as otherwise we'll reregister user again.
		}

		if ( count( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
				if ( ! is_object( $item ) ) {
					continue;
				}
				if ( $item->is_type( 'line_item' ) ) {
					$_product = $item->get_product();

					if ( ProductType === $_product->product_type ) {
						// lets register user for webinar and send him access link.
						$everwebinar_api_key = get_option( 'everwebinar_api_key', '' );
						$everwebinar_id      = get_post_meta( $_product->get_id(), 'everwebinar_id', true );

						// get whole webinar object to access it's friendly name to show.
						$webinar_obj  = get_webinar_data( $everwebinar_api_key, $everwebinar_id );
						$webinar_name = isset( $webinar_obj->name ) ? $webinar_obj->name : $_product->get_title();

						// Extract first schedule id - so register to webinar start working.
						$schedule = isset( $webinar_obj->schedules[0]->schedule ) ? $webinar_obj->schedules[0]->schedule : 0;

						// REGISTER user to webinar!
						$webinar_registration = register_user_to_webinar( $everwebinar_api_key, $everwebinar_id, $user_email, $user_first_name, $user_last_name, $schedule );

						if ( is_wp_error( $webinar_registration ) ) {
							/**
							 * Triggers email to be sent to admin upon error.
							 *
							 * @see \Woo_EverWebinar\Emails\Woo_EverWebinar_Error_Email::trigger();
							 */
							do_action(
								'woo_everwebinar_user_registration_failed',
								$order_id,
								$order,
								$_product,
								$webinar_name,
								$webinar_obj,
								$webinar_registration,
								compact( 'everwebinar_api_key', 'everwebinar_id', 'user_email', 'user_first_name', 'user_last_name', 'schedule' )
							);
						} else {
							$webinar_registration->{'webinar_name'} = $webinar_name; // extend stored data with webinar_name.
							$registration_results[]                 = $webinar_registration;
						}
					}
				}
			}
		}

		if ( count( $registration_results ) ) {

			update_post_meta( $order->get_id(), 'everwebinar_registration_result', wp_json_encode( $registration_results ) );

			/**
			 * Triggers Email to send registered successfully notification.
			 *
			 * @see \Woo_EverWebinar\Emails\Woo_EverWebinar_Registered_Email::trigger();
			 * @see \Woo_EverWebinar\Emails\Woo_EverWebinar_Registered_Email_Admin::trigger();
			 */
			do_action( 'woo_everwebinar_user_registered', $order_id, $order );
		}
	}

}
