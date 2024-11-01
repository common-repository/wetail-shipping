import $ from 'jquery';
import { calculate_total_weight } from './calculate_total_weight';
import { set_required_element_ids } from './toggle_required_dimensions';

export const remove_the_row_from_validation_form = ( event: JQuery.ClickEvent ) => {
	event.preventDefault();
	const parent_third_row = $( event.currentTarget ).parents( '.table-row--third' );
	const parent_second_row = parent_third_row.prev( '.table-row--second' );
	const parent_first_row = parent_second_row.prev( '.table-row--first' );
	parent_third_row.remove();
	parent_second_row.remove();
	parent_first_row.remove();
	calculate_total_weight();
	set_required_element_ids();
};
