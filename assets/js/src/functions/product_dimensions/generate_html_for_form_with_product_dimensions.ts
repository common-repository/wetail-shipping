import $ from "jquery";

export const generate_html_for_form_with_product_dimensions: (data: ProductDimensionsData[]) => JQuery<HTMLElement> = (data) => {
    const form = $('<form class="update-product-dimensions"></form>');

    const table = $(`
        <table class="update-product-dimensions__table">
            <thead>
                <tr>
                    <th colspan="2">${window._wetail_shipping.i18n['Product']}</th>
                    <th class="align-right">${window._wetail_shipping.i18n['Weight']}</th>
                    <th class="align-right">${window._wetail_shipping.i18n['Quantity']}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    `);

    data.forEach((template_data) => {
        const template = window.wp.template('update-product-dimensions');
        const row = template(template_data);
        table.find('tbody').append(row);
    })

    table.appendTo(form);
	$( `<div class="update-product-dimensions__add-row"><button class="button button-small alignright">${ window._wetail_shipping.i18n[ 'Add row' ] }</button></div>` ).appendTo( form );
	$( `
		<div class="update-product-dimensions__buttons">
			<div class="total-weight">
				<label for="total_order_weight" class="total-weight__label">${ window._wetail_shipping.i18n[ 'Total order weight' ] }:</label>
				<input type="number" step="0.001" class="total-weight__value" placeholder="" value="" name="total_order_weight" id="total_order_weight">
				<span class="total-weight__unit"></span>
				<span class="woocommerce-help-tip total-weight__tip" tabindex="0" title="${ window._wetail_shipping.i18n[ 'Overwrite the total weight of the order, if necessary' ] }"></span>
			</div>
			<button type="submit" class="button button-primary alignright">${ window._wetail_shipping.i18n[ 'Continue' ] }</button>
		</div>
	` ).appendTo( form );

    return form;
}
