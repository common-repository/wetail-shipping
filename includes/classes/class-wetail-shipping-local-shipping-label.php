<?php

namespace Wetail\Shipping\Engine;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Local_Shipping_Label' ) ) {
	return;
}

class Wetail_Shipping_Local_Shipping_Label{
	const TABLE_NAME = 'wetail_shipping_labels';

	/**
	 * @var $wc_order_id
	 */
	protected $wc_order_id;

	/**
	 * @var $date_created
	 */
	protected $date_created;

	/**
	 * @var $data
	 */
	protected $data;

	/**
	 * @var $id
	 */
	protected $id;

	public function __construct( $wc_order_id ) {
		$this->wc_order_id = $wc_order_id;
		$this->read();

	}

    /**
     * Sets vars from DB
     * @return string
     * @since 1.0.5
     */
    private function read(){
        global $wpdb;
	    $table_name = $wpdb->prefix . self::TABLE_NAME;
	    wetail_shipping_write_log("READING LABEL");
        $result = $wpdb->get_row( $wpdb->prepare("SELECT * from {$table_name} where wc_order_id = %d;", $this->wc_order_id ) );

	    wetail_shipping_write_log("Last query executed: " . $wpdb->last_query);
        if( ! empty( $result ) ){
			$this->id               = $result->id;
			$this->date_created     = $result->date_created;
			$this->data             = $result->data;
        }
    }

	/** Get date created
	 * @since 1.0.5
	 */
	public function get_date_created( ){
		return $this->date_created;
	}

	/** Get data
	 * @since 1.0.5
	 */
	public function get_data( ){
		return $this->data;
	}

	/** Set data
	 * @param $data
	 * @since 0.9.1
	 */
	public function set_data( $data ){
		$this->data = $data;
	}

    /**
     * Saves object to db
     * @since 0.9.1
     */
    public function save(){
        global $wpdb;

		wetail_shipping_write_log("SAVING LABEL");
	    $data = array(
		    'wc_order_id'   => $this->wc_order_id,
		    'data'          => $this->data
	    );

		if ( $this->id ){
			$where = array(
				'id' => $this->id,
			);
			$format = array('%s'); // Format of the data being updated
			$where_format = array('%d'); // Format of the where clause
			$wpdb->update( $wpdb->prefix . self::TABLE_NAME, $data, $where, $format, $where_format);
		}
		else{
			$format = array('%s', '%s'); // the format of each corresponding data value in the $data array, e.g., '%s' for string, '%d' for integer, '%f' for float.
			$wpdb->insert($wpdb->prefix . self::TABLE_NAME , $data, $format );
			$this->id = $wpdb->insert_id;
		}
		wetail_shipping_write_log("Last error: " . $wpdb->last_error);
    }
}
