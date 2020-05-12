<?php
/**
 * Our EverWebinar settings instantiated in WooCommerce's settings admin UI under Products settings tab.
 *
 * @package woo-everwebinar;
 */

namespace Woo_EverWebinar;

defined( 'ABSPATH' ) || exit();

/**
 * Class Settings
 */
class Settings {

	/**
	 * Settings section id.
	 *
	 * @var string
	 */
	public $id = 'woo_everwebinar';

	/**
	 * Settings Label displayed in Admin.
	 *
	 * @var string
	 */
	public $label = 'EverWebinar';


	/**
	 * Woo_EverWebinar_Settings constructor.
	 */
	public function __construct() {

		$this->label = __( 'EverWebinar', 'woo-everwebinar' );

		add_filter( 'woocommerce_get_sections_products', array( $this, 'add_section' ) );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'add_settings' ), 10, 2 );
	}

	/**
	 * Adds our custom section to Woocommerce.
	 *
	 * @param array $sections
	 * @return array
	 */
	public function add_section( $sections = array() ) {

		$sections[ $this->id ] = $this->label;

		return $sections;
	}

	/**
	 * Add our settings to custom page.
	 *
	 * @param array $settings
	 * @param string $current_section
	 * @return array
	 */
	public function add_settings( $settings = array(), $current_section = '' ) {

		if ( $current_section === $this->id ) {

			$settings = array();

			$settings[] = array(
				'type'  => 'title',
				'title' => __( 'EverWebinar Settings', 'text-domain' ),
				'desc'  => __( 'Set EverWebinar API key, manage email notifications on Emails tab', 'text-domain' ),
				'id'    => $this->id,
			);

			$settings[] = array(
				'name' => __( 'Ever Webinar API key', 'woo-everwebinar' ),
				'type' => 'text',
				'desc' => __( 'Set Api Key to be able to sell webinars', 'woo-everwebinar' ),
				'id'   => 'everwebinar_api_key',
			);

			$settings[] = array(
				'type' => 'sectionend',
				'id'   => $this->id,
			);

		}

		return $settings;
	}
}

