<?php

namespace Wetail\Shipping\Engine;

defined( 'ABSPATH' ) or die();

$currency = get_woocommerce_currency();

$prices = [
	'pay_as_you_go'  => 2,
	'pre_payed_500'  => 400,
	'pre_payed_1000' => 675,
];

if ( $currency !== 'SEK' ) {
	$currency = 'EUR';
	$prices   = [
		'pay_as_you_go'  => 0.2,
		'pre_payed_500'  => 39,
		'pre_payed_1000' => 67,
	];
}

$price_args = [ 'currency' => $currency ];

?>

<div id="no-license" style="display: none">
	<p><?php esc_html_e( 'You don’t seem to have a subscription. Please pick a plan and get started today', 'wetail-shipping' ) ?></p>
	<div class="wetail-shipping-promo">
		<div class="wetail-shipping-promo__cols">
			<div class="wetail-shipping-promo__col wetail-shipping-promo__col--left">
				<img src="<?php echo esc_url( URL . '/assets/images/image.png' ) ?>" alt="Wetail Shipping"
				     class="wetail-shipping-promo__image">
			</div>
			<div class="wetail-shipping-promo__col wetail-shipping-promo__col--right">
				<div class="wetail-shipping-promo__info">
					<h4 class="wetail-shipping-promo__title"><?php esc_html_e( 'Pay as you go', 'wetail-shipping' ) ?></h4>
					<p class="wetail-shipping-promo__text">
						<?php
						/* translators: placeholder for the price per order print */
						printf( esc_html__( 'Only %s/order print', 'wetail-shipping' ), wc_price( esc_html( $prices[ 'pay_as_you_go' ] ), $price_args ) );
						?>
					</p>
					<p class="wetail-shipping-promo__text">
						<?php esc_html_e( 'No fixed costs, you only pay for usage.', 'wetail-shipping' ) ?>
					</p>
				</div>
				<div class="wetail-shipping-promo__info">
					<h4 class="wetail-shipping-promo__title"><?php esc_html_e( 'Pre-paid 500 orders', 'wetail-shipping' ) ?></h4>
					<p class="wetail-shipping-promo__text">
						<?php
						/* translators: placeholder for the price per month  */
						printf( esc_html__( 'from %s/month', 'wetail-shipping' ), wc_price( esc_html( $prices[ 'pre_payed_500' ] ), $price_args ), );
						?>
						<i><?php
							/* translators: placeholder for the price per order */
							printf( esc_html__( '%s/order', 'wetail-shipping' ), wc_price( esc_html( $prices[ 'pre_payed_500' ] / 500 ), array_merge( $price_args, [ 'decimals' => 3 ] ) ) );
							?></i>
					</p>
					<p class="wetail-shipping-promo__text">
						<?php esc_html_e( 'For growing shops, pricing control.', 'wetail-shipping' ) ?>
					</p>
				</div>
				<div class="wetail-shipping-promo__info">
					<h4 class="wetail-shipping-promo__title"><?php esc_html_e( 'Pre-paid 1000 orders', 'wetail-shipping' ) ?></h4>
					<p class="wetail-shipping-promo__text">
						<?php
						/* translators: placeholder for the price per month  */
						printf( esc_html__( 'from %s/month', 'wetail-shipping' ), wc_price( esc_html( $prices[ 'pre_payed_1000' ] ), $price_args ), );
						?>
						<i><?php
							/* translators: placeholder for the price per order */
							printf( esc_html__( '%s/order', 'wetail-shipping' ), wc_price( esc_html( $prices[ 'pre_payed_1000' ] / 1000 ), array_merge( $price_args, [ 'decimals' => 3 ] ) ) );
							?></i>
					</p>
					<p class="wetail-shipping-promo__text">
						<?php esc_html_e( 'For large shops, pricing control.', 'wetail-shipping' ) ?>
					</p>
				</div>
				<div class="wetail-shipping-promo__info">
					<h4 class="wetail-shipping-promo__title"><?php esc_html_e( 'Enterprise', 'wetail-shipping' ) ?></h4>
					<p class="wetail-shipping-promo__text">
						<?php esc_html_e( 'Contact sales', 'wetail-shipping' ) ?>
					</p>
					<p class="wetail-shipping-promo__text">
						<?php esc_html_e( 'Custom integrations and shipping rules.', 'wetail-shipping' ) ?>
					</p>
				</div>
			</div>
		</div>
		<div class="wetail-shipping-promo__buttons">
			<a href="https://wetail.io/integrationer/wetail-shipping/" target="_blank"
			   class="button button-primary alignright"><?php esc_html_e( 'Visit wetail.io', 'wetail-shipping' ) ?></a>
		</div>
	</div>
</div>
