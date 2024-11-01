import { set_selected_sender_id } from './senders_and_datepicker/selected_sender';
import { set_pickup_time } from './senders_and_datepicker/pickup_time';
import { set_validation_passed } from './product_dimensions/validation_passed';
import { set_selected_carrier_id } from './carriers/selected_carrier';

export const reset_all_variables = ( order_id: string ) => {
	set_selected_sender_id( order_id, undefined );
	set_pickup_time( order_id, undefined );
	set_validation_passed( order_id, false );
	set_selected_carrier_id( order_id, undefined );
};
