import $ from 'jquery';

type tiptip_args = {
	content: number
	track: boolean
}

declare global {
	interface JQuery {
		tooltip( arg: tiptip_args ): JQuery;
	}
}

export const init_tooltips = ( css_class: string = '' ) => {
	let css_selector = '.printShippingLabel, .printReturnLabel, .printExistingShippingLabel, .wetail-shipping-engine-icon';
	if ( css_class.length ) {
		css_selector = css_class;
	}
	$( css_selector ).tooltip( {
		classes: {
			'ui-tooltip': 'wetail-shipping-tooltip',
		},
		position: {
			my: 'center bottom-10',
			at: 'center top',
			using: function( position: string, feedback: { vertical: string, horizontal: string } ) {
				$( this ).css( position );
				$( '<div>' )
					.addClass( 'tooltip-arrow' )
					.addClass( feedback.vertical )
					.addClass( feedback.horizontal )
					.appendTo( this );
			},
		},
	} );
};
