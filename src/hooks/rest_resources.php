<?php

/**
 * Class Rest_Resources | src/hooks/rest_resources.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Hooks;

use Innocow\Stock_Records\Models\Transaction\Transaction_Search;
use Innocow\Stock_Records\Services\Inventory;
use Innocow\Stock_Records\Services\Storage;
use Innocow\Stock_Records\Services\Translations;

class Rest_Resources {

    public static function translations( \WP_REST_Request $Request ) {

        try {

            $Translations = new Translations();
            $arr_translations = $Translations->to_array();

            header( "Cache-Control: public" );
            header( "Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + ( 60 * 5 ) ) );
            header( "Etag: " . md5( json_encode( $arr_translations ) ) );
            
            return $arr_translations;            
 
        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }

    public static function products( \WP_REST_Request $Request ) {

        try {

            $Inventory = new Inventory();
            $array_query_params = $Request->get_query_params();
            $products_array = [];

            if ( isset( $array_query_params["all"] ) 
            && intval( $array_query_params["all"] ) === 1 ) {

                $product_ids = $Inventory->get_products_as_ids_all();
                
                foreach( $product_ids as $product_id ) {

                    $WC_Product = new \WC_Product( $product_id );
                    $products_array[] = $Inventory->wc_product_to_array( $WC_Product );

                }

            } else if ( isset( $array_query_params["s"] ) 
            && strlen( $array_query_params["s"] ) > 2 ) {

                $safe_s = filter_var( 
                    $array_query_params["s"], 
                    FILTER_SANITIZE_STRING,
                    FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK
                );

                if ( isset( $array_query_params["f"] ) ) {
                    $format = $array_query_params["f"];
                } else {
                    $format = null;
                }

                $array_filters = [
                    "limit" => 10,
                ];

                $array_of_wc_products_by_title = $Inventory->get_products( 
                    array_merge( $array_filters, [ "s" => $safe_s ] ) 
                );
                $array_by_title = $Inventory->wc_products_to_array( 
                    $array_of_wc_products_by_title, 
                    $format
                );

                $array_of_wc_products_by_sku = $Inventory->get_products( 
                    array_merge( $array_filters, [ "sku" => $safe_s ] ) 
                );
                $array_by_sku = $Inventory->wc_products_to_array( 
                    $array_of_wc_products_by_sku, 
                    $format
                );

                // Two queries have to be done because WP_Query can't do
                // an 'OR' search within a single query.
                $products_array = array_unique( 
                    array_merge( $array_by_title, $array_by_sku ),
                    SORT_REGULAR
                );

            }

            $arr_return["data"] = $products_array;

            header( "Cache-Control: public" );
            header( "Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + ( 60 * 5 ) ) );
            header( "Etag: " . md5( json_encode( $arr_return ) ) );
            
            return $arr_return;            

        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }        

    }

    public static function users( \WP_REST_Request $Request ) {

        try {

            $array_query_params = $Request->get_query_params();
            $users_array = [];

            if ( isset( $array_query_params["s"] ) && strlen( $array_query_params["s"] ) > 2 ) {

                $safe_s = filter_var( 
                    $array_query_params["s"], 
                    FILTER_SANITIZE_STRING,
                    FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK
                );

                $WP_User_Query = new \WP_User_Query( [
                    "number" => 10,
                    "compare" => "LIKE",
                    "search" => "*$safe_s*", 
                    "search_columns" => [ "user_email" ],
                ] );

                if ( isset( $array_query_params["f"] ) ) {

                    $format = $array_query_params["f"];

                } else {

                    $format = false;

                }


                foreach( $WP_User_Query->get_results() as $WP_User ) {

                    if ( $format === "select2" ) {

                        $users_array["results"][] = [
                            "id" => $WP_User->get( "id" ),
                            "text" => $WP_User->get( "user_email" ),
                        ];                        

                    } else if ( $format === "jqautocomp" ) {

                        $users_array["data"][] = [
                            "id" => $WP_User->get( "id" ),
                            "value" => $WP_User->get( "user_email" ),
                            "label" => $WP_User->get( "user_email" )
                        ];

                    } else {

                        $users_array["data"][] = [
                            "id" => $WP_User->get( "id" ),
                            "email" => $WP_User->get( "user_email" ),
                            "roles" => $WP_User->get( "wp_capabilities" )
                        ];

                    }

                }

            }

            header( "Cache-Control: public" );
            header( "Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + ( 60 * 5 ) ) );
            header( "Etag: " . md5( json_encode( $users_array ) ) );

            return $users_array;            

        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );

        }        

    }    

}