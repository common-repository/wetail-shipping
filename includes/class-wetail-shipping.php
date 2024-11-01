<?php /** @noinspection ALL */

namespace Wetail\Shipping\Engine;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping' ) ) {
	return;
}

/**
 * Class Wetail_Shipping
 *
 * Plugin loader
 *
 * @package Wetail\WPTU_Template
 */
final class Wetail_Shipping {

	/**
	 * Load all required classes
	 */
	public static function load() {
		add_action( 'admin_enqueue_scripts', function () {
			Wetail_Shipping_Order_Admin::add_admin_scripts();
		} );

		Wetail_Shipping_Order_Admin::init();
		Wetail_Shipping_Mailer::init();
		Wetail_Shipping_Ajax::init();
		Wetail_Shipping_Order_Controller::init();
		Wetail_Shipping_Settings::init();
	}

}
