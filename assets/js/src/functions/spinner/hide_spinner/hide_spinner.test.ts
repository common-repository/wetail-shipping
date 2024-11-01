import { hide_spinner } from './hide_spinner';
import $ from 'jquery';
import { describe, it, expect, beforeEach } from '@jest/globals';

describe( 'hide_spinner function', () => {

	beforeEach( () => {
		document.body.innerHTML = `<div class="post-123 type-shop_order"><div class="wetail_shipping_engine"><div class="spinner">Spinner</div></div></div>`;
	} );

	it( 'should hide spinner for provided order id', () => {
		hide_spinner( '123' );

		const spinner = $( `.post-123.type-shop_order .wetail_shipping_engine .spinner` );
		expect( spinner.css( 'display' ) ).toBe( 'none' );
		expect( spinner.css( 'visibility' ) ).toBe( 'hidden' );
	} );

	it( 'should not perform any action if order id is null', () => {
		hide_spinner( null );

		const spinner = $( `.post-123.type-shop_order .wetail_shipping_engine .spinner` );
		expect( spinner.css( 'display' ) ).toBe( 'block' );
		expect( spinner.css( 'visibility' ) ).toBe( 'visible' );
	} );

} );
