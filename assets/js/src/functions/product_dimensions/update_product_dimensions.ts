import $ from 'jquery';
import { show_spinner } from '../spinner/show_spinner';
import { hide_spinner } from '../spinner/hide_spinner';
import { call_ajax } from '../call_ajax';
import { set_validation_passed } from './validation_passed';

/**
 * Retrieves updated product dimensions from a table.
 *
 * @param {JQuery} table - The table element.
 * @returns {ProductDimensionsData[]} An array of updated product dimensions.
 */
const get_updated_dimensions = ( table: JQuery ): ProductDimensionsData[] => {
	const updated_dimensions: ProductDimensionsData[] = [];
	if ( table.length ) {
		table.find( 'tbody .table-row--first' ).each( ( _index, row ) => {
			const product_id = $( row ).data( 'product_id' );
			const product_name = $( row ).data( 'product_name' );
			const dimensions_data: ProductDimensionsData = {
				product_id,
				product_name,
				skip_row: $( `#${ product_id }-skip` ).prop( 'checked' ),
				weight: $( `#${ product_id }-weight` ).val()!.toString(),
				length: $( `#${ product_id }-length` ).val()!.toString(),
				height: $( `#${ product_id }-height` ).val()!.toString(),
				width: $( `#${ product_id }-width` ).val()!.toString(),
			};

			updated_dimensions.push( dimensions_data );
		} );
	}

	return updated_dimensions;
};


/**
 * Constructs request data for updating order items in the server.
 *
 * @param {string} order_id - The ID of the order to update.
 * @param {JQuery<HTMLFormElement>} form
 *
 * @returns {RequestData} - The constructed request data.
 */
const get_request_data = ( order_id: string, form: JQuery<HTMLFormElement> ): RequestData => {
	const table = form.find( $( 'table' ) );
	const updated_dimensions = get_updated_dimensions( table );

	let total_order_weight = $( '#total_order_weight' ).val() as string | undefined;
	if ( !total_order_weight ) {
		total_order_weight = '';
	}

	return {
		action: 'wetail_shipping_update_order_items',
		order_id,
		updated_dimensions,
		total_order_weight,
	};
};

/**
 * Updates the dimensions of a product.
 *
 * @param {JQuery.SubmitEvent} event - The submit event object.
 * @returns {void}
 */
export const update_product_dimensions = ( event: JQuery.SubmitEvent ): void => {
	event.preventDefault();
	const form = $( event.currentTarget );
	const order_id = form.data( 'order_id' );
	const type = form.data( 'type' );
	const request_data = get_request_data( order_id, form );

	// @ts-ignore
	tb_remove();

	show_spinner( order_id );

	set_validation_passed( order_id, true );

	setTimeout( () => {
		call_ajax( request_data )
			.always( () => {
				hide_spinner( order_id );
			} )
			.done( () => {
				$( `.${ type }[data-order-id="${ order_id }"]` ).trigger( 'click' );
			} );
	}, 500 );
};
