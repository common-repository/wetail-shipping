let validation_passed: { [ order_id: string ]: boolean } = {};

export const get_validation_passed = ( order_id: string ): boolean => {
	return validation_passed.hasOwnProperty( order_id ) ? validation_passed[ order_id ] : false;
};

export const set_validation_passed = ( order_id: string, passed: boolean ): boolean => {
	validation_passed[ order_id ] = passed;
	return validation_passed[ order_id ];
};
