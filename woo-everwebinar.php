<?php
/**
 * Plugin Name: WooCommerce EverWebinar
 * Description: Sell access to your webinars with WooCommerce.
 * Version: 0.1
 * Author: Mikhail Durnev
 * Author URI: https://mikhailroot.ru
 * Copyright: (c) 2020 Mikhail Durnev (email : mikhailD.101@gmail.com; skype: mikhail.root)
 *
 * @package woo-everwebinar;
 */

namespace Woo_EverWebinar;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( __NAMESPACE__ . '\PluginDirPath', __DIR__ );
define( __NAMESPACE__ . '\ProductType', 'everwebinar' );
define( __NAMESPACE__ . '\ProductTypeLabel', __( 'EverWebinar', 'woo-everwebinar' ) );

require_once __DIR__ . '/includes/autoloader.php';

// include http api methods.
require_once __DIR__ . '/includes/wp-everwebinar-api.php';

// functions to work with orders, extract data from webinars etc.
require_once __DIR__ . '/includes/utilities.php';
require_once __DIR__ . '/includes/shortcodes.php';

$admin            = new Product_Admin();
$order_processing = new Order_Processing();
