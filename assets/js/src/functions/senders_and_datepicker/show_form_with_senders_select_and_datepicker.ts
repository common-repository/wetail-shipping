import $ from 'jquery';
import { get_pickup_time } from './pickup_time';
import { get_senders } from './get_senders';
import { get_selected_sender_id } from './selected_sender';
import { get_carriers } from '../carriers/get_carriers';
import { get_selected_carrier_id } from '../carriers/selected_carrier';
import { close_modal } from '../close_modal';


export const show_form_with_senders_select_and_datepicker = async (
	order_id: string,
	has_multiple_senders: boolean,
	book_pickup_manually: boolean,
	enable_manual_shipping_service_selection_override: boolean,
) => {
	let should_show_the_popup: boolean = false;

	const selected_carrier_id: string | undefined = get_selected_carrier_id( order_id );
	const selected_sender_id: string | undefined = get_selected_sender_id( order_id );
	const pickup_time: string | undefined = get_pickup_time( order_id );

	if ( has_multiple_senders && selected_sender_id && book_pickup_manually && pickup_time && !enable_manual_shipping_service_selection_override ) {
		return should_show_the_popup;
	}

	if ( !book_pickup_manually && has_multiple_senders && selected_sender_id && !enable_manual_shipping_service_selection_override ) {
		return should_show_the_popup;
	}

	if ( !has_multiple_senders && book_pickup_manually && pickup_time && !enable_manual_shipping_service_selection_override ) {
		return should_show_the_popup;
	}

	const WETAIL_SHIPPING_ENGINE_SELECT_SENDER_FORM = 'wetail_shipping_engine_select_sender_form';

	let form_wrapper = $( '<div></div>' );

	form_wrapper.css( 'display', 'none' );

	$( '#' + WETAIL_SHIPPING_ENGINE_SELECT_SENDER_FORM ).remove();

	form_wrapper.attr( 'id', WETAIL_SHIPPING_ENGINE_SELECT_SENDER_FORM );

	const form = $( '<form class="sender-and-pickup-form"></form>' );
	form.attr( 'data-order_id', order_id );

	let form_heading = window._wetail_shipping.i18n[ 'Please' ];

	if ( enable_manual_shipping_service_selection_override && ! selected_carrier_id ) {
		console.info( 'Carrier needs to be selected' );

		let carriers_response;
		try {
			carriers_response = await get_carriers( order_id );
		} catch ( _error ) {
			carriers_response = {
				data: [
					{
						'name': 'DHL Paket',
						'service_id': 'dhl_paket_102',
					},
					{
						'name': 'DHL Paket Export',
						'service_id': 'dhl_paket_export_112',
					},
				],
			};
		}

		if ( carriers_response && !carriers_response.error && carriers_response.data && Array.isArray( carriers_response.data ) ) {

			const { data } = carriers_response;

			const wrapper = $( '<div class="carrier-select-wrapper"></div>' );

			$( `<label for="selected-carrier">${ window._wetail_shipping.i18n[ 'Carrier' ] }</label>` ).appendTo( wrapper );

			const select = $( '<select class="sender-and-pickup-form__select" name="selected-carrier" id="selected-carrier"></select>' );

			$( `<option value="use_mapping" selected>${ window._wetail_shipping.i18n[ 'Use mapping' ] }</option>` ).appendTo( select );

			data.forEach( ( carrier ) => {
				const option = $( '<option></option>' );
				option.attr( 'value', carrier.service_id.toString() );
				option.text( carrier.name );
				option.appendTo( select );
			} );

			select.appendTo( wrapper );

			select.select2();

			$( wrapper ).appendTo( form );

			should_show_the_popup = true;

		} else if ( !carriers_response ) {
			console.error( 'No carriers response' );
		} else if ( carriers_response.error ) {
			console.error( 'Carriers response error:', carriers_response.message );
		} else if ( !carriers_response.data ) {
			console.error( 'Carriers response doesn\'t have "data" property' );
		} else if ( !Array.isArray( carriers_response.data ) ) {
			console.error( 'Carriers response "data" property is not an array' );
		}
	}

	if ( has_multiple_senders && !selected_sender_id ) {
		console.info( 'Sender needs to be selected' );

		form_heading += ' ' + window._wetail_shipping.i18n[ 'select a shipping sender' ];

		const senders_response = await get_senders( order_id );

		if ( senders_response && !senders_response.error && senders_response.data && Array.isArray( senders_response.data ) ) {

			const { data } = senders_response;

			$( `<label for="selected-sender">${ window._wetail_shipping.i18n[ 'Sender' ] }</label>` ).appendTo( form );

			const select = $( '<select class="sender-and-pickup-form__select" name="selected-sender" id="selected-sender" required></select>' );

			$( `<option value="" disabled selected>${ window._wetail_shipping.i18n[ 'Choose sender' ] }</option>` ).appendTo( select );

			data.forEach( ( sender ) => {
				const option = $( '<option></option>' );
				option.attr( 'value', sender.id.toString() );
				option.text( sender.name );
				option.appendTo( select );
			} );

			select.appendTo( form );

			select.select2();

			should_show_the_popup = true;

		} else if ( !senders_response ) {
			console.error( 'No senders response' );
		} else if ( senders_response.error ) {
			console.error( 'Senders response error:', senders_response.message );
		} else if ( !senders_response.data ) {
			console.error( 'Senders response doesn\'t have "data" property' );
		} else if ( !Array.isArray( senders_response.data ) ) {
			console.error( 'Senders response "data" property is not an array' );
		}
	}

	if ( book_pickup_manually && !pickup_time ) {
		console.info( 'Pickup time needs to be selected' );

		if ( has_multiple_senders && !selected_sender_id ) {
			form_heading += ' ' + window._wetail_shipping.i18n[ 'and/or' ];
		}

		form_heading += ' ' + window._wetail_shipping.i18n[ 'schedule pickup' ];

		$( `<p class="schedule-pickup"><input id="schedule-pickup" type="checkbox" checked="checked"/><label for="schedule-pickup">${ window._wetail_shipping.i18n[ 'Schedule pickup' ] }</label></p>` ).appendTo( form );
		const datepicker_element = $( `<input required class="sender-and-pickup-form__datepicker" type="text" placeholder="${ window._wetail_shipping.i18n[ 'Choose date' ] }">` );
		datepicker_element.appendTo( form );
		datepicker_element.datetimepicker(
			{
				minDate: 0,
				dateFormat: 'yy-mm-dd',
				timeFormat: 'HH:mm',
				hourMin: 9,
				hourMax: 18,
				hour: 12,
				minuteMax: 0,
				minute: 0,
				showMinute: false,
			},
		);

		$( `<div>${ window._wetail_shipping.i18n[ 'Time is approximate, please contact carrier for information' ] }</div>` ).appendTo( form );

		should_show_the_popup = window.print_label_pickup_time_need;
	}

	$( `<p>${ form_heading }</p>` ).prependTo( form );

	if ( should_show_the_popup ) {
		$( '<br><br>' ).appendTo( form );
		$( `<div class="sender-and-pickup-form__buttons"><button type="submit" class="button button-primary alignright">${ window._wetail_shipping.i18n[ 'Print shipping label' ] }</button></div>` ).appendTo( form );

		form.appendTo( form_wrapper );

		$( 'body' ).prepend( form_wrapper );

		const width = 500,
			height = $( window ).height()! * 0.8;

		// @ts-ignore
		tb_show( '#' + order_id + ' - ' + window._wetail_shipping.i18n[ 'Select sender & pickup' ], '/?TB_inline&width=' + width + '&height=' + height + '&inlineId=' + WETAIL_SHIPPING_ENGINE_SELECT_SENDER_FORM );
		const TBWindow = $( '.sender-and-pickup-form' ).parents( '#TB_window' );
		TBWindow.attr( 'data-order_id', order_id );
		TBWindow.wrap( '<div class="tb-sender-and-pickup-form"></div>' );
		// @ts-ignore
		$("#TB_closeWindowButton").off( 'click', tb_remove ).on( 'click', close_modal );

		$( '#TB_window' ).on( 'tb_unload', function() {
			$( '.tb-sender-and-pickup-form' ).remove();
		} );
	}

	return should_show_the_popup;
};
