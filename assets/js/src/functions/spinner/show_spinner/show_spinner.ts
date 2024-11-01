import $ from "jquery";

export const show_spinner = (order_id: string | null): void => {
    if (!order_id) return;

    const spinner = $(`.post-${order_id}.type-shop_order .wetail_shipping_engine .spinner`);

    spinner.show().css('visibility', 'visible');
}
