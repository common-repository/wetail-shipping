import $ from 'jquery';
import { print_shipping_label } from './print_shipping_label';
import { cancel_return } from './cancel_return';
import { submit_return_service_form } from './submit_return_service_form';
import { update_product_dimensions } from './product_dimensions/update_product_dimensions';
import { set_carrier_sender_and_pickup_date } from './senders_and_datepicker/set_carrier_sender_and_pickup_date';
import { print_return_label } from './print_return_label';
import { add_a_row_to_dimensions_validation_form } from './product_dimensions/add_a_row_to_dimensions_validation_form';
import { remove_the_row_from_validation_form } from './product_dimensions/remove_the_row_from_validation_form';
import { remove_required_field_from_datepicker } from './senders_and_datepicker/remove_required_field_from_datepicker';
import { change_inputs_required } from './change_inputs_required';
import { calculate_total_weight } from './product_dimensions/calculate_total_weight';
import { toggle_required_dimensions } from './product_dimensions/toggle_required_dimensions';
import { bulk_print } from './bulk_print_labels';

/**
 * Assign events handlers
 *
 * @private
 */
export const assign_events = () => {
	$( document.body )
		.on( 'change', '[name="skip_row"]', change_inputs_required )
		.on( 'change', '.update-product-dimensions [name="skip_row"]', calculate_total_weight )
		.on( 'input', '.update-product-dimensions [name="weight"]', calculate_total_weight )
		.on( 'keyup', '.total-weight__value', toggle_required_dimensions )
		.on( 'change', '.total-weight__value', toggle_required_dimensions )
		.on( 'submit', '.return-service', submit_return_service_form )
		.on( 'click', '.update-product-dimensions__add-row .button', add_a_row_to_dimensions_validation_form )
		.on( 'click', '.update-product-dimensions .remove-row', remove_the_row_from_validation_form )
		.on( 'submit', '.update-product-dimensions', update_product_dimensions )
		.on( 'submit', '.sender-and-pickup-form', set_carrier_sender_and_pickup_date )
		.on( 'click', '.button-cancel', cancel_return )
		.on( 'click', '.printShippingLabel, .printExistingShippingLabel', print_shipping_label )
		.on( 'click', '.printReturnLabel', print_return_label )
		.on( 'submit', '#posts-filter, #wc-orders-filter', bulk_print )
		.on( 'change', '#schedule-pickup', remove_required_field_from_datepicker );
};
