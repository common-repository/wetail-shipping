<?php

namespace Wetail\Shipping\Engine;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Settings' ) ) {
	return;
}

class Wetail_Shipping_Settings {
	public static $tab = 'settings_tab_wetail_shipping';

	/**
	 * Bootstrap the class and hooks required actions & filters
	 *
	 * @return void
	 */
	public static function init() {
		if ( is_admin() ) {
			add_filter( 'woocommerce_settings_tabs_array', [ __CLASS__, 'add_settings_tab' ], 50 );
			add_action( 'woocommerce_settings_tabs_' . self::$tab, [ __CLASS__, 'add_settings' ] );
			add_action( 'woocommerce_update_options_' . self::$tab, [ __CLASS__, 'update_settings' ] );
		}
		/**
		 * Migrates settings from old key to new key if already not
		 *
		 * @return void
		 * @since 1.0.0
		 * @clickup https://app.clickup.com/t/8694my0qa
		 */
		add_action('plugins_loaded', [__CLASS__,'plugins_loaded_handler']);
	}

	/**
	 * Migrates settings from old key to new key if already not
	 *
	 * @return void
	 * @since 1.0.0
	 * @clickup https://app.clickup.com/t/8694my0qa
	 */
	public static function plugins_loaded_handler() {
		if ( get_option( 'wetail_shipping_wse_to_wetail_shipping_options_migrated' ) ) {
			//if we did it before we won't do it again
			return;
		}

		$mapping = array(
			'wse_api_key'                                           => 'wetail_shipping_api_key',
			'wse_template_weight'                                   => 'wetail_shipping_template_weight',
			'wse_has_multiple_senders'                              => 'wetail_shipping_has_multiple_senders',
			'wse_book_pickup_manually'                              => 'wetail_shipping_book_pickup_manually',
			'wse_enable_manual_shipping_service_selection_override' => 'wse_enable_manual_shipping_service_selection_override',
			'wse_manual_order_validation'                           => 'wetail_shipping_manual_order_validation',
			'wse_ignore_product_dimension_if_not_given'             => 'wetail_shipping_ignore_product_dimension_if_not_given',
			'wse_print_pdfs_on_status'                              => 'wetail_shipping_print_pdfs_on_status',
		);

		//loop over old and new keys
		foreach ( $mapping as $from => $to ) {
			if ( $value = get_option( $from ) ) { //if there is something
				update_option( $to, $value ); //copy value to new key
				delete_option( $from ); // and delete old key
			}
		}

		//remember we did migration and on what unix time
		update_option( 'wetail_shipping_wse_to_wetail_shipping_options_migrated', time() );
	}


	/**
	 * Add a new settings tab to WooCommerce settings page
	 *
	 * @param array $settings_tabs Array of WooCommerce setting tabs & their labels
	 *
	 * @return array Array of WooCommerce setting tabs & their labels
	 */
	public static function add_settings_tab( $settings_tabs ) {
		$pos = array_search( 'shipping', array_keys( $settings_tabs ) );
		if ( $pos === false ) {
			return $settings_tabs;
		}

		$insert = [ self::$tab => 'Wetail Shipping' ];

		return array_merge(
			array_slice( $settings_tabs, 0, $pos + 1 ),
			$insert,
			array_slice( $settings_tabs, $pos + 1 )
		);
	}


	/**
	 * Uses the WooCommerce admin fields API to output settings via the woocommerce_admin_fields() function.
	 *
	 * @return void
	 */
	public static function add_settings() {
		woocommerce_admin_fields( self::get_settings() );
	}

	/**
	 * Get all the settings for this plugin for woocommerce_admin_fields() function.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$settings = array(
			'section_title'           => array(
				'title' => '',
				'type'  => 'title',
				'desc'  => '',
				'id'    => self::$tab . '_section_title'
			),
			'api_key'                 => array(
				'title' => esc_html__( 'API Key', 'wetail-shipping' ),
				'type'  => 'text',
				'id'    => 'wetail_shipping_api_key',
				'desc'  => sprintf(
					esc_html__( 'The API-key will be emailed to you when you purchase a license at wetail.io. If you haven\'t signed up yet, use this %slink%s.', 'wetail-shipping' ),
				    '<a href="https://wetail.io/integrationer/wetail-shipping/" target="_blank">',
					'</a>'
				),
			),
			'template_weight'         => array(
				'title'             => esc_html__( 'Minimum weight per order', 'wetail-shipping' ),
				'type'              => 'number',
				'id'                => 'wetail_shipping_template_weight',
				'css'               => 'max-width: 128px',
				'desc'              => esc_html__( 'Setting this value will never set the order weight below this threshold. Leave empty to allow any order weight including zero or none.', 'wetail-shipping' ),
				'custom_attributes' => [
					'step' => '0.01',
					'data-unit_html' => '<span class="unit">' . get_option( 'woocommerce_weight_unit' ) . '</span>'
				]
			),
			'has_multiple_senders'    => array(
				'title' => esc_html__( 'Support multiple senders', 'wetail-shipping' ),
				'type'  => 'checkbox',
				'id'    => 'wetail_shipping_has_multiple_senders',
				'desc'  => sprintf(
					esc_html__( 'Allow multiple senders in Wetail shipping. Selecting this option will prompt you for the sender upon every shipment label creation. To create another sender, contact %ssupport@wetail.io%s.', 'wetail-shipping' ),
					'<a href="mailto:support@wetail.io">',
					'</a>'
				)
			),
			'book_pickup_manually'    => array(
				'title' => esc_html__( 'Manual carrier pickups', 'wetail-shipping' ),
				'type'  => 'checkbox',
				'id'    => 'wetail_shipping_book_pickup_manually',
				'desc'  => esc_html__( 'Allow scheduling pickups. Selecting this option will prompt you with an option to order pickup.', 'wetail-shipping' ),
			),
			'select_shipping_method_manually' => array(
				'title' => esc_html__( 'Manual shipping method selection', 'wetail-shipping' ),
				'type'  => 'checkbox',
				'id'    => 'wetail_shipping_enable_manual_shipping_service_selection_override',
				'desc'  => esc_html__( 'Allow manual selection of shipping method.', 'wetail-shipping' ),
			),
			'manual_order_validation' => array(
				'title' => esc_html__( 'Manual order validation', 'wetail-shipping' ),
				'type'  => 'checkbox',
				'id'    => 'wetail_shipping_manual_order_validation',
				'desc'  => esc_html__( 'Selecting this option will prompt you with an additional order confirmation dialog. In this dialog you can change weight and dimensions of each product in the order.', 'wetail-shipping' ),
			),
			'ignore_product_dimension_if_not_given' => array(
				'title' => esc_html__( 'Ignore product dimensions', 'wetail-shipping' ),
				'type'  => 'checkbox',
				'id'    => 'wetail_shipping_ignore_product_dimension_if_not_given',
				'desc'  => esc_html__( 'Selecting this option will ignore any product dimensions when creating a shipment label. Instead it will use the minimum dimensions given by carrier shipping service.', 'wetail-shipping' ),
			),
			'print_pdfs_on_status' => array(
				'title'   => esc_html__( 'Print PDFs automatically', 'wetail-shipping' ),
				'type'    => 'select',
				'id'      => 'wetail_shipping_print_pdfs_on_status',
				'options' => array_merge( array( 'none' => esc_html__( 'Do not print', 'wetail-shipping' ) ), wc_get_order_statuses() ),
			),
			'section_end'                           => array(
				'type' => 'sectionend',
				'id' => self::$tab . '_section_end'
			)
		);

		return apply_filters( self::$tab . '_settings', $settings );
	}

	/**
	 * Uses the WooCommerce options API to save settings via the woocommerce_update_options() function
	 *
	 * @return void
	 */
	public static function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}
}
