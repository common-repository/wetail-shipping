import { show_spinner } from './spinner/show_spinner';
import { call_ajax } from './call_ajax';
import { hide_spinner } from './spinner/hide_spinner';

export const send_get_shipping_label_request = ( order_id: string ): JQuery.jqXHR<MyResponse<string>> => {
	let data: RequestData = {
		action: 'wetail_shipping_get_shipping_label',
		order_id,
	};

	show_spinner( order_id );
	return call_ajax( data )
		.always( () => {
				hide_spinner( order_id );
			},
		);
};
