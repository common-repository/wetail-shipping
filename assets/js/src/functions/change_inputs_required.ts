import $ from 'jquery';

export const change_inputs_required = ( event: JQuery.ChangeEvent ) => {
	const checkbox = $( event.currentTarget );
	if ( checkbox.data( 'ignore' ) === 'yes' ) {
		return;
	}
	const productId = checkbox.data( 'product_id' );
	const inputsSelector = `#${ productId }-weight, #${ productId }-length, #${ productId }-width, #${ productId }-height`;
	const inputs = checkbox.parents( '.update-product-dimensions' ).find( inputsSelector );
	inputs.prop( 'required', !checkbox.prop( 'checked' ) );
	inputs.siblings( '.unit' ).find( '.req' ).toggleClass( 'hidden', checkbox.prop( 'checked' ) );
};
