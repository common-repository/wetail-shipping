import $ from 'jquery';
import ClickEvent = JQuery.ClickEvent;
import { reset_all_variables } from './reset_all_variables';

export const cancel_return = ( event: ClickEvent ) => {
	event.preventDefault();
	const order_id = $( '#TB_ajaxContent' ).find( 'input[name="order_id"]' ).val();
	if ( typeof order_id === 'string' ) {
		reset_all_variables( order_id );
	}
	// @ts-ignore
	tb_remove();
};
