import { call_ajax } from './call_ajax';
import { hide_spinner } from './spinner/hide_spinner';
import { show_spinner } from './spinner/show_spinner';
import $ from 'jquery';
import { handle_print_label_success_response } from './handle_print_label_success_response';

export const send_wetail_shipping_print_shipping_label_request = (
	order_id: string,
	selected_carrier_id?: string,
	selected_sender_id?: string,
	pickup_time?: string,
): JQuery.jqXHR<MyResponse<string>> => {
	let data: RequestData = {
		action: 'wetail_shipping_print_shipping_label',
		order_id,
	};

	if ( selected_carrier_id ) {
		data.selected_carrier_id = selected_carrier_id;
	}

	if ( selected_sender_id ) {
		data.selected_sender_id = selected_sender_id;
	}

	if ( pickup_time ) {
		data.pickup_time = pickup_time;
	}

	show_spinner( order_id );

	return call_ajax( data )
		.done( ( response ) => {
			handle_print_label_success_response( response, order_id );
		} )
		.always( () => {
				hide_spinner( order_id );
			},
		);
};
