<?php

/**
 * Class Rest_Settings | src/hooks/rest_settings.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Hooks;

use Innocow\Stock_Records\Exceptions\HttpRequestValidationException;
use Innocow\Stock_Records\Services\Html;
use Innocow\Stock_Records\Services\WP_Options;

class Rest_Settings {

    public static function read_settings( \WP_REST_Request $Request ) {

        try {
    
            $WP_Options = new WP_Options();

            $return_array = [
                "notices" => $WP_Options->notices(),
                "table_columns_all" => $WP_Options->table_columns_all(),
                "table_columns_preferred" => $WP_Options->table_columns_preferred(),
            ];

            return $return_array;

        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }

    public static function update_settings( \WP_REST_Request $Request ) {

        try {

            $Html = new Html();
            $WP_Options = new WP_Options();            

            $array_params = $Request->get_params();

            if ( ! isset( $array_params["set"] ) ) {
                throw new HttpRequestValidationException( "Missing parameter: set" );
            }

            if ( $array_params["set"] === "display" ) {

                $fn_display_id = $Html->form_field_name( "opt-do-table-display-id" );
                $fn_display_email = $Html->form_field_name( "opt-do-table-display-email" );
                $fn_display_order = $Html->form_field_name( "opt-do-table-display-order" );
                $fn_display_product_sku = $Html->form_field_name( "opt-do-table-display-product-sku" );
                $fn_display_note = $Html->form_field_name( "opt-do-table-display-note" );

                $wp_opt_column_display_id = $WP_Options->option_key( 
                    "table_hide_id" 
                );
                $wp_opt_column_display_email = $WP_Options->option_key( 
                    "table_hide_email" 
                );
                $wp_opt_column_display_order = $WP_Options->option_key( 
                    "table_hide_order" 
                );
                $wp_opt_column_display_product_sku = $WP_Options->option_key( 
                    "table_hide_product_sku" 
                );
                $wp_opt_column_display_product_note = $WP_Options->option_key( 
                    "table_hide_note" 
                );

                if ( isset( $array_params[$fn_display_id] ) ) {

                    if ( boolval( $array_params[$fn_display_id] ) === true ) {
                        update_option( $wp_opt_column_display_id, 0 );
                    }

                } else {
                    update_option( $wp_opt_column_display_id, 1 );
                }

                if ( isset( $array_params[$fn_display_email] ) ) {

                    if ( boolval( $array_params[$fn_display_email] ) === true ) {
                        update_option( $wp_opt_column_display_email, 0 );
                    }

                } else {
                    update_option( $wp_opt_column_display_email, 1 );
                }

                if ( isset( $array_params[$fn_display_order] ) ) {

                    if ( boolval( $array_params[$fn_display_order] ) === true ) {
                        update_option( $wp_opt_column_display_order, 0 );
                    }

                } else {
                    update_option( $wp_opt_column_display_order, 1 );
                }

                if ( isset( $array_params[$fn_display_product_sku] ) ) {

                    if ( boolval( $array_params[$fn_display_product_sku] ) === true ) {
                        update_option( $wp_opt_column_display_product_sku, 0 );
                    }

                } else {
                    update_option( $wp_opt_column_display_product_sku, 1 );
                }

                if ( isset( $array_params[$fn_display_note] ) ) {

                    if ( boolval( $array_params[$fn_display_note] ) === true ) {
                        update_option( $wp_opt_column_display_product_note, 0 );
                    }

                } else {
                    update_option( $wp_opt_column_display_product_note, 1 );
                }

            }

            if ( $array_params["set"] === "notices" ) {

                $fn_notice_hide_few_results = $Html->form_field_name( 
                    "opt-notice-hide-few-results"
                );
                $wp_opt_notice_hide_few_results = $WP_Options->option_key(
                    "notice_hide_few_results"
                );

                if ( isset( $array_params[$fn_notice_hide_few_results] ) ) {

                    if ( boolval( $array_params[$fn_notice_hide_few_results] ) === true ) {
                        update_option( $wp_opt_notice_hide_few_results, 1 );
                    } else {
                        update_option( $wp_opt_notice_hide_few_results, 0 );
                    }

                }

            }

            return new \WP_REST_Response( [
                "status" => 200,
                "isUpdated" => true
            ] );

        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }
    
}
