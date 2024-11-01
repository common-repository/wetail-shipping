<?php

namespace Wetail\Shipping\Engine;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Utils' ) ) {
	return;
}


class Wetail_Shipping_Utils{

	/**
	 * @param /WC_Order_Item_Product $item
	 * @return float|int|string
	 */
	protected static function get_item_weight( $item ){
		$product_id =  $item->get_variation_id() ?  $item->get_variation_id() :  $item->get_product_id();
		$product_packing_dimensions = new Wetail_Shipping_Product_Packing_Dimensions( $product_id );
		$weight = $product_packing_dimensions->get_weight();
		$weight = floatval( str_replace(',', '.', $weight) );

		if( 'g' == get_option( 'woocommerce_weight_unit' ) )
			$weight = $weight / 1000;

		$weight *= $item->get_quantity();
		return $weight;
	}

	/**
	 * @param /WC_Order_Item_Product $item
	 * @return float|int|string
	 */
	protected static function maybe_convert_dimension( $value ){
		$value =  floatval( $value );
		if( 'cm' == get_option( 'woocommerce_dimension_unit' ) )
			$value = $value / 100;

		return $value;
	}

	/**
	 * @param /WC_Order $order
	 * @return float|int
	 */
	public static function get_order_total_weight( $wc_order ){
		$weight = array_reduce(
			$wc_order->get_items(),
			function( $carry, $item ) use ( $wc_order ) {

				if( ! is_a( $item, 'WC_Order_Item_Product' ) ){
					return $carry;
				}

				$product_id =  $item->get_variation_id() ?  $item->get_variation_id() :  $item->get_product_id();
				$wc_product = wc_get_product( $product_id );
				if( boolval( $wc_product->get_meta('wetail_shipping_order_id_' . $wc_order->get_id() .  '_skip_row') ) ){
					return $carry;
				}

				$product_packing_dimensions = new Wetail_Shipping_Product_Packing_Dimensions( $product_id );
				$product_weight = $product_packing_dimensions->get_weight();
				if ( ! $product_weight ){
					$wc_product = wc_get_product( $product_id );
					$product_weight = $wc_product->get_weight();
				}

				$weight = floatval( $product_weight ) * $item->get_quantity();
				$weight = floatval( str_replace(',', '.', $weight) );
				return $carry + $weight;
			}
		);
		//TODO use automatic converter  wc_get_weight( WC()->cart->get_cart_contents_weight(),'kg',get_option( 'woocommerce_weight_unit' ) );

		if( $additional_row = $wc_order->get_meta( 'wetail_shipping_additional_row' ) ){
			$weight += floatval($additional_row['weight']);
		}

		if( 'g' == get_option( 'woocommerce_weight_unit' ) )
			$weight = $weight / 1000;

		return $weight;
	}

	/**
	 * @param /WC_Order $wc_order
	 * @return float|int
	 * @since 0.9
	 */
	public static function get_overridden_order_total_weight( $wc_order ){
		return $wc_order->get_meta( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_ORDER_WEIGHT );
	}

	/**
	 * Returns true if order weight is below template_weight, if template_weight exists
	 */
	public static function weight_is_below_given_minimum( $wc_order_id ) {
		$min_order_weight = get_option( 'wetail_shipping_template_weight', false );
		if ( $min_order_weight ) {
			$wc_order = wc_get_order( $wc_order_id );
			if ( Wetail_Shipping_Utils::get_order_total_weight( $wc_order ) < $min_order_weight ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns order weight BUT takes template weight into account -> it returns template weight if order weight is lower than template weight
	 */
	public static function get_order_weight( $wc_order_id ) {
		$wc_order = wc_get_order( $wc_order_id );
		$min_order_weight = get_option( 'wetail_shipping_template_weight', false );

		$total_weight = Wetail_Shipping_Utils::get_overridden_order_total_weight( $wc_order );
		if ( ! $total_weight ){
			$total_weight = Wetail_Shipping_Utils::get_order_total_weight( $wc_order );
		}

		if ( $min_order_weight ) {
			if ( $total_weight < $min_order_weight ) {
				return $min_order_weight;
			}
		}
		return $total_weight;
	}

	/**
	 * Returns product id if is simple product else variation id
	 */
	public static function get_product_id( $wc_product ) {

		if ( 'variation' === $wc_product->get_type() ) {
			return $wc_product->get_variation_id();
		}
		return $wc_product->get_id();
	}

	/**
	 * Converts an array of base64 strings to an array of binary data.
	 *
	 * @param array $base64Strings An array of base64 strings to convert to binary strings.
	 *
	 * @return array An array of binary data converted from the given base64 strings.
	 *
	 * @since 0.8.1
	 * @wrike https://www.wrike.com/open.htm?id=1337528076
	 */
	public static function base64_array_to_binary_array( array $base64Strings ): array {
		$binaryStrings = [];
		foreach ( $base64Strings as $base64String ) {
			$binaryStrings[] = base64_decode( $base64String );
		}

		return $binaryStrings;
	}
}
