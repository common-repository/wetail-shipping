import $ from 'jquery';
import { generate_order_PDFs_preview } from './generate_order_PDFs_preview';

export const show_pdf_from_response = ( response: MyResponse<string> ): void => {
	const width = $( window ).width()! * 0.8,
		height = $( window ).height()! * 0.8;

	if ( response.hasOwnProperty( 'url' ) ) {
		// @ts-ignore
		tb_show( window._wetail_shipping.i18n[ 'Print shipping label' ], response.url + '&TB_iframe=1&width=' + width + '&height=' + height );
	} else {

		let popup = $( '<div></div>' );

		popup.css( 'display', 'none' );

		$( '#wetail-shipping-engine-order-pdf-thickbox' ).remove();

		popup.attr( 'id', 'wetail-shipping-engine-order-pdf-thickbox' );

		if ( response.data ) {
			generate_order_PDFs_preview( response.data, popup );
		}

		$( 'body' ).prepend( popup );

		// @ts-ignore
		tb_show( window._wetail_shipping.i18n[ 'Print shipping label' ], '/?TB_inline&width=' + width + '&height=' + height + '&inlineId=wetail-shipping-engine-order-pdf-thickbox' );
	}

	const TBWindow = $( '.wetail-shipping-engine-order-pdf-iframe-wrapper' ).parents( '#TB_window' );

	TBWindow.wrap( '<div class="tb-wetail-shipping-engine-order-pdf"></div>' );

	$( '#TB_window' ).on( 'tb_unload', function() {
		$( '.tb-wetail-shipping-engine-order-pdf' ).remove();
	} );
};
