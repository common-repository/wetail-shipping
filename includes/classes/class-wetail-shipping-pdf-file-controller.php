<?php

namespace Wetail\Shipping\Engine;


defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Mailer' ) ) {
	return;
}

class Wetail_Shipping_Pdf_File_Controller {

	/** Saves base64 string to uploads folder
	 * @param $wc_order_id
	 * @param $base64_pdf_string
	 *
	 * @return bool
	 */
	public static function save_shipping_label_pdf( $wc_order_id, $base64_pdf_string ){

		$pdf_binary = base64_decode( $base64_pdf_string );
		$file_name = sprintf('return_label_%d.pdf', $wc_order_id );
		$result = wp_upload_bits( $file_name, null, $pdf_binary );

		if ( ! $result['error'] ) {
			return $result['file'];
		} else {
			return false;
		}
	}


	/**
	 * Merges multiple PDFs into a single PDF.
	 *
	 * @param array $pdfs_binaries An array of binary data of individual PDFs to be merged.
	 *
	 * @return string The binary data of the merged PDF.
	 *
	 * @since 0.8.1
	 * @wrike https://www.wrike.com/open.htm?id=1337528076
	 */
	public static function merge_pdfs( $pdfs_binaries ) {
		include PATH . '/vendor/autoload.php';

		$merger = new \iio\libmergepdf\Merger;
		foreach ( $pdfs_binaries as $pdf ) {
			$merger->addRaw( $pdf );
		}

		return $merger->merge();
	}
}
