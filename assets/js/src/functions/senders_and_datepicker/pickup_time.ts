let pickup_time: { [ order_id: string ]: string | undefined } = {};

export const get_pickup_time = ( order_id: string ): string | undefined => {
	return pickup_time.hasOwnProperty( order_id ) ? pickup_time[ order_id ] : undefined;
};

export const set_pickup_time = ( order_id: string, time: string | undefined ): string | undefined => {
	pickup_time[ order_id ] = time;
	return pickup_time[ order_id ];
};
