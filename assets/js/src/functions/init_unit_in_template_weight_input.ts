import $ from 'jquery';

export const init_unit_in_template_weight_input = () => {
	const wetail_shipping_template_weight_element = $( '#wetail_shipping_template_weight' );
	if ( !wetail_shipping_template_weight_element.length ) {
		return;
	}
	const unit_html = wetail_shipping_template_weight_element.data( 'unit_html' );

	if ( !unit_html ) {
		return;
	}

	wetail_shipping_template_weight_element.parent().css( 'position', 'relative' );
	wetail_shipping_template_weight_element.after( unit_html );
};
