import $ from 'jquery';
import ChangeEvent = JQuery.ChangeEvent;

export const remove_required_field_from_datepicker = ( event: ChangeEvent ) => {
	const target = $( event.target );
	const senderDatepicker = target.parents( '.sender-and-pickup-form' ).find( $( '.sender-and-pickup-form__datepicker' ) );

	if ( target.is( ':checked' ) ) {
		window.print_label_pickup_time_need = true;
		senderDatepicker.attr( 'required', 'required' );
		senderDatepicker.show();
		senderDatepicker.next().show();
	} else {
		window.print_label_pickup_time_need = false;
		senderDatepicker.removeAttr( 'required' );
		senderDatepicker.val( '' );
		senderDatepicker.hide();
		senderDatepicker.next().hide();
	}
};
