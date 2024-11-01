import $ from 'jquery';
import { close_modal } from './close_modal';

type service_option = { name: string, service_id: string }

const generate_popup_html = ( order_id: string ): string => {
	return `
    <div id="choose-return-service" class="choose-return-service">
    	<p>${ window._wetail_shipping.i18n[ 'Please select a return service provided by your parcel delivery company for the order' ] }</p>
        <form class="return-service">
        	<input type="hidden" name="action" value="wetail_shipping_print_return_label" >
        	<input type="hidden" name="order_id" value="${ order_id }" >
            <div>
				<ul class="return-service__list"></ul>
			</div>
            <div class="return-service__buttons">
                <button type="reset" class="button button-secondary button-cancel">${ window._wetail_shipping.i18n[ 'Cancel' ] }</button>
                <button type="submit" class="button button-primary button-ok">${ window._wetail_shipping.i18n[ 'Confirm' ] }</button>
            </div>
        </form>
    </div>
    `;
};

const get_service_options_data = (): service_option[] => {
	return [
		{
			name: 'Postnord Return Pickup',
			service_id: 'postnord_return_pickup_20',
		},
		{
			name: 'Postnord Return DropOff',
			service_id: 'postnord_return_dropoff_24',
		},
		{
			name: 'DHL Return Connect',
			service_id: 'dhl_return_connect_107',
		},
		{
			name: 'DHL Home Delivery Return',
			service_id: 'dhl_home_delivery_return_402',
		},
		{
			name: 'DHL Service Point C2B',
			service_id: 'dhl_service_point_c2b_104',
		},
		{
			name: 'DB Schenker retur via ombud',
			service_id: 'db_schenker_parcel_retur_via_ombud_REP',
		},
		{
			name: 'Bring Pickup Parcel Retur',
			service_id: 'bring_pickup_parcel_return',
		}
	];
};

const get_option_html = ( options: service_option[] ): string => {
	let option_html: string = '';
	options.forEach( ( option: service_option ) => {
		option_html += `<li class="return-service__item">`;
		option_html += `<input required type="radio" name="shipping_service_id" value="${ option.service_id }" id="${ option.service_id }">`;
		option_html += `<label for="${ option.service_id }">${ option.name }</label>`;
		option_html += `</li>`;
	} );

	return option_html;
};

export const open_choose_return_service_popup = ( order_id: string ): void => {
	$( '.choose-return-service' ).remove();
	const popup_html: string = generate_popup_html( order_id );
	const popup: JQuery<HTMLElement> = $( popup_html );
	const options: service_option[] = get_service_options_data();
	const option_html: string = get_option_html( options );
	popup.find( '.return-service__list' ).append( $( option_html ) );
	popup.hide();

	$( 'body' ).append( popup );

	// @ts-ignore
	tb_show( '#' + order_id + ' - ' + window._wetail_shipping.i18n[ 'Select return service' ], '/?TB_inline&inlineId=choose-return-service' );

	const TBWindow = $( '.return-service' ).parents( '#TB_window' );
	TBWindow.attr( 'data-order_id', order_id );
	TBWindow.wrap( '<div class="tb-return-service"></div>' );
	// @ts-ignore
	$("#TB_closeWindowButton").off( 'click', tb_remove ).on( 'click', close_modal );

	$( '#TB_window' ).on( 'tb_unload', function() {
		$( '.tb-return-service' ).remove();
	} );
};
