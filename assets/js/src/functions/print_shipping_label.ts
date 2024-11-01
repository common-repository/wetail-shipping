import $ from 'jquery';
import { send_wetail_shipping_print_shipping_label_request } from './send_wetail_shipping_print_shipping_label_request';
import { show_pdf_from_response } from './show_pdf_from_response';
import { show_form_with_senders_select_and_datepicker } from './senders_and_datepicker/show_form_with_senders_select_and_datepicker';
import { get_selected_sender_id } from './senders_and_datepicker/selected_sender';
import { get_pickup_time } from './senders_and_datepicker/pickup_time';
import { send_get_shipping_label_request } from './send_get_shipping_label_request';
import { validate_the_order } from './product_dimensions/validate_the_order';
import { reset_all_variables } from './reset_all_variables';
import { check_license_key } from './license_key/check_license_key';
import { get_selected_carrier_id } from './carriers/selected_carrier';
import ClickEvent = JQuery.ClickEvent;

export const print_shipping_label = async ( event: ClickEvent ) => {
	event.preventDefault();

	const license_is_ok = check_license_key();

	if ( !license_is_ok ) {
		console.error( 'License key is not set' );
		return;
	}

	const _this = event.currentTarget;
	const order_id: string = $( _this ).data( 'order-id' );
	const type: string = $( _this ).data( 'type' );

	if ( $( _this ).hasClass( 'printExistingShippingLabel' ) ) {
		const existing_shipping_label_response = await send_get_shipping_label_request( order_id );

		if ( !existing_shipping_label_response ) {
			console.error( 'No Existing shipping label response' );
			reset_all_variables( order_id );
			return;
		}

		if ( existing_shipping_label_response.error ) {
			let error_text = 'Some kind of error occurred on the server when processing the API request.';
			if ( existing_shipping_label_response.message ) {
				error_text += ' Existing Shipping label response error: ' + existing_shipping_label_response.message;
			}
			console.error( error_text );
			alert( error_text );
			reset_all_variables( order_id );
			return;
		}

		console.info( 'Showing Existing shipping label PDF' );

		show_pdf_from_response( existing_shipping_label_response );
		reset_all_variables( order_id );
		return;
	}

	console.info( 'Validating order dimensions' );
	const order_dimensions_are_valid = await validate_the_order( order_id, type );

	if ( !order_dimensions_are_valid ) {
		return;
	}

	console.info( 'Checking whether there are multiple senders and whether a manual pickup booking is needed' );

	const { has_multiple_senders, book_pickup_manually, enable_manual_shipping_service_selection_override } = window._wetail_shipping.settings;

	if ( has_multiple_senders || book_pickup_manually || enable_manual_shipping_service_selection_override ) {
		const should_show_the_popup = await show_form_with_senders_select_and_datepicker( order_id, has_multiple_senders, book_pickup_manually, enable_manual_shipping_service_selection_override );
		if ( should_show_the_popup ) {
			return;
		}
	}

	console.info( 'Check for multiple senders and manual pickup is over' );

	console.info( 'Getting shipping label' );

	const shipping_label_response = await send_wetail_shipping_print_shipping_label_request(
		order_id,
		get_selected_carrier_id( order_id ),
		get_selected_sender_id( order_id ),
		get_pickup_time( order_id ),
	);

	if ( !shipping_label_response ) {
		console.error( 'No shipping label response' );
		return;
	}

	if ( shipping_label_response.error ) {
		let error_text = 'Some kind of error occurred on the server when processing the API request.';
		if ( shipping_label_response.message ) {
			error_text += ' Shipping label response error: ' + shipping_label_response.message;
		}
		console.error( error_text );
		alert( error_text );
		reset_all_variables( order_id );
		return;
	}

	console.info( 'Showing shipping label PDF' );

	show_pdf_from_response( shipping_label_response );
	reset_all_variables( order_id );
};
