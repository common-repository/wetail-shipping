import $ from 'jquery';
import SubmitEvent = JQuery.SubmitEvent;
import { show_pdf_from_response } from './show_pdf_from_response';
import { show_spinner } from './spinner/show_spinner';
import { hide_spinner } from './spinner/hide_spinner';
import { call_ajax } from './call_ajax';
import { reset_all_variables } from './reset_all_variables';

export const submit_return_service_form = ( event: SubmitEvent ) => {
	event.preventDefault();

	const form: HTMLFormElement = event.currentTarget;

	const formData = new FormData( form );

	const order_id = formData.get( 'order_id' ) as string;

	let data: RequestData = {
		action: 'wetail_shipping_print_return_label',
		order_id,
		shipping_service_id: formData.get( 'shipping_service_id' ) as string,
	};

	// @ts-ignore
	tb_remove();

	show_spinner( order_id );

	call_ajax( data )
		.always( () => {
			hide_spinner( order_id );
		} )
		.done( ( response: MyResponse<string> ) => {
			if ( !response ) {
				console.error( 'No return service label response' );
				return;
			}

			if ( response.error ) {
				let error_text = 'Some kind of error occurred on the server when processing the API request.';
				if ( response.message ) {
					error_text += ' Return service response error: ' + response.message;
				}
				console.error( error_text );
				alert( error_text );
				reset_all_variables( order_id );
				return;
			}

			console.info( 'Showing return service PDF' );

			show_pdf_from_response( response );
			reset_all_variables( order_id );
		} );

	return false;
};
