<?php
/**
 * Main Plugin's file class, Singleton used to access other parts if necessary.
 *
 * @package woo-everwebinar;
 */

namespace Woo_EverWebinar;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Plugin
 *
 * @package Woo_EverWebinar
 */
class Plugin {

	/**
	 * Instance for singleton.
	 *
	 * @var static|null
	 */
	protected static $instance = null;

	/**
	 * @var Product_Admin
	 */
	public $product_admin;

	/**
	 * @var Order_Processing
	 */
	public $order_processing;


	/**
	 * Plugin constructor.
	 */
	private function __construct() {

		$this->product_admin    = new Product_Admin();
		$this->order_processing = new Order_Processing();

	}

	/**
	 * Below is Singleton specific code.
	 */

	/**
	 * Singleton instance getter.
	 *
	 * @return static
	 */
	public static function get_instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}


	/**
	 * Prevent cloning of singleton.
	 */
	private function __clone() {
	}

	/**
	 * Prevent deserializing to singleton.
	 */
	private function __wakeup() {
	}
}