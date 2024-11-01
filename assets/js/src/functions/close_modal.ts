import $ from 'jquery';
import { reset_all_variables } from './reset_all_variables';

export const close_modal = () => {
	const order_id = $( '#TB_window' ).data( 'order_id' );
	if ( order_id ) {
		reset_all_variables( order_id );
	}

	// @ts-ignore
	tb_remove();
};
