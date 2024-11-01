import $ from 'jquery';

export const handle_print_label_success_response = (response:any, order_id:string) => {
	if (response && !response.error && response.data && response.icon_html) {
		const icon = $(`.order-${order_id} .wetail-shipping-engine-icon`);

		if (icon.length > 0) {
			icon.remove();
		}

		$(`.order-${order_id}  .wetail_shipping_engine.column-wetail_shipping_engine`).append($(response.icon_html));
	}
}
