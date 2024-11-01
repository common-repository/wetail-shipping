import $ from "jquery";

export const hide_spinner = (order_id: string | null): void => {
    if (!order_id) return;

    const spinner = $(`.post-${order_id}.type-shop_order .wetail_shipping_engine .spinner`);

    spinner.hide().css('visibility', 'hidden');
}
