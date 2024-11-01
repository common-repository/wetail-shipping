
import { call_ajax } from './call_ajax';

export const send_bulk_print_shipping_labels_request = ( orderIds: Array<string> ): JQuery.jqXHR<MyResponse<string>> => {
	let data: RequestData = {
		action: 'wetail_shipping_bulk_print_shipping_labels',
		wetail_shipping_nonce: window._wetail_shipping.wetail_shipping_nonce,
		order_ids: orderIds
	};

	return call_ajax( data )
		.always( () => {
			},
		);
};
