import { show_spinner } from '../spinner/show_spinner';
import { call_ajax } from '../call_ajax';
import { hide_spinner } from '../spinner/hide_spinner';

export const get_carriers = ( order_id: string ): JQuery.jqXHR<MyResponse<CarriersData[]>> => {
	const data: RequestData = {
		action: 'wetail_shipping_client_shipping_methods',
		wetail_shipping_nonce: window._wetail_shipping.wetail_shipping_nonce,
		order_id
	};

	show_spinner( order_id );
	return call_ajax( data ).always( () => {
		hide_spinner( order_id );
	} );
};
