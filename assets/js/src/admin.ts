import { assign_events } from './functions/assign_events';
import { init_tooltips } from './functions/init_tooltips';
import { init_unit_in_template_weight_input } from './functions/init_unit_in_template_weight_input';

/**
 * Initialize
 *
 * @private
 */
function __init() {
	console.info( '[Wetail Shipping Engine] Plugin JS initiated!' );
	window.print_label_pickup_time_need = true;
	assign_events();
	init_tooltips();
	init_unit_in_template_weight_input();
}


if ( document.readyState === 'complete' ) {
	__init();
} else {
	window.addEventListener( 'load', function() {
		__init();
	} );
}
