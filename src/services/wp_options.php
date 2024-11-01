<?php

/**
 * Class Options | src/services/options.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Services;

class WP_Options {

    private $arr_option_keys = [];

    public function __construct() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $prefix = $Stock_Records->get_plugin_code();

        if ( ! $prefix ) {
            throw new \RuntimeException( "Invalid plugin prefix." );
        }

        $this->arr_option_keys = [
            "table_hide_id" => $prefix . "_table_hide_id",
            "table_hide_product_name_and_sku" => $prefix . "_table_hide_product_name_and_sku",
            "table_hide_product_name" => $prefix . "_table_hide_product_name",
            "table_hide_product_sku" => $prefix . "_table_hide_product_sku",
            "table_hide_order" => $prefix . "_table_hide_order",
            "table_hide_note" => $prefix . "_table_hide_note",
            "table_hide_email" => $prefix . "_table_hide_email",
            "notice_hide_few_results" => $prefix . "_notice_hide_few_results",
        ];

    }

    /**
     * Return the canonical option key string for wordpress based on a code.
     * 
     * @param string $code A codeword for an option key.
     * 
     * @return string|null An opion key string or NULL if code doesn't exist.
     **/
    public function option_key( $code ) {

        if ( isset( $this->arr_option_keys[$code] ) ) {
            return $this->arr_option_keys[$code];
        }

        return null;

    }

    public function create_default_options() {

        if ( ! ( get_option( $this->option_key( "table_hide_id" ) ) ) ) {
            update_option( $this->option_key( "table_hide_id" ), true );
        }

        if ( ! ( get_option( $this->option_key( "table_hide_product_name_and_sku" ) ) ) ) {
            update_option( $this->option_key( "table_hide_product_name_and_sku" ), true );
        }

        if ( ! ( get_option( $this->option_key( "table_hide_product_sku" ) ) ) ) {
            update_option( $this->option_key( "table_hide_product_sku" ), true );
        }

    }

    /**
     * Some PHP array functions seem to add numerical indexes to the values. See
     * array_diff() for example. This function removed the indexes. Using array_values()
     * isn't ideal as the order here is important. (Sigh, PHP)
     * 
     * @param array $arr_indexed Array with indexes.
     * 
     * @return array Array without indexes.
     */
    private function flatten_array( array $arr_indexed ) {

        $arr_flattened = [];

        foreach( $arr_indexed as $key => $value ) {
            $arr_flattened[] = $value;
        }

        return $arr_flattened;

    }

    public function table_columns_all() {

        $array_table_columns = [
            "id",
            "date",
            "change",
            "product_name_and_sku",
            "product_name",
            "product_sku",
            "order",
            "email",
            "note"
        ];

        return $array_table_columns;

    }

    /**
     * Array of column names for use with screen displays.
     *
     * @return array Array of column names.
     */
    public function table_columns_preferred() {

        $columns = $this->table_columns_all();
        $hide_columns = [];

        if ( get_option( $this->option_key( "table_hide_id" ) ) ) {
            $hide_columns[] = "id";
        }

        if ( get_option( $this->option_key( "table_hide_product_name_and_sku" ) ) ) {
            $hide_columns[] = "product_name_and_sku";
        }

        if ( get_option( $this->option_key( "table_hide_product_name" ) ) ) {
            $hide_columns[] = "product_name";
        }

        if ( get_option( $this->option_key( "table_hide_product_sku" ) ) ) {
            $hide_columns[] = "product_sku";
        }

        if ( get_option( $this->option_key( "table_hide_email" ) ) ) {
            $hide_columns[] = "email";
        }

        if ( get_option( $this->option_key( "table_hide_order" ) ) ) {
            $hide_columns[] = "order";
        }

        if ( get_option( $this->option_key( "table_hide_note" ) ) ) {
            $hide_columns[] = "note";
        }

        $display_columns_indexed = array_diff( $columns, $hide_columns );

        return $this->flatten_array( $display_columns_indexed );

    }

    public function table_columns_preferred_as_json() {
        return json_encode( $this->table_columns_preferred() );
    }

    /**
     * Array of column names for use with exported documents.
     *
     * @return array Array of column names.
     */
    public function table_columns_file() {

        $columns = $this->table_columns_all();
        $hide_columns = [
            "id",
            "product_name_and_sku"
        ];

        $display_columns_indexed = array_diff( $columns, $hide_columns );

        return $this->flatten_array( $display_columns_indexed );

    }

    public function table_columns_file_as_json() {
        return json_encode( $this->table_columns_file() );
    }

    public function notices() {

        $notices = [];

        $notices["hide_few_results"] = get_option( 
            $this->option_key( "notice_hide_few_results" )
        );

        return $notices;

    }

    public function notices_as_json() {
        return json_encode( $this->notices() );
    }

    public function wp_timezone_string() {

        if ( ! get_option("timezone_string") ) {
            return "UTC";
        } else {
            return get_option("timezone_string");
        }

    }

}