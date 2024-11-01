<?php

namespace Wetail\Shipping\Engine;

use WC_Order;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Mailer' ) ) {
	return;
}

class Wetail_Shipping_Mailer {

	/**
	 * Initializes actions.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'woocommerce_email_order_meta', [ __CLASS__, 'add_tracking_link' ], 10, 1 );
	}


	/**
	 * Adds a tracking link to the order if the order status is configured to be tracked.
	 *
	 * @param WC_Order $order The order object.
	 *
	 * @return void
	 */
	public static function add_tracking_link( $order ) {
		$tracking_link = $order->get_meta( Wetail_Shipping_Order_Controller::WETAIL_SHIPPING_TRACKING_LINK );

		if ( empty( $tracking_link ) ) {
			return;
		}

		$html = '<h2>' . esc_html__( 'Track your parcel', 'wetail-shipping' ) . '</h2>';
		$html .= '<div style="display:block;margin:-10px 0 40px;">';
		$html .= '<a href="' . $tracking_link . '">' . $tracking_link . '</a>';
		$html .= '</div>';

		echo esc_html( apply_filters( 'wetail_shipping_engine_woo_tracking_link', $html, $tracking_link, $order ) );
	}

	/**
	 */
	static public function send_mail( $wc_order, $pdf_shipping_label_file_path ) {

		$wc_order_number = intval( apply_filters( 'woocommerce_order_number', $wc_order->get_id(), $wc_order ) );

		$mailer = WC()->mailer();

		// Get the email header
		$email_heading = esc_html__( 'Return label for orders', 'wetail-shipping' ) . $wc_order_number;#TODO
		$header        = $mailer->email_header( $email_heading );
		$message       = '<p>' . esc_html__( 'Hello! Attached you will find the return label for orders', 'wetail-shipping' ) . $wc_order_number . '</p>';
		$footer        = $mailer->email_footer();
		$email_content = $header . $message . $footer;

		$headers     = array( 'Content-Type: text/html; charset=UTF-8' );
		$attachments = array( $pdf_shipping_label_file_path );

		$result = wp_mail( sanitize_email( $wc_order->get_billing_email() ), $email_heading, $email_content, $headers, $attachments );

		if ( $result ) {
			return true;
		} else {
			return false;
		}
	}
}
