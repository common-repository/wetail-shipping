import $ from 'jquery';

const required_element_ids: string[] = [];

export const set_required_element_ids = () => {
	$( '.update-product-dimensions input[required]' ).each( (_index, element) => {
		if ( !required_element_ids.includes( element.id ) ) {
			required_element_ids.push( element.id );
		}
	})
}

export const get_required_element_ids = () => {
	return required_element_ids;
}

export const unset_required = ( id: string ) => {
	const input_elem = $( `#${ id }` );
	if ( !input_elem.length ) {
		return;
	}
	input_elem.removeAttr( 'required' );
	input_elem.next( '.unit' ).children( '.req' ).hide();
}

export const set_required = ( id: string ) => {
	const input_elem = $( `#${ id }` );
	if ( !input_elem.length ) {
		return;
	}
	input_elem.attr( 'required', 'required' );
	input_elem.next( '.unit' ).children( '.req' ).show();
}

export const toggle_required_dimensions = ( event: JQuery.KeyUpEvent | JQuery.ChangeEvent ) => {
	const total_weight_input_element = event.currentTarget;

	required_element_ids.forEach( ( id ) => {
		if ( total_weight_input_element.value.length ) {
			unset_required( id );
		} else {
			set_required( id );
		}
	} );

};
