<?php

namespace Wetail\Shipping\Engine;

defined( 'ABSPATH' ) or die();
$ignore_product_dimension_if_not_given = wc_string_to_bool( get_option( 'wetail_shipping_ignore_product_dimension_if_not_given' ) );

$required       = $ignore_product_dimension_if_not_given ? '' : 'required="required"';
$asterisk       = $ignore_product_dimension_if_not_given ? '' : '<span class="req">*</span>';
$weight_unit    = get_option( 'woocommerce_weight_unit' );
$dimension_unit = get_option( 'woocommerce_dimension_unit' );
?>

<script type="text/html" id="tmpl-update-product-dimensions">
	<tr data-product_id="{{{data.product_id}}}" data-product_name="{{{data.product_name}}}"
	    class="table-row table-row--first">
		<td colspan="2">{{{data.product_name}}}</td>
		<td class="table-cell table-cell--weight align-right">
			<label for="{{{data.product_id}}}-weight"
			       class="screen-reader-text"><?php esc_html_e( 'Weight', 'wetail-shipping' ); ?></label>
			<input id="{{{data.product_id}}}-weight"
			       class="align-right"
			       type="number"
			       step="0.001"
			       name="weight"
				<?php echo esc_attr( $required ) ?>
				   placeholder=""
				   value="{{{data.weight}}}">
			<span class="unit"><?php echo esc_html( $weight_unit ) ?><?php echo $asterisk ?></span>
		</td>
		<td class="align-right">
			<span id="{{{data.product_id}}}-quantity" class="quantity">{{{data.quantity}}}</span>
		</td>
	</tr>
	<tr data-product_id="{{{data.product_id}}}" data-product_name="{{{data.product_name}}}"
	    class="table-row table-row--second">
		<td class="table-cell table-cell--quarter-length">
			<label for="{{{data.product_id}}}-length"
			       class="screen-reader-text"><?php esc_html_e( 'Length', 'wetail-shipping' ); ?></label>
			<input id="{{{data.product_id}}}-length"
			       type="number"
			       step="0.01"
			       name="length"
				<?php echo esc_attr( $required ) ?>
				   placeholder="<?php esc_attr_e( 'Length', 'wetail-shipping' ); ?>"
				   value="{{{data.length}}}">
			<span class="unit"><?php echo esc_html( $dimension_unit ) ?><?php echo $asterisk ?></span>
		</td>
		<td class="table-cell table-cell--quarter-width">
			<label for="{{{data.product_id}}}-width"
			       class="screen-reader-text"><?php esc_html_e( 'Width', 'wetail-shipping' ); ?></label>
			<input id="{{{data.product_id}}}-width"
			       type="number"
			       step="0.01"
			       name="width"
				<?php echo esc_attr( $required ) ?>
				   placeholder="<?php esc_attr_e( 'Width', 'wetail-shipping' ); ?>"
				   value="{{{data.width}}}">
			<span class="unit"><?php echo esc_html( $dimension_unit ) ?><?php echo $asterisk ?></span>
		</td>
		<td class="table-cell table-cell--quarter-height">
			<label for="{{{data.product_id}}}-height"
			       class="screen-reader-text"><?php esc_html_e( 'Height', 'wetail-shipping' ); ?></label>
			<input id="{{{data.product_id}}}-height"
			       type="number"
			       step="0.01"
			       name="height"
				<?php echo esc_attr( $required ) ?>
				   placeholder="<?php esc_attr_e( 'Height', 'wetail-shipping' ); ?>"
				   value="{{{data.height}}}">
			<span class="unit"><?php echo esc_html( $dimension_unit ) ?><?php echo $asterisk ?></span>
		</td>
		<td class="table-cell"></td>
	</tr>
	<tr data-product_id="{{{data.product_id}}}" data-product_name="{{{data.product_name}}}"
	    class="table-row table-row--third">
		<td class="table-cell table-cell--quarter-skip" colspan="4">
			<# if ( data.manually_added ) { #>
			<button
				class="button button-small button-link-delete remove-row"><?php esc_html_e( 'Remove', 'wetail-shipping' ); ?></button>
			<# } else { #>
			<input id="{{{data.product_id}}}-skip"
			       type="checkbox"
			       data-product_id="{{{data.product_id}}}"
			       data-ignore="<?php echo esc_attr( $ignore_product_dimension_if_not_given ? 'yes' : 'no') ?>"
			       name="skip_row">
			<label for="{{{data.product_id}}}-skip"><?php esc_html_e( 'Do not ship this product', 'wetail-shipping' ); ?></label>
			<# } #>
		</td>
	</tr>
</script>
