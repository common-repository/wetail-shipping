import $ from 'jquery';
import { calculate_total_weight } from './calculate_total_weight';
import { get_required_element_ids, set_required_element_ids, unset_required } from './toggle_required_dimensions';

type PromptProductName = () => ( false | string )
const prompt_product_name: PromptProductName = () => {
	let product_name: string | null;

	do {
		product_name = prompt( window._wetail_shipping.i18n[ 'Enter the name of the product' ], '' );
		if ( product_name === null ) {
			return false;
		}
		if ( !product_name.trim().length ) {
			alert( window._wetail_shipping.i18n[ 'The name of the product is empty. Please try again.' ] );
		}

	} while ( !product_name.trim().length );

	return product_name;
};

const get_data_for_the_row_template = ( product_name: string, id: number ): ProductDimensionsData => {
	return {
		product_id: `${ product_name }_${ id }`,
		product_name,
		quantity: 1,
		weight: '',
		height: '',
		length: '',
		width: '',
		skip_row: false,
		manually_added: true,
	};
};

export const add_a_row_to_dimensions_validation_form = ( event: JQuery.ClickEvent ) => {
	event.preventDefault();
	const product_name = prompt_product_name();
	if ( !product_name ) {
		return;
	}
	const tbody_element = $( event.currentTarget )
		.parents( '.update-product-dimensions' )
		.find( 'tbody' );
	const data_for_the_row_template = get_data_for_the_row_template( product_name, tbody_element.find('tr').length / 3 );
	const template = window.wp.template( 'update-product-dimensions' );
	const row = template( data_for_the_row_template );
	tbody_element.append( row );

	calculate_total_weight();
	set_required_element_ids();

	const total_weight_input_element_value = $( '.total-weight__value ').val() as string;
	if ( total_weight_input_element_value.length ) {
		get_required_element_ids().forEach( unset_required );
	}
};
