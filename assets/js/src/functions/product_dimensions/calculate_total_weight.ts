import $ from 'jquery';

export const calculate_total_weight = () => {
	const weight_input_fields = $( '.update-product-dimensions input[name="weight"]' );
	let total_weight: number = 0;
	let unit: string = '';
	weight_input_fields.each( ( _index, input ) => {
		let product_id: string = $( input ).parents( '.table-row' ).data( 'product_id' );

		if ( $( `#${ product_id }-skip` ).prop( 'checked' ) ) {
			return;
		}

		let value = $( input ).val() as string | number | undefined;
		if ( typeof value === 'undefined' ) {
			return;
		}

		if ( typeof value !== 'number' ) {
			value = parseFloat( value );
		}

		if ( isNaN( value ) ) {
			return;
		}

		const quantity = parseFloat( $( `#${ product_id }-quantity` ).text() );

		if ( isNaN( quantity ) ) {
			return;
		}

		total_weight += value * quantity;

		if ( !unit.length ) {
			unit = $( input ).next( '.unit' ).text().replace(/\*/g, '');
		}
	} );

	$( '.update-product-dimensions .total-weight__value' ).attr( 'placeholder', Math.round( total_weight * 1000 ) / 1000 );
	$( '.update-product-dimensions .total-weight__unit' ).text( unit );
};
