import {call_ajax} from "../call_ajax";
import {hide_spinner} from "../spinner/hide_spinner";
import {show_spinner} from "../spinner/show_spinner";

export const send_the_order_dimensions_validation_request = ( order_id: string ) => {
    let data: RequestData = {
        action: 'wetail_shipping_validate_order',
        order_id,
    };

    show_spinner(order_id);

    return call_ajax(data).always(() => {
        hide_spinner(order_id);
    });
}
