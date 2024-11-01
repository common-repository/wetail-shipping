let selected_sender_id: { [ order_id: string ]: string | undefined } = {};

export const get_selected_sender_id = ( order_id: string ): string | undefined => {
	return selected_sender_id.hasOwnProperty( order_id ) ? selected_sender_id[ order_id ] : undefined;
};

export const set_selected_sender_id = ( order_id: string, id: string | undefined ): string | undefined => {
	selected_sender_id[ order_id ] = id;
	return selected_sender_id[ order_id ];
};
