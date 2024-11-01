<?php

namespace Wetail\Shipping\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}


class Wetail_Shipping_Request {

	const DEV_API_URL = 'https://ws-dev2.wetail.dev/api/';
	const API_URL = 'https://production.ws.wetail.io/api/';


	public static function custom_http_request_timeout( $timeout_value ) {
		return 20;
	}


	public static function get_api_url() {

		if ( defined( 'WETAIL_SHIPPING_TEST' ) ) {
			return self::DEV_API_URL;
		}

		return self::API_URL;
	}

	/**
	 * Make a GET API request to Wetail Shipping Engine
	 *
	 * @param string $path
	 * @param bool $print_error
	 * @param array $data
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function get( $path ) {

		$response = wp_remote_get( esc_url( self::get_api_url() . $path ), array(
			'headers' => self::get_headers(),
			'timeout' => 10
		) );

		if ( is_a( $response, 'WP_Error' ) ) {
			throw new \Exception( esc_html( $response->get_error_message() ) );
		}

		$response_body = wp_remote_retrieve_body( $response );

		// Decode the JSON string into an array
		$decoded_body = json_decode( $response_body, true );

		// Log the decoded data if necessary
		wetail_shipping_write_log( print_r( $decoded_body, true ) );

		// Check the status code and handle errors
		if ( 200 !== intval( $response[ 'response' ][ 'code' ] ) ) {
			throw new \Exception( esc_html( $decoded_body[ 'detail' ] ?? 'Unknown error' ) );
		}

		return $decoded_body; // return the decoded array
	}

	/**
	 * Make a POST API request to Fortnox
	 *
	 * @param string $path
	 * @param array $payload
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function post( $path, $payload = [] ) {

		add_filter( 'http_request_timeout', [
			'Wetail\Shipping\Engine\Wetail_Shipping_Request',
			'custom_http_request_timeout'
		], 10, 1 );

		wetail_shipping_write_log( 'POST: ' . $path );
		wetail_shipping_write_log( $payload );

		$args = [
			'headers'     => self::get_headers(),
			'body'        => html_entity_decode( wp_json_encode( $payload ) ),
			'method'      => 'POST',
			'data_format' => 'body'
		];

		$response = wp_remote_post( esc_url( self::get_api_url() . $path ), $args );

		if ( is_a( $response, 'WP_Error' ) ) {
			throw new \Exception( esc_html( $response->get_error_message() ) );
		}

		$data = json_decode( $response[ 'body' ], true );

		wetail_shipping_write_log( $data );

		if ( 200 !== intval( $response[ 'response' ][ 'code' ] ) ) {
			throw new \Exception( esc_html( $data[ 'message' ] ) );

		}

		return $data;
	}


	/**
	 * Make a PUT API request to Fortnox
	 *
	 * @param string $path
	 * @param array $payload
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function put( $path, $payload = [] ) {
		//NOT IMPLEMENTED YET
	}

	private static function get_headers() {
		return [
			'Access-Token' => get_option( 'wetail_shipping_api_key' ),
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json'
		];
	}
}
