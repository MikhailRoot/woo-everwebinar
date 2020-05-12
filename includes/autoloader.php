<?php
/**
 * Autoloader for our plugin.
 *
 * @package woo-everwebinar
 */

if ( ! function_exists( 'woo_everwebinar_autoloader' ) ) {

	/**
	 * Autoloader function.
	 *
	 * @param string $class_name Fully qualified class name.
	 * @return bool
	 */
	function woo_everwebinar_autoloader( $class_name ) {

		if ( false !== strpos( $class_name, 'Woo_EverWebinar' ) ) {

			$parts = explode( '\\', strtolower( str_replace( '_', '-', $class_name ) ) );

			$class_file = 'class-' . array_pop( $parts ) . '.php';

			$plugin_dir = array_shift( $parts );

			$relative_path_part = implode( DIRECTORY_SEPARATOR, $parts );

			if ( count( $parts ) ) {
				$relative_path_part .= DIRECTORY_SEPARATOR;
			}

			$class_file_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_dir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $relative_path_part . $class_file;

			$class_file_path_direct = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_dir . DIRECTORY_SEPARATOR . $relative_path_part . $class_file;

			if ( file_exists( $class_file_path ) ) {

				require_once $class_file_path;
				return true;
			}

			if ( file_exists( $class_file_path_direct ) ) {

				require_once $class_file_path_direct;
				return true;
			}
		}

		return false;

	}

	spl_autoload_register( 'woo_everwebinar_autoloader' );
}
