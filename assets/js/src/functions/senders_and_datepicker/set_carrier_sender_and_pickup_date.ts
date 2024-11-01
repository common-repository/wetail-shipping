import $ from 'jquery';
import { set_selected_sender_id } from './selected_sender';
import { set_pickup_time } from './pickup_time';
import { set_selected_carrier_id } from '../carriers/selected_carrier';

export const set_carrier_sender_and_pickup_date = ( event: JQuery.SubmitEvent ) => {
	event.preventDefault();
	const form = $( event.currentTarget );
	const order_id = form.data( 'order_id' );

	const carrier_select = form.find( 'select[name="selected-carrier"]' );
	if ( carrier_select.length ) {
		const selected_carrier_id = carrier_select.val() as string;
		set_selected_carrier_id( order_id, selected_carrier_id );
	}

	const sender_select = form.find( 'select[name="selected-sender"]' );
	if ( sender_select.length ) {
		const selected_sender_id = sender_select.val() as string;
		set_selected_sender_id( order_id, selected_sender_id );
	}

	const datepicker_element = form.find( '.sender-and-pickup-form__datepicker' );
	if ( datepicker_element.length ) {
		const selected_date = datepicker_element.val() as string;
		set_pickup_time( order_id, selected_date );
	}

	// @ts-ignore
	tb_remove();

	setTimeout( () => {
		$( `.printShippingLabel[data-order-id="${ order_id }"]` ).trigger( 'click' );
	}, 500 );
};
