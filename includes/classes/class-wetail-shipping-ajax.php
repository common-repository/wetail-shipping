<?php

namespace Wetail\Shipping\Engine;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Ajax' ) ) {
	return;
}

class Wetail_Shipping_Ajax {

	/**
	 */
	static public function init() {

		add_action( 'wp_ajax_wetail_shipping_update_order_items', [ __CLASS__, 'update_order_items' ] );
		add_action( 'wp_ajax_wetail_shipping_validate_order', [ __CLASS__, 'validate_order' ] );
		add_action( 'wp_ajax_wetail_shipping_get_senders', [ __CLASS__, 'get_senders_ajax' ] );
		add_action( 'wp_ajax_wetail_shipping_get_shipping_label', [ __CLASS__, 'get_label_ajax' ] );
		add_action( 'wp_ajax_wetail_shipping_print_shipping_label', [ __CLASS__, 'print_label_ajax' ] );
		add_action( 'wp_ajax_wetail_shipping_print_return_label', [ __CLASS__, 'print_return_label_ajax' ] );
		add_action( 'wp_ajax_wetail_shipping_send_return_label', [ __CLASS__, 'send_return_label_to_customer_ajax' ] );

		/**
		 * @since 0.8.1
		 * @wrike https://www.wrike.com/open.htm?id=1337528076
		 */
		add_action( 'wp_ajax_wetail_shipping_bulk_print_shipping_labels', [ __CLASS__, 'bulk_print_shipping_labels_ajax' ] );
		add_action( 'wp_ajax_wetail_shipping_client_shipping_methods', [ __CLASS__, 'get_client_shipping_methods_ajax' ] );
	}

	/**
	 * If current user cannot manage woocommerce, it will return error to ajax request
	 *
	 * @return void
	 *
	 * @since 0.8.1
	 */
	private static function ensure_permissions() {
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			self::return_message( true, null, 'Nonce verification failed' );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			self::return_message( true, null, 'Insufficient permissions' );
		}
	}

	/**
	 * Handles bulk print AJAX call
	 *
	 * @since 0.8.1
	 * @wrike https://www.wrike.com/open.htm?id=1337528076
	 */
	public static function bulk_print_shipping_labels_ajax() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}
		#endregion

		$order_ids = $_REQUEST[ 'order_ids' ];
		if ( ! is_array( $order_ids ) ) {
			$order_ids = array( $order_ids );
		}
		//wse_write_log( 'Received request to merge pdfs for following ids: ' . json_encode( $order_ids ) );
		$order_ids = array_filter( $order_ids, function ( $value ) {
			return is_numeric( $value ) && is_int( $value + 0 );
		} );

		if( Wetail_Shipping_Bulk_Controller::unsynced_is_over_limit( $order_ids ) ){
			self::return_message( true, null, 'Number of unsynced orders are over 8. This will require a lot of system resources to complete so action is stopped. Please choose fewer orders to synchronize.' );
		}

		try {
			$merged = Wetail_Shipping_Bulk_Controller::generate_shipping_labels( $order_ids );
			/**
			 * Setting flag that pdf was printed
			 *
			 * @since 1.0.5
			 * @clickup https://app.clickup.com/t/8694f6tbu
			 */
			array_map(function ( $wc_order_id ) {
				self::set_order_printed_meta( $wc_order_id );
			}, $order_ids );

			self::return_message( false, base64_encode( $merged ), null );
		} catch ( \Exception $e ) {
			self::return_message( true, null, 'Failed to complete request: ' . $e->getMessage() );
		}
	}

	/** Adds order meta to WETAIL_SHIPPING_SHIPPING_LABEL_PRINTED key
	 * @since 1.0.5
	 * @clickup https://app.clickup.com/t/8694f6tbu
	 */
	public static function set_order_printed_meta( $wc_order_id ){
		$wc_order = wc_get_order($wc_order_id);
		$is_printed = $wc_order->get_meta( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_SHIPPING_LABEL_PRINTED );

		if ( empty( $is_printed ) ) {
			$wc_order->add_meta_data( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_SHIPPING_LABEL_PRINTED, 'yes', true );
			$wc_order->save();
		}
	}
	/**
	 * Retrieves client shipping methods via an AJAX request.
	 *
	 * @return void
	 */
	public static function get_client_shipping_methods_ajax() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}

		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}
		#endregion

		try {
			$response_data = Wetail_Shipping_Request::get( 'user/shipping_services/' );
			return self::return_data( [ 'data' => $response_data[ 'objects' ] ] );
		} catch ( \Exception $error ) {
			self::return_message( true, null, $error->getMessage() );
		}
	}

	/**
	 * Handler function for getting sanders
	 * @since 0.3.1
	 */
	static public function get_senders_ajax() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}

		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}
		#endregion

		try {
			$response_data = Wetail_Shipping_Request::get( 'senders/public/' );
			return self::return_data( [ 'data' => $response_data[ 'objects' ] ] );
		} catch ( \Exception $error ) {
			self::return_message( true, null, $error->getMessage() );
		}
	}

	/**
	 * Handler function for getting a label
	 *
	 * @param array $columns
	 *
	 * @since 0.3.1
	 */
	static public function get_label_ajax() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}

		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}

		$order_id = sanitize_text_field( $_POST[ 'order_id' ] );

		if ( empty( $order_id ) ) {
			die();
		}

		$message = Wetail_Shipping_Order_Controller::get_label( $order_id );

		if ( $message ) {
			$wc_order = wc_get_order( $order_id );
			$wc_order->add_meta_data( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_SHIPPING_LABEL_PRINTED, 'yes', true );
			$wc_order->save();
			self::return_message( false, $message, null );
		} else {
			self::return_message( true, null, 'Label does not exist' );
		}
	}

	/**
	 * Handler function for printing a label
	 *
	 * @param array $columns
	 *
	 * @since 0.3.1
	 */
	static public function print_label_ajax() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}

		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}
		#endregion


		$order_id = sanitize_text_field( $_POST[ 'order_id' ] );

		if ( empty( $order_id ) ) {
			die();
		}

		$selected_sender_id  = array_key_exists( 'selected_sender_id', $_POST ) ? absint( sanitize_text_field( $_POST[ 'selected_sender_id' ] ) ) : false;
		$pickup_time         = array_key_exists( 'pickup_time', $_POST ) ? sanitize_text_field( $_POST[ 'pickup_time' ] ) : false;
		$shipping_service_id = self::get_selected_carrier_id( sanitize_text_field( $_POST[ 'selected_carrier_id' ] ) );

		$wc_order = wc_get_order( absint( $order_id ) );

		try {

			$response_data = Wetail_Shipping_Order_Controller::generate_label( $wc_order, $selected_sender_id, $pickup_time, $shipping_service_id );

			/**
			 * Adding icon to response
			 * @clickup - https://app.clickup.com/t/8694f6tbu
			 */
			ob_start();
			Wetail_Shipping_Order_Admin::render_icon( $order_id, true );
			$response_data[ 'shipping_label' ][ 'icon_html' ] = ob_get_clean();


			//region CU-8694f6tbu
			/**
			 * Setting flag that pdf was printed
			 *
			 * @since 1.0.5
			 * @clickup https://app.clickup.com/t/8694f6tbu
			 */
			$wc_order->add_meta_data( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_SHIPPING_LABEL_PRINTED, 'yes', true );
			$wc_order->save();
			//endregion

			self::return_data( $response_data[ 'shipping_label' ] );

		} catch ( \Exception $error ) {

			Wetail_Shipping_Order_Controller::handle_sync_error( $wc_order, $error->getMessage() );
			self::return_message( true, null, $error->getMessage() );

		}
	}

	/**
	 *
	 */
	static function get_selected_carrier_id( $shipping_service_id ) {
		if ( empty( $shipping_service_id ) || $shipping_service_id === 'use_mapping' ) {
			$shipping_service_id = false;
		}

		return $shipping_service_id;
	}

	/**
	 * Returns order payload
	 * @return mixed|void
	 * @throws \Exception
	 * @since 0.3.1
	 */
	public static function print_return_label_ajax() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}

		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}
		#endregion

		$order_id            = sanitize_text_field( $_POST[ 'order_id' ] );
		$shipping_service_id = sanitize_text_field( $_POST[ 'shipping_service_id' ] );

		if ( empty( $order_id ) ) {
			wp_send_json_error( [ 'message' => 'No order ID' ] );
		}

		if ( empty( $shipping_service_id ) ) {
			wp_send_json_error( [ 'message' => 'No shipping service ID' ] );
		}

		$wc_order = wc_get_order( absint( $order_id ) );
		try {
			$response_data = Wetail_Shipping_Shipping_Label::create_return_shipping_label( $wc_order, $shipping_service_id );
			self::return_data( $response_data );
		} catch ( \Exception $error ) {
			Wetail_Shipping_Order_Controller::handle_sync_error( $wc_order, $error->getMessage() );
			self::return_message( true, null, $error->getMessage() );
		}
	}

	/**
	 * Creates a return label and sends it to customer
	 * @return mixed|void
	 * @throws \Exception
	 * @since 0.3.1
	 */
	public static function send_return_label_to_customer_ajax() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}

		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}
		#endregion

		$order_id = sanitize_text_field( $_POST[ 'order_id' ] );

		if ( empty( $order_id ) ) {
			wp_send_json_error( [ 'message' => 'No order ID' ] );
		}

		$shipping_service_id = sanitize_text_field( $_POST[ 'shipping_service_id' ] );

		if ( empty( $shipping_service_id ) ) {
			wp_send_json_error( [ 'message' => 'No shipping service ID' ] );
		}

		$wc_order = wc_get_order( absint( $order_id ) );
		try {
			$response_data = Wetail_Shipping_Shipping_Label::create_return_shipping_label( $wc_order, $shipping_service_id );
			$pdf_file_path = Wetail_Shipping_Pdf_File_Controller::save_shipping_label_pdf( $wc_order->get_id(), $response_data[ 'data' ] );
			$success       = Wetail_Shipping_Mailer::send_mail( $wc_order, $pdf_file_path );

			return self::return_message( false, null, esc_html__( 'Return label has been sent to customer', 'wetail-shipping' ) );

		} catch ( \Exception $error ) {
			return self::return_message( true, null, $error->getMessage() );
		}
	}

	/** Function to sanitize updated dimensions
	 *
	 */
	public static function sanitize_updated_dimensions(){
		$sanitized_updated_dimensions = array();
		if ( isset( $_POST['updated_dimensions'] ) && is_array( $_POST['updated_dimensions'] ) ) {
		    foreach ( $_POST['updated_dimensions'] as $key => $dimension ) {
		        if ( is_array( $dimension ) ) {
		            // Sanitize each field within the dimension array
		            $sanitized_dimension = array(
		                'product_id' => intval( $dimension['product_id'] ),
		                'weight'     => floatval( $dimension['weight'] ),
	                    'quantity'   => floatval( $dimension['quantity'] ),
		                'height'     => floatval( $dimension['height'] ),
		                'length'     => floatval( $dimension['length'] ),
		                'width'      => floatval( $dimension['width'] ),
		                'skip_row'   => array_key_exists( 'skip_row', $dimension ) && strtolower($dimension['skip_row']) === 'false'? false : boolval($dimension['skip_row'] )
		            );

		            // Add the sanitized dimension to the sanitized array
		            $sanitized_updated_dimensions[] = $sanitized_dimension;
		        }
			}
		}
		return $sanitized_updated_dimensions;
	}

	/** Update all products in order with weight, height, length and width
	 * @return mixed|void
	 * @throws \Exception
	 * @since 0.3.1
	 */
	public static function update_order_items() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}

		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}
		#endregion

		if ( empty( $_POST[ 'updated_dimensions' ] ) ) {
			die();
		}

		$data        = self::sanitize_updated_dimensions();
		$order_id    = sanitize_text_field( $_POST[ 'order_id' ] );
		$wc_order_id = absint( $order_id );

		$wc_order = wc_get_order( $wc_order_id );

		Wetail_Shipping_Order_Controller::remove_additional_row_meta( $wc_order );
		Wetail_Shipping_Order_Controller::remove_order_weight( $wc_order );

		$total_order_weight = array_key_exists( 'total_order_weight',$_POST ) ? sanitize_text_field( $_POST[ 'total_order_weight' ] ) : false;

		if ( ! empty( $total_order_weight ) ) {
			Wetail_Shipping_Order_Controller::add_order_weight( $wc_order, floatval( wp_unslash( $total_order_weight ) ) );
		}

		$wc_order->save();

		foreach ( $data as $product_data ) {

			if ( $product_data[ 'product_id' ] == 0 ) {
				Wetail_Shipping_Order_Controller::add_additional_row_meta( $wc_order, $product_data );
				continue;
			}
			$wc_product = wc_get_product( $product_data[ 'product_id' ] );

			if ( $product_data[ 'skip_row' ] )  {
				self::add_skip_row_meta( $wc_product, $wc_order_id );
				continue;
			} else {
				self::remove_skip_row_meta( $wc_product, $wc_order_id );
			}
			$product_packing_dimensions = new Wetail_Shipping_Product_Packing_Dimensions( $product_data[ 'product_id' ] );
			$product_packing_dimensions->set_weight( $product_data[ 'weight' ] );
			$product_packing_dimensions->set_height( $product_data[ 'height' ] );
			$product_packing_dimensions->set_length( $product_data[ 'length' ] );
			$product_packing_dimensions->set_width( $product_data[ 'width' ] );
			$product_packing_dimensions->save();
		}

		return self::return_message( false, null, '' );
	}

	/** Returns true if input data has additional row data
	 *
	 * @param $data
	 *
	 * @return bool
	 * @since 0.3.1
	 */
	public static function has_additonal_row( $data ) {
		$product_ids = array_column( $data, 'product_id' );

		return in_array( 0, $product_ids );
	}

	/** Adds meta data to productso that we know that the row should be skipped when sending to API
	 *
	 * @param $wc_product
	 * @param $wc_order_id
	 *
	 * @since 0.3.1
	 */
	public static function add_skip_row_meta( $wc_product, $wc_order_id ) {
		$wc_product->update_meta_data( 'wetail_shipping_order_id_' . $wc_order_id . '_skip_row', true );
		$wc_product->save_meta_data();
	}

	/** Removes meta data
	 *
	 * @param $wc_product
	 * @param $wc_order_id
	 *
	 * @since 0.3.1
	 */
	public static function remove_skip_row_meta( $wc_product, $wc_order_id ) {
		$wc_product->delete_meta_data( 'wetail_shipping_order_id_' . $wc_order_id . '_skip_row' );
		$wc_product->save_meta_data();
	}

	/** Validates an order. Checks if all products in order has weight, height, length and width
	 * @return mixed|void
	 * @throws \Exception
	 * @since 0.3.1
	 */
	public static function validate_order() {
		#region permission check CU-8694mc8br
		if ( ! isset( $_POST[ 'wetail_shipping_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wetail_shipping_nonce' ] ) ), 'wetail_shipping_admin_nonce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ){
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Nonce verification failed',
				] ) );
			}

		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			if( ! defined('PHPUNIT_TESTSUITE') ) {
				die( json_encode( [
					'error'   => true,
					'data'    => null,
					'message' => 'Insufficient permissions',
				] ) );
			}
		}
		#endregion

		$order_id = sanitize_text_field( $_POST[ 'order_id' ] );

		if ( empty( $order_id ) ) {
			die();
		}

		$order_id = absint( $order_id );

		if ( Wetail_Shipping_Utils::weight_is_below_given_minimum( $order_id ) ) {
			return self::return_message( false, null, 'All products are valid' );
		}

		$products_to_validate = self::validate_order_items( $order_id );

		if ( empty( $products_to_validate ) ) {
			return self::return_message( false, null, 'All products are valid' );
		} elseif ( isset( $products_to_validate[ 'ignore_product_dimension' ] ) ) {
			return self::return_message( false, null, 'Dimension validation ignored' );
		} else {
			return self::return_message( true, $products_to_validate );
		}
	}

	/**
	 * @param $is_error
	 * @param null $data
	 * @param null $message
	 *
	 * @return array|void
	 * @since 0.3.1
	 */
	public static function return_message( $is_error, $data = null, $message = null ) {
		$return_data = [
			'error' => $is_error
		];

		if ( ! empty( $data ) ) {
			$return_data[ 'data' ] = $data;
		}

		if ( ! empty( $message ) ) {
			$return_data[ 'message' ] = $message;
		}

		if ( ! defined( 'PHPUNIT_TESTSUITE' ) ) {
			die( wp_json_encode( $return_data ) );
		} else {
			return $return_data;
		}
	}

	/**
	 * @param $data
	 *
	 * @return mixed|void
	 * @since 0.3.1
	 */
	public static function return_data( $data ) {

		if ( ! defined( 'PHPUNIT_TESTSUITE' ) ) {
			die( wp_json_encode( $data ) );
		} else {
			return $data;
		}
	}

	/** checks that all products in order has weight and dimensions. Otherwise it returns the itens that has missing
	 * data
	 *
	 * @param $wc_order_id
	 *
	 * @since 0.3.1
	 */
	public static function validate_order_items( $wc_order_id ) {
		$wc_order                              = wc_get_order( $wc_order_id );
		$order_is_below_given_minimum          = Wetail_Shipping_Utils::weight_is_below_given_minimum( $wc_order_id );
		$manual_order_validation               = wc_string_to_bool( get_option( 'wetail_shipping_manual_order_validation', 'no' ) );
		$ignore_product_dimension_if_not_given = wc_string_to_bool( get_option( 'wetail_shipping_ignore_product_dimension_if_not_given' ) );
		$products                              = [];

		if ( $ignore_product_dimension_if_not_given && ! $manual_order_validation ) {
			$products[ 'ignore_product_dimension' ] = true;

			return $products;
		}

		foreach ( $wc_order->get_items() as $item ) {

			if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
				continue;
			}

			$wc_product                 = $item->get_product();
			$product_packing_dimensions = new Wetail_Shipping_Product_Packing_Dimensions( Wetail_Shipping_Utils::get_product_id( $wc_product ) );
			if ( ! $manual_order_validation ) {

				if ( ! floatval( $product_packing_dimensions->get_weight() ) > 0.0 ) {
					if ( ! $order_is_below_given_minimum ) {
						$products[] = self::get_product_data( $wc_product, $item->get_quantity(), $product_packing_dimensions );
						continue;
					}
				}
				if ( ! floatval( $product_packing_dimensions->get_height() ) > 0.0 ||
				     ! floatval( $product_packing_dimensions->get_length() ) > 0.0 || ! floatval( $product_packing_dimensions->get_width() ) > 0.0 ) {
					if ( ! $ignore_product_dimension_if_not_given ) {
						$products[] = self::get_product_data( $wc_product, $item->get_quantity(), $product_packing_dimensions );
					}
				}
			} else {
				$products[] = self::get_product_data( $wc_product, $item->get_quantity(), $product_packing_dimensions );
			}
		}

		return $products;
	}

	/** Returns product data
	 * @since 0.3.1
	 */
	private static function get_product_data( $wc_product, $quantity, $product_packing_dimensions ) {
		return [
			'product_id'   => $wc_product->get_id(),
			'product_name' => $wc_product->get_name(),
			'quantity'     => $quantity,
			'weight'       => $product_packing_dimensions->get_weight(),
			'height'       => $product_packing_dimensions->get_height(),
			'length'       => $product_packing_dimensions->get_length(),
			'width'        => $product_packing_dimensions->get_width(),
		];
	}
}

