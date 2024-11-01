<?php

namespace Wetail\Shipping\Engine;

defined( __NAMESPACE__ . '\PATH' ) or die();

if ( class_exists( __NAMESPACE__ . '\Wetail_Shipping_Product_Packing_Dimensions' ) ) {
	return;
}

class Wetail_Shipping_Product_Packing_Dimensions{
	const TABLE_NAME = 'wetail_shipping_product_packing_dimensions';

	/**
	 * @var $length
	 */
	protected $length;

	/**
	 * @var $width
	 */
	protected $width;

	/**
	 * @var $height
	 */
	protected $height;

	/**
	 * @var $weight
	 */
	protected $weight;

	/**
	 * @var $wc_product_id
	 */
	protected $wc_product_id;

	/**
	 * @var $id
	 */
	protected $id;

	public function __construct( $wc_product_id ) {
		$this->wc_product_id = $wc_product_id;
		$this->read();

	}

    /**
     * Returns ID of the current monthly book
     * @return string
     * @since 0.9.0
     */
    private function read(){
        global $wpdb;
	    $table_name = $wpdb->prefix . self::TABLE_NAME;
        $result = $wpdb->get_row( $wpdb->prepare("SELECT * from {$table_name} where wc_product_id = %d;", $this->wc_product_id ) );
        if( ! empty( $result ) ){
			$this->id               = $result->id;
			$this->wc_product_id    = $result->wc_product_id;
			$this->length           = $result->length;
			$this->height           = $result->height;
			$this->width            = $result->width;
			$this->weight           = $result->weight;
        }
		else{
			$wc_product              = wc_get_product($this->wc_product_id);
			$this->length           = $wc_product->get_length();
			$this->height           = $wc_product->get_height();
			$this->width            = $wc_product->get_width();
			$this->weight           = $wc_product->get_weight();
		}
    }

	/** Get height
	 * @since 0.9.1
	 */
	public function get_height( ){
		return $this->height;
	}

	/** Get width
	 * @since 0.9.1
	 */
	public function get_width( ){
		return $this->width;
	}

	/** Get length
	 * @since 0.9.1
	 */
	public function get_length( ){
		return $this->length;
	}

	/** Get weight
	 * @since 0.9.1
	 */
	public function get_weight( ){
		return $this->weight;
	}

	/** Get id
	 * @since 0.9.1
	 */
	public function get_id( ){
		return $this->id;
	}

	/** Set height
	 * @param $height
	 * @since 0.9.1
	 */
	public function set_height( $height ){
		$this->height = $height;
	}

	/** Set length
	 * @param $length
	 * @since 0.9.1
	 */
	public function set_length( $length ){
		$this->length = $length;
	}

	/** Set width
	 * @param $width
	 * @since 0.9.1
	 */
	public function set_width( $width ){
		$this->width = $width;
	}
	/** Set weight
	 * @param $weight
	 * @since 0.9.1
	 */
	public function set_weight( $weight ){
		$this->weight = $weight;
	}

    /**
     * Saves object to db
     * @since 0.9.1
     */
    public function save(){
        global $wpdb;

	    $data = array(
		    'weight' => $this->weight,
		    'height' => $this->height,
		    'width'  => $this->width,
		    'length' => $this->length,
		    'wc_product_id' => $this->wc_product_id
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
    }
}
