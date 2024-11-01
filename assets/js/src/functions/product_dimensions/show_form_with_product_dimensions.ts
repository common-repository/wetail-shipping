import $ from 'jquery';
import { generate_html_for_form_with_product_dimensions } from './generate_html_for_form_with_product_dimensions';
import { calculate_total_weight } from './calculate_total_weight';
import { init_tooltips } from '../init_tooltips';
import { set_required_element_ids } from './toggle_required_dimensions';
import { close_modal } from '../close_modal';

export const show_form_with_product_dimensions = ( response: MyResponse<ProductDimensionsData[]>, order_id: string, type: string ) => {
	const width = 500,
		height = $( window ).height()! * 0.8;

	const WETAIL_SHIPPING_ENGINE_UPDATE_ORDER_ITEMS_FORM = 'wetail-shipping-engine-update-order-items-form';

	let form_wrapper = $( '<div></div>' );

	form_wrapper.css( 'display', 'none' );

	$( '#' + WETAIL_SHIPPING_ENGINE_UPDATE_ORDER_ITEMS_FORM ).remove();

	form_wrapper.attr( 'id', WETAIL_SHIPPING_ENGINE_UPDATE_ORDER_ITEMS_FORM );

	$( `<p>${ window._wetail_shipping.i18n[ 'Your order can not be processed. Please enter the weight and/or dimensions of your package.' ] }</p>` )
		.appendTo( form_wrapper );

	// generate form markup
	if ( Array.isArray( response.data ) && response.data.length ) {
		const { data } = response;

		const form = generate_html_for_form_with_product_dimensions( data );

		form.attr( 'data-order_id', order_id );
		form.attr( 'data-type', type );

		form.appendTo( form_wrapper );
	}


	$( 'body' ).prepend( form_wrapper );

	// @ts-ignore
	tb_show( window._wetail_shipping.i18n[ 'Missing order info' ] + ' - ' + window._wetail_shipping.i18n[ 'Order' ] + ' #' + order_id, '/?TB_inline&width=' + width + '&height=' + height + '&inlineId=' + WETAIL_SHIPPING_ENGINE_UPDATE_ORDER_ITEMS_FORM );

	calculate_total_weight();

	const TBWindow = $( '.update-product-dimensions' ).parents( '#TB_window' );
	TBWindow.attr( 'data-order_id', order_id );
	TBWindow.wrap( '<div class="tb-window-update-product-dimensions"></div>' );
	// @ts-ignore
	$("#TB_closeWindowButton").off( 'click', tb_remove ).on( 'click', close_modal );

	init_tooltips( '.total-weight__tip' );

	set_required_element_ids();

	$( '#TB_window' ).on( 'tb_unload', function() {
		$( '.tb-window-update-product-dimensions' ).remove();
	} );
};
