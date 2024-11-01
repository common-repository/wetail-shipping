import $ from "jquery";

type CallAjax = ( data: RequestData ) => JQuery.jqXHR<MyResponse<any>>;

export const call_ajax: CallAjax = (data) => {

	data.wetail_shipping_nonce = window._wetail_shipping.wetail_shipping_nonce;

	return $.ajax({
        url: window.ajaxurl,
        data,
        type: 'post',
        dataType: 'json',
    })
}
