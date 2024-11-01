
import { show_pdf_from_response } from './show_pdf_from_response';
import { send_bulk_print_shipping_labels_request } from './send_bulk_print_shipping_labels';

export const bulk_print = async ( event: JQuery.SubmitEvent ) => {
	const target = event.target as HTMLButtonElement;

	// Navigate to the parent form
	const form = target.closest('form');

	if (!form) return; // Exit if no form is found

	// Get the select element relevant to the clicked button
	const actionSelect = form.querySelector<HTMLSelectElement>(target.id === 'doaction' ? 'select[name="action"]' : 'select[name="action2"]');
	if (!actionSelect) return; // Exit if no select element is found

	const selectedAction = actionSelect.value;

	// Check if the selected action matches your custom bulk action
	if (selectedAction === 'wetail_shipping_print_labels') {
		event.preventDefault(); // Prevent the form from submitting
		let checkboxes = form.querySelectorAll<HTMLInputElement>('input[name="post[]"]:checked');
		if ( !checkboxes.length ) {
			checkboxes = form.querySelectorAll<HTMLInputElement>('input[name="id[]"]:checked'); // HPOS.
		}
		const orderIds = Array.from(checkboxes).map(checkbox => checkbox.value);
		let response = await send_bulk_print_shipping_labels_request(orderIds);
		if ( response.error ){
			alert(response.message)
		}
		else{
			show_pdf_from_response( response );
		}
	}
};
