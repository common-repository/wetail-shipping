<?php
/**
 * @package Wetail Shipping
 * @link https://wetail.io/
 * @wordpress-plugin
 * Plugin Name: Wetail Shipping
 * Plugin URI: https://wetail.io/integrationer/wetail-shipping/
 * Description: Wetail Shipping
 * Author: Wetail AB
 * Version: 1.0.5
 * Tested up to: 6.6.2
 * WC tested up to: 9.3.3
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Author URI: https://wetail.io/
 */



namespace Wetail\Shipping\Engine;

defined( 'ABSPATH' ) or die();

/**
 * Local constants
 */
define( __NAMESPACE__ . '\PATH', dirname( __FILE__ ) );
define( __NAMESPACE__ . '\SLUG', 'wetail-shipping' );
define( __NAMESPACE__ . '\INDEX', __FILE__ );
define( __NAMESPACE__ . '\NAME', basename( __DIR__ ) );
define( __NAMESPACE__ . '\PLUGIN_ID', basename( __DIR__ ) . 'plugin.php/' . basename( INDEX ) );
define( __NAMESPACE__ . '\URL', dirname( plugins_url() ) . '/' . basename( dirname( __DIR__ ) ) . '/' . NAME );
define( __NAMESPACE__ . '\VERSION', 0.9 );


/**
 * Autoloader init
 */
require_once "autoload.php";

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'wetail-shipping', false, dirname( plugin_basename( __FILE__ ) ) . '/language' );
} );
/**
 * Load the plugin
 */
Wetail_Shipping::load();

if ( ! function_exists( 'wetail_shipping_write_log' ) ) {
	function wetail_shipping_write_log( $log ) {

		$logger  = wc_get_logger();
		$context = array( 'source' => 'wetail-shipping' );
		if ( is_array( $log ) || is_object( $log ) ) {
			$logger->debug( print_r(  $log, true ), $context );
		} else {
			$logger->debug( esc_html( $log ), $context );
		}
	}
}

// Hook into plugin activation to check for the table
register_activation_hook(__FILE__, __NAMESPACE__ . '\\ws_create_labels_table');

function ws_create_labels_table() {
	global $wpdb;

	// Table name
	$table_name = $wpdb->prefix . 'wetail_shipping_labels';

	// Check if the table exists already
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {

		// SQL to create the table
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            wc_order_id INT(11) NOT NULL,
            date_created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            data MEDIUMTEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		// Load the upgrade functions
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// Execute the table creation
		dbDelta($sql);
	}
}

// Optional: You can also check for the table on every init in case of plugin updates without activation
add_action( 'plugins_loaded', __NAMESPACE__ . '\\ws_create_labels_table');

register_activation_hook(__FILE__, function(){
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . Wetail_Shipping_Product_Packing_Dimensions::TABLE_NAME;

	$sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        wc_product_id int(11) NOT NULL,
        height DECIMAL(10,2),
        width DECIMAL(10,2),
        length DECIMAL(10,2),
        weight DECIMAL(10,2),
    	PRIMARY KEY (id)
    ) $charset_collate;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
});
