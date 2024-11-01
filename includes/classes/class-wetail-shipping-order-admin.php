<?php

namespace Wetail\Shipping\Engine;


use WC_Order;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Order_Admin' ) ) {
	return;
}

class Wetail_Shipping_Order_Admin {
	/**
	 * Modify table
	 */
	static public function init() {

		add_action( 'load-edit.php', [ __CLASS__, 'load_edit_action_handler' ] );
		add_action( 'load-woocommerce_page_wc-orders', [ __CLASS__, 'load_edit_action_handler' ], 9999 ); // HPOS.

		/**
		 * @since 0.8.1
		 * @wrike https://www.wrike.com/open.htm?id=1337528076
		 */
		add_filter( 'bulk_actions-edit-shop_order', [ __CLASS__, 'add_bulk_actions' ], 20, 1 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', [ __CLASS__, 'add_bulk_actions' ], 20, 1 ); // HPOS.
		add_filter( 'handle_bulk_actions-edit-shop_order', [ __CLASS__, 'handle_bulk_actions' ], 1, 3 );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', [ __CLASS__, 'handle_bulk_actions' ], 1, 3 ); // HPOS.
	}

	/**
	 * Moved some calls to inner method
	 *
	 * @return void
	 *
	 * @since 0.8.1
	 * @wrike https://www.wrike.com/open.htm?id=1337528076
	 */
	public static function load_edit_action_handler() {
		add_thickbox();

		// Modify columns
		add_filter( 'manage_shop_order_posts_columns', [ __CLASS__, 'add_columns' ] );
		add_filter( 'woocommerce_shop_order_list_table_columns', [ __CLASS__, 'add_columns' ] );

		// Get column content
		add_filter( 'manage_posts_custom_column', [ __CLASS__, 'get_column_content' ], 10, 2 );
		add_action( 'woocommerce_shop_order_list_table_custom_column', [ __CLASS__, 'get_column_content' ], 10, 2 );

		add_action( 'admin_head', [ __CLASS__, 'add_js_templates' ] );
	}

	/**
	 * Handles attempts for custom bulk action
	 * It will always ignore unknown bulk action but throw error for custom we added
	 * It supposed to be processed via AJAX call
	 *
	 * @param $redirect_to string
	 * @param $action string
	 * @param $post_ids array
	 *
	 * @throws \Exception
	 *
	 * @since 0.8.1
	 * @wrike https://www.wrike.com/open.htm?id=1337528076
	 */
	public static function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
		if ( $action !== 'wetail_shipping_print_labels' ) {
			return $redirect_to;
		}
		throw new \Exception( 'Not implemented. You\'re supposed to use AJAX call instead' );
	}

	/**
	 * Adds bulk action to shop order listing
	 *
	 * @param $bulk_array array
	 *
	 * @return array
	 *
	 * @since 0.8.1
	 * @wrike https://www.wrike.com/open.htm?id=1337528076
	 */
	public static function add_bulk_actions( $bulk_array ) {
		$bulk_array[ 'wetail_shipping_print_labels' ] = esc_html__( 'Print labels', 'wetail-shipping' );

		return $bulk_array;
	}

	public static function add_admin_scripts() {
		wp_enqueue_style(
			'jquery-datetimepicker',
			URL . '/assets/css/jquery-ui-timepicker-addon.css',
			[],
			'1.6.3',
		);

		wp_enqueue_script(
			'jquery-datetimepicker',
			URL . '/assets/js/libs/jquery-ui-timepicker-addon.js',
			[ 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ],
			'1.6.3',
		);

		wp_enqueue_style(
			'select2css',
			URL . '/assets/css/select2.css',
			[],
			'4.0.13'
		);

		wp_enqueue_script(
			'select2',
			URL . '/assets/js/libs/select2.min.js',
			[ 'jquery' ],
			'4.0.13',
			false
		);

		wp_enqueue_script(
			'wetail-shipping',
			URL . '/assets/js/admin.js',
			[ 'jquery', 'jquery-datetimepicker', 'jquery-ui-tooltip', 'select2' ],
			VERSION . '-' . filemtime( PATH . '/assets/js/admin.js' ),
			true
		);

		wp_localize_script( 'wetail-shipping', '_wetail_shipping', [
			'i18n'     => [
				'Update & print'                                                                              => esc_html__( 'Update & print', 'wetail-shipping' ),
				'Product'                                                                                     => esc_html__( 'Product', 'wetail-shipping' ),
				'Weight'                                                                                      => esc_html__( 'Weight', 'wetail-shipping' ),
				'Total order weight'                                                                          => esc_html__( 'Total order weight', 'wetail-shipping' ),
				'Overwrite the total weight of the order, if necessary'                                       => esc_html__( 'Overwrite the total weight of the order, if necessary', 'wetail-shipping' ),
				'Quantity'                                                                                    => esc_html__( 'Quantity', 'wetail-shipping' ),
				'Add row'                                                                                     => esc_html__( 'Add row', 'wetail-shipping' ),
				'Enter the ID of the product'                                                                 => esc_html__( 'Enter the ID of the product', 'wetail-shipping' ),
				'Enter the name of the product'                                                               => esc_html__( 'Enter the name of the product', 'wetail-shipping' ),
				'Enter the quantity of the product'                                                           => esc_html__( 'Enter the quantity of the product', 'wetail-shipping' ),
				'The ID of the product is empty. Please try again.'                                           => esc_html__( 'The ID of the product is empty. Please try again.', 'wetail-shipping' ),
				'The name of the product is empty. Please try again.'                                         => esc_html__( 'The name of the product is empty. Please try again.', 'wetail-shipping' ),
				'The quantity of the product is empty. Please try again.'                                     => esc_html__( 'The quantity of the product is empty. Please try again.', 'wetail-shipping' ),
				'The quantity of the product should be a number. Please try again.'                           => esc_html__( 'The quantity of the product should be a number. Please try again.', 'wetail-shipping' ),
				'Please select a return service provided by your parcel delivery company for the order'       => esc_html__( 'Please select a return service provided by your parcel delivery company for the order', 'wetail-shipping' ),
				'Print'                                                                                       => esc_html__( 'Print', 'wetail-shipping' ),
				'Send to client'                                                                              => esc_html__( 'Send to client', 'wetail-shipping' ),
				'OK'                                                                                          => esc_html__( 'OK', 'wetail-shipping' ),
				'Cancel'                                                                                      => esc_html__( 'Cancel', 'wetail-shipping' ),
				'Confirm'                                                                                     => esc_html__( 'Confirm', 'wetail-shipping' ),
				'Continue'                                                                                    => esc_html__( 'Continue', 'wetail-shipping' ),
				'Your order can not be processed. Please enter the weight and/or dimensions of your package.' => esc_html__( 'Your order can not be processed. Please enter the weight and/or dimensions of your package.', 'wetail-shipping' ),
				'Missing account connection'                                                                  => esc_html__( 'Missing account connection', 'wetail-shipping' ),
				'Missing order info'                                                                          => esc_html__( 'Missing order info', 'wetail-shipping' ),
				'Order'                                                                                       => esc_html__( 'Order', 'wetail-shipping' ),
				'Select sender & pickup'                                                                      => esc_html__( 'Select sender & pickup', 'wetail-shipping' ),
				'Please'                                                                                      => esc_html__( 'Please', 'wetail-shipping' ),
				'select a shipping sender'                                                                    => esc_html__( 'select a shipping sender', 'wetail-shipping' ),
				'Sender'                                                                                      => esc_html__( 'Sender', 'wetail-shipping' ),
				'Choose sender'                                                                               => esc_html__( 'Choose sender', 'wetail-shipping' ),
				'Carrier'                                                                                     => esc_html__( 'Carrier', 'wetail-shipping' ),
				'Use mapping'                                                                                 => esc_html__( 'Use mapping', 'wetail-shipping' ),
				'and/or'                                                                                      => esc_html__( 'and/or', 'wetail-shipping' ),
				'schedule pickup'                                                                             => esc_html__( 'schedule pickup', 'wetail-shipping' ),
				'Schedule pickup'                                                                             => esc_html__( 'Schedule pickup', 'wetail-shipping' ),
				'Choose date'                                                                                 => esc_html__( 'Choose date', 'wetail-shipping' ),
				'Time is approximate, please contact carrier for information'                                 => esc_html__( 'Time is approximate, please contact carrier for information', 'wetail-shipping' ),
				'Select return service'                                                                       => esc_html__( 'Select return service', 'wetail-shipping' ),
				'Print shipping label'                                                                        => esc_html__( 'Print shipping label', 'wetail-shipping' ),
				'Print label'                                                                                 => esc_html__( 'Print label', 'wetail-shipping' ),
				'View label'                                                                                  => esc_html__( 'View label', 'wetail-shipping' ),
				'Return'                                                                                      => esc_html__( 'Return', 'wetail-shipping' ),
			],
			'settings' => [
				'enable_manual_shipping_service_selection_override' => wc_string_to_bool( get_option( 'wetail_shipping_enable_manual_shipping_service_selection_override', 'no' ) ),
				'has_multiple_senders'                              => wc_string_to_bool( get_option( 'wetail_shipping_has_multiple_senders', 'no' ) ),
				'book_pickup_manually'                              => wc_string_to_bool( get_option( 'wetail_shipping_book_pickup_manually', 'no' ) ),
				'license_key_is_set'                                => ! empty( get_option( 'wetail_shipping_api_key' ) ),
			],
			'wetail_shipping_nonce' => wp_create_nonce('wetail_shipping_admin_nonce')
		] );

		wp_enqueue_style(
			'wetail-shipping',
			URL . '/assets/css/style.css',
			[],
			VERSION . '-' . filemtime( PATH . '/assets/css/style.css' )
		);

		wp_enqueue_style(
			'wetail-shipping-icons',
			URL . '/assets/fonts/wetail-icons/styles.css',
			[],
			VERSION . '-' . filemtime( PATH . '/assets/fonts/wetail-icons/styles.css' )
		);
	}

	/**
	 * Add columns
	 *
	 * @param array $columns
	 */
	static public function add_columns( $columns = [] ) {
		$columns[ 'wetail_shipping_engine' ] = 'Wetail Shipping';

		return $columns;
	}

	/**
	 * Get column content
	 *
	 * @param string $column
	 * @param integer|WC_Order $order_id
	 */
	static public function get_column_content( $column, $order_id ) {
		if ( is_a( $order_id, 'WC_Order' ) ) {
			$order_id = $order_id->get_id(); // HPOS.
		}
		switch ( $column ) {
			case 'wetail_shipping_engine':
				self::render_shipping_button( $order_id );
				self::render_existing_shipping_pdf_button( $order_id );
				self::render_return_button( $order_id );
				self::render_icon( $order_id );
				self::render_spinner();
				break;
		}
	}

	static private function render_shipping_button( $order_id ) {
		$icon = '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M5.07687 0C4.8325 0 4.63048 0.190938 4.61613 0.435471L4.28875 6.0143H2.30769C2.12361 6.0143 1.95713 6.12395 1.88407 6.29331L0.0379137 10.5727C0.0109582 10.6352 -0.00149755 10.7018 0.000143141 10.7678L0 10.7795V16.7938C0 17.0493 0.206638 17.2564 0.461538 17.2564H17.5385C17.7934 17.2564 18 17.0493 18 16.7938V10.7795L17.9999 10.7679C18.0015 10.7018 17.9891 10.6352 17.9621 10.5727L16.1159 6.29331C16.0429 6.12395 15.8764 6.0143 15.6923 6.0143H13.7112L13.3838 0.435471C13.3694 0.190938 13.1674 0 12.923 0H5.07687ZM13.7655 6.93958L13.8198 7.86485H14.5384C14.7933 7.86485 14.9999 8.07198 14.9999 8.32749C14.9999 8.583 14.7933 8.79013 14.5384 8.79013H13.3846H4.61534H3.23069C2.97579 8.79013 2.76915 8.583 2.76915 8.32749C2.76915 8.07198 2.97579 7.86485 3.23069 7.86485H4.18015L4.23445 6.93958H2.61095L1.16397 10.2937H16.836L15.389 6.93958H13.7655ZM12.4878 0.925277L12.7593 5.55167H5.24057L5.51206 0.925277H12.4878ZM12.8951 7.86485L12.7865 6.0143H5.21342L5.10482 7.86485H12.8951ZM0.923077 16.3311V11.2421H17.0769V16.3311H0.923077ZM8.53849 2.31319C8.53849 2.18544 8.64181 2.08187 8.76926 2.08187L11.7693 2.08187C11.8967 2.08187 12 2.18544 12 2.31319C12 2.44094 11.8967 2.54451 11.7693 2.54451L8.76926 2.54451C8.64181 2.54451 8.53849 2.44094 8.53849 2.31319ZM6.46152 2.08187C6.33407 2.08187 6.23076 2.18544 6.23076 2.31319C6.23076 2.44094 6.33407 2.54451 6.46152 2.54451H7.84614C7.97359 2.54451 8.07691 2.44094 8.07691 2.31319C8.07691 2.18544 7.97359 2.08187 7.84614 2.08187H6.46152ZM8.53849 3.23847C8.53849 3.11071 8.64181 3.00715 8.76926 3.00715L11.7693 3.00715C11.8967 3.00715 12 3.11071 12 3.23847C12 3.36622 11.8967 3.46979 11.7693 3.46979L8.76926 3.46979C8.64181 3.46979 8.53849 3.36622 8.53849 3.23847ZM8.76926 3.93243C8.64181 3.93243 8.53849 4.03599 8.53849 4.16375C8.53849 4.2915 8.64181 4.39507 8.76926 4.39507H11.7693C11.8967 4.39507 12 4.2915 12 4.16375C12 4.03599 11.8967 3.93243 11.7693 3.93243L8.76926 3.93243ZM2.76915 13.4165C3.02405 13.4165 3.23069 13.2094 3.23069 12.9539C3.23069 12.6984 3.02405 12.4912 2.76915 12.4912C2.51425 12.4912 2.30761 12.6984 2.30761 12.9539C2.30761 13.2094 2.51425 13.4165 2.76915 13.4165Z" fill="#2270B1"/>
				</svg>
		';

		$format              = '<a href="#" class="button button-secondary wetail-shipping-engine-button %1$s" ';
		$format              .= 'data-order-id="%2$s" ';
		$format              .= 'data-shipping-service-id="%3$s" ';
		$format              .= 'data-nonce="" ';
		$format              .= 'data-type="%1$s" ';
		$format              .= 'title="%4$s">';
		$format              .= $icon . '</a>';
		$css_class           = 'printShippingLabel';
		$shipping_service_id = '';
		$title               = esc_attr__( 'Print label', 'wetail-shipping' );
		$html                = wp_unslash( sprintf( $format, $css_class, $order_id, $shipping_service_id, $title ) );
		$allowed_html        = array(
			'a'    => array(
				'href'                     => true,
				'title'                    => true,
				'class'                    => true,
				'data-order-id'            => true,
				'data-shipping-service-id' => true,
				'data-type'                => true,
				'data-nonce'               => true,

			),
			'svg'  => array(
				'width'   => true,
				'height'  => true,
				'viewBox' => true,
				'fill'    => true,
				'xmlns'   => true,
			),
			'path' => array(
				'fill-rule' => true,
				'clip-rule' => true,
				'd'         => true,
				'fill'      => true,
			)
		);

		echo wp_kses( $html, $allowed_html );
	}

	static private function render_existing_shipping_pdf_button( $wc_order_id ) {
		$wc_order                = wc_get_order( $wc_order_id );
		$wetail_shipping_has_pdf = ! empty( $wc_order->get_meta( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_SHIPPING_LABEL_GENERATED ) );
		if ( $wetail_shipping_has_pdf ) {
			$icon = '<svg width="16" height="21" viewBox="0 0 16 21" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M0 1.5C0 0.671573 0.671573 0 1.5 0H14.5C15.3284 0 16 0.671573 16 1.5V19.5C16 20.3284 15.3284 21 14.5 21H1.5C0.671573 21 0 20.3284 0 19.5V1.5ZM1.5 1C1.22386 1 1 1.22386 1 1.5V19.5C1 19.7761 1.22386 20 1.5 20H14.5C14.7761 20 15 19.7761 15 19.5V1.5C15 1.22386 14.7761 1 14.5 1H1.5Z" fill="#2270B1"/>
			<rect x="2.5" y="11.5" width="1" height="7" rx="0.5" fill="#2270B1"/>
			<rect x="2.5" y="2.5" width="3" height="1" rx="0.5" fill="#2270B1"/>
			<rect x="6.5" y="2.5" width="7" height="1" rx="0.5" fill="#2270B1"/>
			<rect x="6.5" y="4.5" width="7" height="1" rx="0.5" fill="#2270B1"/>
			<rect x="6.5" y="6.5" width="7" height="1" rx="0.5" fill="#2270B1"/>
			<rect x="0.5" y="9.5" width="15" height="1" fill="#2270B1"/>
			<rect x="4.5" y="11.5" width="1" height="7" rx="0.5" fill="#2270B1"/>
			<rect x="6.5" y="11.5" width="1" height="7" rx="0.5" fill="#2270B1"/>
			<rect x="12.5" y="11.5" width="1" height="7" rx="0.5" fill="#2270B1"/>
			<rect x="8.5" y="11.5" width="3" height="7" rx="0.5" fill="#2270B1"/>
			</svg>
			';

			$format              = '<a href="#" class="button button-secondary wetail-shipping-engine-button %1$s" ';
			$format              .= 'data-order-id="%2$s" ';
			$format              .= 'data-shipping-service-id="%3$s" ';
			$format              .= 'data-nonce="" ';
			$format              .= 'data-type="%1$s" ';
			$format              .= 'title="%4$s">';
			$format              .= $icon . '</a>';
			$css_class           = esc_html( 'printExistingShippingLabel' );
			$shipping_service_id = '';
			$title               = esc_attr__( 'View label', 'wetail-shipping' );
			$html                = wp_unslash( sprintf( $format, $css_class, $wc_order_id, $shipping_service_id, $title ) );
			$allowed_html        = array(
				'a'    => array(
					'href'                     => true,
					'title'                    => true,
					'class'                    => true,
					'data-order-id'            => true,
					'data-shipping-service-id' => true,
					'data-type'                => true,
					'data-nonce'               => true,

				),
				'svg'  => array(
					'width'   => true,
					'height'  => true,
					'viewBox' => true,
					'fill'    => true,
					'xmlns'   => true,
				),
				'path' => array(
					'fill-rule' => true,
					'clip-rule' => true,
					'd'         => true,
					'fill'      => true,
				),
				'rect' => array(
					'x'      => true,
					'y'      => true,
					'width'  => true,
					'height' => true,
					'rx'     => true,
					'fill'   => true,
				)
			);

			echo wp_kses( $html, $allowed_html );
		}
	}

	static private function render_return_button( $order_id ) {
		$icon                = '<svg width="21" height="14" viewBox="0 0 21 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M16.1936 2.95888C16.131 2.95888 16.0685 2.96144 16.0059 2.964L14.6823 0.257127C14.6085 0.104898 14.4571 0.0102339 14.2908 0.0102339H8.93879C8.90876 0.00383772 8.87874 0 8.84621 0C8.81369 0 8.78366 0.00383772 8.75364 0.0102339H3.38166C3.21527 0.0102339 3.06389 0.106177 2.99008 0.257127L0.0463803 6.27467C0.0463803 6.27467 0.0438782 6.28107 0.0426272 6.28491C0.0288657 6.31561 0.0176063 6.34631 0.0101001 6.37957V6.38213C0.00384486 6.41155 0.00134277 6.44225 0.00134277 6.47423V13.551C0.00134277 13.7979 0.197756 13.9987 0.439208 13.9987H17.2345C17.4759 13.9987 17.6723 13.7979 17.6723 13.551V12.5506C19.6014 11.911 21.0013 10.0574 21.0013 7.87372C21.0013 5.16301 18.8445 2.9576 16.1936 2.9576V2.95888ZM9.28408 0.905702H14.0193L15.0889 3.09192C13.5651 3.46034 12.3204 4.56944 11.7399 6.02778H9.28408V0.905702ZM3.65314 0.905702H8.40835V6.02778H1.14605L3.65188 0.905702H3.65314ZM16.7966 13.1045H0.875821V6.92325H11.4772C11.4184 7.23154 11.3858 7.55007 11.3858 7.875C11.3858 10.5857 13.5426 12.7911 16.1936 12.7911C16.3975 12.7911 16.5989 12.777 16.7953 12.7515V13.1045H16.7966ZM17.6723 11.6001C17.3946 11.7153 17.1018 11.801 16.7966 11.8483C16.6002 11.879 16.3988 11.8957 16.1948 11.8957C14.0268 11.8957 12.2628 10.0919 12.2628 7.875C12.2628 7.54751 12.3028 7.22771 12.3754 6.92325C12.4505 6.60855 12.5618 6.30921 12.7044 6.02778C13.2561 4.9417 14.2795 4.14474 15.4943 3.92087C15.7219 3.87865 15.9546 3.85563 16.1936 3.85563C16.2787 3.85563 16.3625 3.85947 16.4463 3.86458C18.4968 3.99762 20.1256 5.74507 20.1256 7.87628C20.1256 9.55848 19.1098 11.0015 17.6723 11.6001Z" fill="#2270B1"/>
									<path d="M17.1694 3.31963C17.0317 3.31963 16.9204 3.43348 16.9204 3.57419C16.9204 3.71491 17.0317 3.82876 17.1694 3.82876C17.9187 3.82876 18.528 4.52339 18.528 5.37664C18.528 6.2299 17.9187 6.92452 17.1694 6.92452H16.395L16.6302 6.1493C16.6602 6.04952 16.6277 5.94079 16.5476 5.87555C16.4675 5.81031 16.3562 5.80135 16.2686 5.8538L13.1485 7.70102C13.0722 7.74707 13.0247 7.83022 13.0247 7.92105C13.0247 8.01188 13.0722 8.09503 13.1485 8.14108L16.2736 9.98063C16.3124 10.0037 16.3549 10.0139 16.3975 10.0139C16.4525 10.0139 16.5076 9.9947 16.5526 9.9576C16.6327 9.89236 16.6652 9.78362 16.6339 9.68384L16.3712 8.82547H17.1719C18.6569 8.82547 19.8641 7.59101 19.8641 6.07255C19.8641 4.55409 18.6569 3.31963 17.1681 3.31963H17.1694Z" fill="#2270B1"/>
									</svg>
		';
		$format              = '<a href="#" class="button button-secondary wetail-shipping-engine-button %1$s" ';
		$format              .= 'data-order-id="%2$s" ';
		$format              .= 'data-shipping-service-id="%3$s" ';
		$format              .= 'data-nonce="" ';
		$format              .= 'data-type="%1$s" ';
		$format              .= 'title="%4$s">';
		$format              .= $icon . '</a>';
		$css_class           = esc_html( 'printReturnLabel' );
		$shipping_service_id = esc_html( '12345' ); // TODO Get shipping service ID
		$title               = esc_attr__( 'Return', 'wetail-shipping' );
		$html                = wp_unslash( sprintf( $format, $css_class, $order_id, $shipping_service_id, $title ) );
		$allowed_html        = array(
			'a'    => array(
				'href'                     => true,
				'title'                    => true,
				'class'                    => true,
				'data-order-id'            => true,
				'data-shipping-service-id' => true,
				'data-type'                => true,
				'data-nonce'               => true,
			),
			'svg'  => array(
				'width'   => true,
				'height'  => true,
				'viewBox' => true,
				'fill'    => true,
				'xmlns'   => true,
			),
			'path' => array(
				'd'    => true,
				'fill' => true,
			),
			'rect' => array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
			)
		);

		echo wp_kses( $html, $allowed_html );
	}

	/**
	 * Render an icon based on the order's shipping status.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return void | string
	 *
	 *
	 * Midified @since 1.0.5: replaced green mark condition with _wetail_shipping_label_printed order meta value
	 * @clickup https://app.clickup.com/t/8694f6tbu
	 */
	static public function render_icon( $order_id, $_wetail_shipping_label_printed_already = false ) {
		$order                          = wc_get_order( $order_id );
		$wetail_shipping_has_error      = wc_string_to_bool( $order->get_meta( 'wetail_shipping_error' ) );
		$wetail_shipping_label_printed  = wc_string_to_bool( $order->get_meta( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_SHIPPING_LABEL_PRINTED ) );

		if ( ! $wetail_shipping_label_printed ){
			$wetail_shipping_label_printed = $order->get_meta( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_SHIPPING_LABEL_DEPRECATED );
		}

		if ( $wetail_shipping_has_error ) {
			$html = '<span class="wetail-shipping-engine-icon wetail-shipping-engine-icon--error" title="' . esc_html__( 'Creation of shipping label failed', 'wetail-shipping' ) . '"><svg width="19" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9.5" cy="10" r="9.5" fill="#C44F35"/><rect x="5.96448" y="4.34314" width="13" height="3" rx="1.5" transform="rotate(45 5.96448 4.34314)" fill="white"/><rect x="3.84314" y="13.5355" width="13" height="3" rx="1.5" transform="rotate(-45 3.84314 13.5355)" fill="white"/></svg></span>';
			if ( ! defined( 'PHPUNIT_TESTSUITE' ) ) {
				echo $html;
			}
			else{
				return $html;
			}
		} elseif ( $wetail_shipping_label_printed ) {
			$html = '<span class="wetail-shipping-engine-icon wetail-shipping-engine-icon--success" title="' . esc_html__( 'Creation of shipping label was successful', 'wetail-shipping' ) . '"><svg width="19" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9.5" cy="10" r="9.5" fill="#49C435"/><path d="M4 10.1724L5.35385 8.96552L8.06154 11.3793L13.4769 5L15 6.03448L8.73846 15H7.38462L4 10.1724Z" fill="white"/></svg></span>';
			if ( ! defined( 'PHPUNIT_TESTSUITE' ) ) {
				echo $html;
			}
			else{
				return $html;
			}
		} elseif ( $_wetail_shipping_label_printed_already ) {
			$html = '<span class="wetail-shipping-engine-icon wetail-shipping-engine-icon--success" title="' . esc_html__( 'Creation of shipping label was successful', 'wetail-shipping' ) . '"><svg width="19" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9.5" cy="10" r="9.5" fill="#49C435"/><path d="M4 10.1724L5.35385 8.96552L8.06154 11.3793L13.4769 5L15 6.03448L8.73846 15H7.38462L4 10.1724Z" fill="white"/></svg></span>';
			if ( ! defined( 'PHPUNIT_TESTSUITE' ) ) {
				echo $html;
			}
			else{
				return $html;
			}
		}
	}


	static private function render_spinner() {
		echo '<div class="spinner" style="display: none"></div>';
	}


	static public function add_js_templates() {
		include_once PATH . '/templates/update-product-dimensions.php';
		include_once PATH . '/templates/no-license.php';
	}
}
