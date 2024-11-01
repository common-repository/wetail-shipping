<?php

namespace Wetail\Shipping\Engine;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Order_Controller' ) ) {
	return;
}

class Wetail_Shipping_Order_Controller {

	const WETAIL_SHIPPING_SHIPPING_LABEL_DEPRECATED = '_wetail_shipping_label';
	const WETAIL_SHIPPING_ORDER_WEIGHT              = '_wetail_shipping_order_weight';
	const WETAIL_SHIPPING_TRACKING_LINK             = '_wetail_shipping_tracking_link';
	const WETAIL_SHIPPING_SHIPPING_LABEL_GENERATED  = '_wetail_shipping_label_generated';
	const WETAIL_SHIPPING_SHIPPING_LABEL_PRINTED    = '_wetail_shipping_label_printed';

	static public function init() {
		add_action( 'woocommerce_init', [ __CLASS__, 'woocommerce_init_handler' ] );
	}

	/**
	 * @param $wc_order \WC_Order
	 * @param $selected_sender_id int
	 * @param $pickup_time string
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function generate_label( $wc_order, $selected_sender_id, $pickup_time, $shipping_service_id ) {
		$payload = Wetail_Shipping_Shipping_Label::format_order_payload(
			$wc_order,
			false,
			$selected_sender_id,
			$pickup_time
		);

		if ( $shipping_service_id ) {
			$payload[ 'shipping_service_id' ] = $shipping_service_id;
			$response_data                    = Wetail_Shipping_Request::post( 'shipments/external/mapping_override/', $payload );
		} else {
			$response_data = Wetail_Shipping_Request::post( 'shipments/external/regular/', $payload );
		}

		self::maybe_clear_errors( $wc_order );
		self::save_pdf_data( $wc_order->get_id(), $response_data[ 'shipping_label' ][ 'data' ] );
		self::add_order_comment( $wc_order, $response_data[ 'shipping_label' ] );
		self::add_tracking_link( $wc_order, $response_data[ 'shipping_label' ]);

		$wc_order->add_meta_data( self::WETAIL_SHIPPING_SHIPPING_LABEL_GENERATED, true, true );
		$wc_order->save();
		return $response_data;
	}

	/** Adds tracking link and label generated meta.
	 * @since 1.0.5
	 * @param $wc_order
	 * @param $shipment
	 */
	public static function add_tracking_link( $wc_order, $shipment ){

		if ( array_key_exists( 'tracking_url', $shipment[ 'shipment' ] ) && ! empty( $shipment[ 'shipment' ][ 'tracking_url' ] ) ) {
			$wc_order->add_meta_data( self::WETAIL_SHIPPING_TRACKING_LINK, $shipment[ 'shipment' ][ 'tracking_url' ], true );
			$wc_order->save();
		}
	}

	/**
	 * @param $wc_order_id int
	 * @since 1.0.5
	 * @return array|mixed|string
	 */
	public static function get_label( $wc_order_id ) {
		$wetail_shipping_label = new Wetail_Shipping_Local_Shipping_Label( $wc_order_id );

		if ( $wetail_shipping_label->get_data() !== null ){
			return $wetail_shipping_label->get_data();
		}

		$wc_order = wc_get_order( absint( $wc_order_id ) );
		return $wc_order->get_meta( self::WETAIL_SHIPPING_SHIPPING_LABEL_DEPRECATED );
	}

	/**
	 * Handle error
	 *
	 * @param \WC_Order $order
	 * @param string $error
	 */
	static function handle_sync_error( $wc_order, $error ) {
		$wc_order->add_meta_data( 'wetail_shipping_error', 1 );
		$wc_order->save();
		$wc_order->add_order_note( 'Wetail Shipping: Fel vid synkronisering </br>' . $error );
	}

	/**
	 * Handle error
	 *
	 * @param \WC_Order $wc_order
	 * @param array $shipment
	 */
	static function add_order_comment( $wc_order, $shipment ) {
		$shipping_company = explode( '_', $shipment[ 'shipment' ][ 'shipping_service_id' ] )[ 0 ];
		$message = sprintf( '<strong>Wetail Shipping:</strong> Fraktsedel har skapats med %s', ucfirst( esc_html( $shipping_company ) ) );

		if ( array_key_exists( 'tracking_url', $shipment[ 'shipment' ] ) && ! empty( $shipment[ 'shipment' ][ 'tracking_url' ] ) ) {
			wetail_shipping_write_log( $shipment[ 'shipment' ][ 'tracking_url' ] );

			if ( array_key_exists( 'shipping_company_shipment_id', $shipment[ 'shipment' ] ) && ! empty( $shipment[ 'shipment' ][ 'shipping_company_shipment_id' ] ) ) {
				$shipment_id = $shipment[ 'shipment' ][ 'shipping_company_shipment_id' ];
			} else {
				$shipment_id = 'länk';
			}

			$message .= sprintf( '<strong> Spårningslänk:</strong> <a href="%s">%s</a>', esc_html( $shipment[ 'shipment' ][ 'tracking_url' ] ), esc_html( $shipment_id ) );
		}
		if ( array_key_exists( 'booking_number', $shipment[ 'shipment' ] ) && ! empty( $shipment[ 'shipment' ][ 'booking_number' ] ) ) {
			$message .= sprintf( '<strong> Bokningsnummer:</strong> %s', esc_html( $shipment[ 'shipment' ][ 'booking_number' ] ) );
		}

		$wc_order->add_order_note( $message );
	}

	/**
	 * Remove meta indicating errors
	 *
	 * @param \WC_Order $order
	 */
	static function maybe_clear_errors( $wc_order ) {
		$wc_order->delete_meta_data( 'wetail_shipping_error' );
		$wc_order->save_meta_data();
	}

	/**
	 * Saves PDF data to meta table
	 *
	 * @param $wc_order_id int
	 * @param $pdf_data mixed
	 */
	static public function save_pdf_data( $wc_order_id, $pdf_data ) {
		$wetail_shipping_label = new Wetail_Shipping_Local_Shipping_Label( $wc_order_id );
		$wetail_shipping_label->set_data( $pdf_data );
		$wetail_shipping_label->save();
	}

	/** Registers automatic sync hook
	 * @since 0.9.0
	 */
	public static function woocommerce_init_handler() {
		$status_to_sync = get_option( 'wetail_shipping_print_pdfs_on_status', 'none' );
		if ( 'none' !== $status_to_sync ) {
			$status_to_sync = str_replace( 'wc-', '', $status_to_sync );

			if ( 'processing' === $status_to_sync ){
				add_action( 'woocommerce_order_status_processing', [
					__CLASS__,
					'schedule_sync_action'
				], 10, 1 );
			}
			else{
				add_action( 'woocommerce_order_status_' . $status_to_sync, [
					__CLASS__,
					'wc_order_status_set_sync_cb'
				], 10, 1 );
			}
		}
	}

	/**
	 * https://app.clickup.com/t/8694fy1c8
	 * @since 0.9.1
	 */
	public static function schedule_sync_action( $wc_order_id ){
		$param  = [ $wc_order_id ];
		$delay  = 30;
		$ts     = time() + $delay;

		if ( function_exists( 'as_schedule_single_action' ) ){
			return as_schedule_single_action( $ts, 'wse_shipping_label_sync', $param, '_wse' );
		}

		return wp_schedule_single_event( $ts, 'wse_shipping_label_sync', $param );
	}

	/**
	 * https://app.clickup.com/t/8694fy1c8
	 * @since 0.9.1
	 */
	static function trigger_sync( $wc_order_id ){
		self::wc_order_status_set_sync_cb( $wc_order_id );
	}

	/** Callback for syncing an order on order status change
	 * @param $wc_order_id
	 * @since 0.9.0
	 */
	public static function wc_order_status_set_sync_cb( $wc_order_id ) {
		wetail_shipping_write_log("wc_order_status_set_sync_cb");


		if( $label = self::get_label( $wc_order_id ) ){
			wetail_shipping_write_log("get_label " . $wc_order_id);
			return;
		}
		try {
			$wc_order = wc_get_order( $wc_order_id );
			self::generate_label( $wc_order, false, false,false );
		} catch ( \Exception $error ) {
			$wc_order = wc_get_order( $wc_order_id );
			Wetail_Shipping_Order_Controller::handle_sync_error( $wc_order, $error->getMessage() );
		}
	}

	/**
	 * @param $wc_order_id
	 * @param $product_data
	 *
	 * @since 0.8.1
	 */
	public static function remove_order_weight( $wc_order ) {
		$wc_order->delete_meta_data( self::WETAIL_SHIPPING_ORDER_WEIGHT );
	}

	/** Adds weight to items to an order
	 *
	 * @param $order_weight
	 * @param $product_data
	 *
	 * @since 0.3.1
	 */
	public static function add_order_weight( $wc_order, $order_weight ) {
		$wc_order->update_meta_data( self::WETAIL_SHIPPING_ORDER_WEIGHT, $order_weight );
	}

	/**
	 * @param $wc_order_id
	 * @param $product_data
	 *
	 * @since 0.8.1
	 */
	public static function remove_additional_row_meta( $wc_order ) {
		$wc_order->delete_meta_data( 'wetail_shipping_additional_row' );
	}

	/** Adds fictional order items to an order
	 *
	 * @param $wc_order
	 * @param $product_data
	 *
	 * @since 0.3.1
	 */
	public static function add_additional_row_meta( $wc_order, $product_data ) {
		$wc_order->update_meta_data( 'wetail_shipping_additional_row', $product_data );
		$wc_order->save();
	}

	/**
	 * Returns payload for fictional order_item
	 * @param $product_data
	 *
	 * @return array
	 */
	static function format_fictional_order_item_payload( $product_data ) {

		return [
			'sku'                 => 'tmp',
			'name'                => 'tmp',
			'quantity'            => 1,
			'total'               => 0,
			'subtotal'            => 0,
			'price'               => 0,
			'weight'              => Wetail_Shipping_Shipping_Label::convert_weight( $product_data['weight'] ),
			'width'               => Wetail_Shipping_Shipping_Label::convert_length_dimension( $product_data['width'] ),
			'height'              => Wetail_Shipping_Shipping_Label::convert_length_dimension( $product_data['height'] ),
			'length'              => Wetail_Shipping_Shipping_Label::convert_length_dimension( $product_data['length'] ),
			'external_product_id' => 0
		];
	}
}

