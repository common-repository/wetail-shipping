type ProductDimensionsData = {
	product_id: string | number
	product_name?: string
	quantity?: number
	skip_row: boolean
	manually_added?: boolean
	weight: string
	height: string
	length: string
	width: string
}

type SendersData = {
	id: number,
	name: string,
	address_1: string,
	address_2: string,
	zipcode: number,
	city: string,
	country: string
	selected?: boolean
}

type CarriersData = {
	name: string
	service_id: string
}

type MyResponse<dataType> = {
	error?: string
	url?: string
	data?: dataType
	message?: string
}

type RequestData = {
	action?: string
	wetail_shipping_nonce?: string
	order_id?: string
	order_ids?: Array
	updated_dimensions?: ProductDimensionsData[]
	total_order_weight?: string
	shipping_service_id?: string
	selected_carrier_id?: string
	selected_sender_id?: string
	pickup_time?: string
}

interface Window {
	ajaxurl: string,
	print_label_pickup_time_need: boolean,
	wp: {
		template: ( templateId: string ) => ( templateData: Object ) => HTMLElement
	},
	_wetail_shipping: {
		i18n: {
			[ key: string ]: string
		}
		settings: {
			enable_manual_shipping_service_selection_override: boolean
			has_multiple_senders: boolean
			book_pickup_manually: boolean
			license_key_is_set: boolean
		},
		wetail_shipping_nonce: string
	}
}
