import { show_spinner } from './show_spinner';
import $ from 'jquery';
import { beforeAll, describe, expect, it } from '@jest/globals';


describe( 'show_spinner function', () => {
	beforeAll( () => {
		document.body.innerHTML = '<div class="post-1 type-shop_order"><div class="wetail_shipping_engine"><div class="spinner"></div></div></div>';
	} );

	it( 'should make the spinner visible when an order_id is provided', () => {
		const order_id = '1';
		const spinner = $( `.post-${ order_id }.type-shop_order .wetail_shipping_engine .spinner` );
		spinner.hide().css( 'visibility', 'hidden' );

		show_spinner( order_id );

		expect( spinner.css( 'display' ) ).toBe( 'block' );
		expect( spinner.css( 'visibility' ) ).toBe( 'visible' );
	} );

	it( 'should not affect the spinner when the order_id is null', () => {
		const spinner = $( '.spinner' );
		spinner.hide().css( 'visibility', 'hidden' );

		show_spinner( null );

		expect( spinner.css( 'display' ) ).toBe( 'none' );
		expect( spinner.css( 'visibility' ) ).toBe( 'hidden' );
	} );
} );
