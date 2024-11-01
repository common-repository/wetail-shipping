import $ from 'jquery';

export function check_license_key(): boolean {
	const { license_key_is_set } = window._wetail_shipping.settings;

	if ( !license_key_is_set ) {
		const width = 630,
			height = 500;

		// @ts-ignore
		tb_show( window._wetail_shipping.i18n[ 'Missing account connection' ], '/?TB_inline&width=' + width + '&height=' + height + '&inlineId=no-license' );

		const TBWindow = $( '.wetail-shipping-promo' ).parents( '#TB_window' );
		TBWindow.wrap( '<div class="tb-window-no-license"></div>' );

		$( '#TB_window' ).on( 'tb_unload', function() {
			$( '.tb-window-no-license' ).remove();
		} );
	}

	return license_key_is_set;
}
