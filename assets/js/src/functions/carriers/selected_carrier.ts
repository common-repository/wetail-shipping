let selected_carrier_id: { [ order_id: string ]: string | undefined } = {};

export const get_selected_carrier_id = ( order_id: string ): string | undefined => {
	return selected_carrier_id.hasOwnProperty( order_id ) ? selected_carrier_id[ order_id ] : undefined;
};

export const set_selected_carrier_id = ( order_id: string, carrier_id: string | undefined ): string | undefined => {
	selected_carrier_id[ order_id ] = carrier_id;
	return selected_carrier_id[ order_id ];
};
