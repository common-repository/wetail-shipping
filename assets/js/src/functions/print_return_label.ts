import $ from 'jquery';
import ClickEvent = JQuery.ClickEvent;
import { set_validation_passed } from './product_dimensions/validation_passed';
import { open_choose_return_service_popup } from './open_choose_return_service_popup';
import { validate_the_order } from './product_dimensions/validate_the_order';
import { check_license_key } from './license_key/check_license_key';

export const print_return_label = async ( event: ClickEvent ) => {
	event.preventDefault();

	const license_is_ok = check_license_key();

	if ( !license_is_ok ) {
		console.error( 'License key is not set' );
		return;
	}

	const _this = event.currentTarget;
	const order_id: string = $( _this ).data( 'order-id' );
	const type: string = $( _this ).data( 'type' );

	console.info( 'Validating order dimensions' );
	const order_dimensions_are_valid = await validate_the_order( order_id, type );

	if ( !order_dimensions_are_valid ) {
		return;
	}

	open_choose_return_service_popup( order_id );
	set_validation_passed( order_id, false );
};
