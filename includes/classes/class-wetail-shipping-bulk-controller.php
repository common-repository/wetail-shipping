<?php

namespace Wetail\Shipping\Engine;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Bulk_Controller' ) ) {
	return;
}

class Wetail_Shipping_Bulk_Controller {

	const MAX_UNSYNCED_ORDERS = 8;


	/** Returns true if number unsynced orders are under limit. This is because sync operation takes about 3 seconds
	 * which will cause timeout if number of unsunced orders are over 8
	 * @param $wc_order_ids
	 * @since 0.9.2
	 * @return bool|void
	 */
	public static function unsynced_is_over_limit( $wc_order_ids ) {
		$num_unsynced = 0;
		foreach ( $wc_order_ids as $wc_order_id ) {
			$shipping_label = new Wetail_Shipping_Local_Shipping_Label( $wc_order_id );
			$label = $shipping_label->get_data();
			if ( empty( $label ) ){
				$num_unsynced++;
			}
		}

		if ( $num_unsynced > self::MAX_UNSYNCED_ORDERS ) {
			return true;
		}
		return false;
	}


	/**
	 * Prints shipping labels for orders in given array. If label exists it returns meta value otherwise order
	 * is synced to Wetail Shipping API
	 *
	 * @since 0.9.2
	 * @wrike https://www.wrike.com/open.htm?id=1337528076
	 */
	public static function generate_shipping_labels( $wc_order_ids ) {
		$shipping_labels = [];
		foreach ( $wc_order_ids as $wc_order_id ) {
			$shipping_label = new Wetail_Shipping_Local_Shipping_Label( $wc_order_id );
			$label = $shipping_label->get_data();
			if ( $label ) {
				$shipping_labels[] = $label;
			} else {
				$wc_order = wc_get_order( $wc_order_id );
				$response = Wetail_Shipping_Order_Controller::generate_label( $wc_order,false, false, false );
				$shipping_labels[] =  $response['shipping_label']['data'];
			}
		}

		$shipping_labels = Wetail_Shipping_Utils::base64_array_to_binary_array( $shipping_labels );
		return Wetail_Shipping_Pdf_File_Controller::merge_pdfs( $shipping_labels );
	}
}
