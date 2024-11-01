<?php

namespace Wetail\Shipping\Engine;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Shipping_Label' ) ) {
	return;
}

class Wetail_Shipping_Shipping_Label {

	/** Converts dimension into m
	 * @param $weight
	 *
	 * @return float|int
	 */
	public static function convert_length_dimension( $value ){
		$dimension_unit = get_option('woocommerce_dimension_unit');

		if ( 'cm' === $dimension_unit ){
			return floatval( $value ) / 100;
		}
		elseif ( 'mm' === $dimension_unit ){
			return floatval( $value ) / 1000;
		}
		return floatval( $value );
	}

	/** Converts weight into kg
	 * @param $weight
	 *
	 * @return float|int
	 */
	public static function convert_weight( $weight ){
		if( 'g' == get_option( 'woocommerce_weight_unit' ) )
			return floatval( $weight ) / 1000;
		return floatval( $weight );
	}

	/**
	 * @param $wc_order
	 * @param $shipping_service_id
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function create_return_shipping_label( $wc_order, $shipping_service_id ){
		$payload = self::format_order_payload( $wc_order, $shipping_service_id );
		$reponse_data  = Wetail_Shipping_Request::post( 'shipments/external/return/', $payload );
		return $reponse_data;
	}

	/** Returns payload for order
	 *
	 * @param /WC_Order $wc_order
	 * @param $shipping_service_id
	 * @param $sender_id
	 * @param $pickup_time
	 *
	 * @return array
	 */
	static function format_order_payload( $wc_order, $shipping_service_id=false, $sender_id=false, $pickup_time=false ) {

		$shipping = $wc_order->get_items( 'shipping' );
		if( ! is_a( $shipping, 'WC_Order_Item_Shipping' ) ){
			$shipping = reset( $shipping );
		}

		$wetail_shipping_order_payload = apply_filters( 'wse_order_payload', [
			'customer'                  => self::format_customer_payload( $wc_order ),
			'external_order_id'         => intval(apply_filters( 'woocommerce_order_number', $wc_order->get_id(), $wc_order )),
			'order_number'              => intval(apply_filters( 'woocommerce_order_number', $wc_order->get_id(), $wc_order )),
			'status'                    => $wc_order->get_status(),
			'datetime_paid'             => substr( $wc_order->get_date_created(), 0, 10 ), # To cut off order time
			'currency'                  => $wc_order->get_currency(),
			'payment_method_id'         => $wc_order->get_payment_method(),
			'payment_method_name'       => $wc_order->get_payment_method_title(),
			'shipping_method_name'      => $wc_order->get_shipping_method(),
			'shipping_method_id'        => $shipping ? $shipping->get_method_id() . '-' . $shipping->get_instance_id() : null,
			'total'                     => $wc_order->get_total(),
			'total_tax'                 => $wc_order->get_total_tax(),
			'total_discount'            => $wc_order->get_total_discount(),
			'weight'                    => Wetail_Shipping_Utils::get_order_weight( $wc_order->get_id() )
		], $wc_order);

		if( defined('PHPUNIT_TESTSUITE') ){
			$wetail_shipping_order_payload['shipping_method_id'] = $shipping->get_method_id();
		}

		if ( $shipping_service_id ){
			$wetail_shipping_order_payload['shipping_service_id'] = $shipping_service_id;
		}

		if ( $sender_id ){
			$wetail_shipping_order_payload['sender_id'] = intval($sender_id);
		}

		if ( $pickup_time ){
			$wetail_shipping_order_payload['pickup_datetime'] = $pickup_time;
		}

		$order_items = [];
		foreach ( $wc_order->get_items() as $item ) {
			$wc_product = wc_get_product( $item->get_product_id() );
			if( boolval( $wc_product->get_meta('wetail_shipping_order_id_' . $wc_order->get_id() .  '_skip_row') ) ){
				continue;
			}

			$order_items[] = self::format_order_item_payload( $item, $wc_order, $wc_product );
		}
		wetail_shipping_write_log("Order items before additional row logic:");
		wetail_shipping_write_log($order_items);
		if( $additonal_row = $wc_order->get_meta( 'wetail_shipping_additional_row' ) ){
			$order_items[] = Wetail_Shipping_Order_Controller::format_fictional_order_item_payload( $additonal_row );
		}

		if ( count( $order_items ) == 0 ){
			throw new \Exception('No orderitems');
		}

		$wetail_shipping_order_payload[ 'order_items' ] = $order_items;

		wetail_shipping_write_log("Order items after additional row logic:");
		wetail_shipping_write_log($order_items);
		return $wetail_shipping_order_payload;
	}

	/** Returns payload for customer
	 *
	 * @param /WC_Order $wc_order
	 *
	 * @return array
	 */
	static function format_customer_payload( $wc_order ) {
		$address = $wc_order->get_address();
		return [
			'billing_email'         => $wc_order->get_billing_email(),
			'billing_first_name'    => ! empty( $wc_order->get_billing_company() ) ? $wc_order->get_billing_company() : $address['first_name'],
			'billing_last_name'     => ! empty( $wc_order->get_billing_company() ) ? '' : $address['last_name'],
			'billing_address_1'     => $wc_order->get_billing_address_1(),
			'billing_address_2'     => $wc_order->get_billing_address_2(),
			'billing_zipcode'       => $wc_order->get_billing_postcode(),
			'billing_city'          => $wc_order->get_billing_city(),
			'billing_state'         => $wc_order->get_billing_state(),
			'billing_country'       => $wc_order->get_billing_country(),
			'billing_phone'         => $wc_order->get_billing_phone(),
			'shipping_first_name'   => ! empty( $wc_order->get_shipping_company() ) ? $wc_order->get_shipping_company() : $wc_order->get_shipping_first_name(),
			'shipping_last_name'    => ! empty( $wc_order->get_shipping_company() ) ? '' :$wc_order->get_shipping_last_name(),
			'shipping_address_1'    => $wc_order->get_shipping_address_1(),
			'shipping_address_2'    => $wc_order->get_shipping_address_2(),
			'shipping_zipcode'      => $wc_order->get_shipping_postcode(),
			'shipping_city'         => $wc_order->get_shipping_city(),
			'shipping_state'        => $wc_order->get_shipping_state(),
			'shipping_country'      => $wc_order->get_shipping_country(),
		];
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
			'weight'              => self::convert_weight( $product_data['weight'] ),
			'width'               => self::convert_length_dimension( $product_data['width'] ),
			'height'              => self::convert_length_dimension( $product_data['height'] ),
			'length'              => self::convert_length_dimension( $product_data['length'] ),
			'external_product_id' => 0
		];
	}

	/**
	 * Returns payload for order_item
	 * @param $wc_order_item
	 * @param $wc_order
	 *
	 * @return array
	 */
	static function format_order_item_payload( $wc_order_item, $wc_order, $wc_product ) {

		$wc_order->get_item_total( $wc_order_item, true, false );
		$subtotal = $wc_order->get_item_subtotal( $wc_order_item, false, false );
		$product_id =  $wc_order_item->get_variation_id() ?  $wc_order_item->get_variation_id() :  $wc_order_item->get_product_id();
		$product_packing_dimensions = new Wetail_Shipping_Product_Packing_Dimensions( $product_id );
		$product_weight = $product_packing_dimensions->get_weight();
		$product_weight = ($product_weight === 0 || $product_weight === null) ? $wc_product->get_weight() : $product_weight;

		return [
			'sku'                 => $wc_product->get_sku(),
			'name'                => $wc_product->get_name(),
			'quantity'            => floatval( $wc_order_item->get_quantity() ),
			'total'               => floatval( $wc_order->get_item_total( $wc_order_item, true, false ) ),
			'subtotal'            => floatval( $subtotal ),
			'price'               => floatval( $subtotal ) / floatval( $wc_order_item->get_quantity() ),
			'weight'              => self::convert_weight( $product_weight ),
			'width'               => self::convert_length_dimension( $product_packing_dimensions->get_width() ),
			'height'              => self::convert_length_dimension( $product_packing_dimensions->get_height() ),
			'length'              => self::convert_length_dimension( $product_packing_dimensions->get_length() ),
			'external_product_id' => $wc_product->get_id()
		];
	}
}
