import { get_validation_passed } from './validation_passed';
import { send_the_order_dimensions_validation_request } from './send_the_order_dimensions_validation_request';
import { show_form_with_product_dimensions } from './show_form_with_product_dimensions';

export const validate_the_order = async ( order_id: string, type: string ): Promise<boolean> => {
	if ( get_validation_passed( order_id ) ) {
		return true;
	}

	const order_dimensions_validation_response = await send_the_order_dimensions_validation_request( order_id );

	if ( !order_dimensions_validation_response ) {
		console.error( 'No order dimensions validation response' );
		return false;
	}

	if ( order_dimensions_validation_response.error ) {
		console.info( order_dimensions_validation_response.message ? order_dimensions_validation_response.message : 'Order dimensions are not valid' );
		show_form_with_product_dimensions( order_dimensions_validation_response, order_id, type );
		return false;
	}

	console.info( order_dimensions_validation_response.message ? order_dimensions_validation_response.message : 'Order dimensions are valid' );

	return true;

};
