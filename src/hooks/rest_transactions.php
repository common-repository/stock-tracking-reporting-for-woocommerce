<?php

/**
 * Class Rest_Transactions | src/hooks/rest_transactions.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Hooks;

use Innocow\Stock_Records\Exceptions\HttpRequestValidationException;
use Innocow\Stock_Records\Models\Transaction\Transaction;
use Innocow\Stock_Records\Models\Transaction\Transaction_Search;
use Innocow\Stock_Records\Services\Calendar;
use Innocow\Stock_Records\Services\Html;
use Innocow\Stock_Records\Services\Http;
use Innocow\Stock_Records\Services\Inventory;
use Innocow\Stock_Records\Services\Storage;

class Rest_Transactions {

    public static function create_transaction( \WP_REST_Request $Request ) {

        try {

            $Html = new Html();
            $Http = new Http();
            $Storage = new Storage();
            $Trn = new Transaction();

            $arr_params = $Request->get_body_params();
            $fn_id = $Html->form_field_name( "transaction-id" );
            $fn_datetime_created = $Html->form_field_name( "transaction-datetime-created" );
            $fn_user_id = $Html->form_field_name( "transaction-user-id" );
            $fn_product_id = $Html->form_field_name( "transaction-product-id" );
            $fn_stock_amount = $Html->form_field_name( "transaction-stock-amount" );
            $fn_stock_change = $Html->form_field_name( "transaction-stock-change" );
            $fn_order_id = $Html->form_field_name( "transaction-order-id" );
            $fn_note = $Html->form_field_name( "transaction-note" );
            $fn_sync_levels = $Html->form_field_name( "do-sync-levels" );            

            if ( isset( $arr_params[$fn_id] ) ) {

                if ( intval( $arr_params[$fn_id] ) !== 0 ) {
                    throw new HttpRequestValidationException( 
                        "A create request must not contain a transaction id."
                    );
                }

            }

            $Http->validate_request_param( $fn_datetime_created, $arr_params );
            $Http->validate_request_param( "user", $arr_params );
            $Http->validate_request_param( "product", $arr_params );
            $Http->validate_request_param( "stock", $arr_params );
            $Http->validate_request_param( "order-id", $arr_params, false );

            $Trn->is_manual( true );
            $Trn->set_timestamp_created( intval( $arr_params[$fn_datetime_created] ) );
            $Trn->set_timestamp_updated( intval( $arr_params[$fn_datetime_created] ) );
            $Trn->set_user_id( intval( $arr_params[$fn_user_id] ) );
            $Trn->set_product_id( intval( $arr_params[$fn_product_id] ) );

            if ( $arr_params[$fn_stock_change] === "increase" ) {
                $Trn->set_debit( intval( $arr_params[$fn_stock_amount] ) );
            }

            if ( $arr_params[$fn_stock_change] === "decrease" ) {
                $Trn->set_credit( intval( $arr_params[$fn_stock_amount] ) );
            }
      
            if ( isset( $arr_params[$fn_order_id] ) 
            && ! empty( $arr_params[$fn_order_id] ) ) {
                $Trn->set_order_id( intval( $arr_params[$fn_order_id] ) );
            }

            if ( isset( $arr_params[$fn_note] )
            && ! empty( $arr_params[$fn_note] ) ) {
                $Trn->set_note( $arr_params[$fn_note] );
            }

            $rows_changed = $Storage->create( $Trn );

            if ( $rows_changed ) {

                if ( isset( $arr_params[$fn_sync_levels] ) ) {

                    if ( $arr_params[$fn_sync_levels] == true ) {

                        $Inventory = new Inventory();
                        $Inventory->set_product_stock( 
                            $Trn->get_product_id(),
                            $Trn->get_change(),
                            $Trn->is_decrease()
                        );

                    }

                }

            } else {
                throw new \RuntimeException( "Could not create transaction." );
            }

            return new \WP_REST_Response( [
                "status" => 201,
                "isCreated" => true,
            ] );

        } catch ( HttpRequestValidationException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 400 ] 
            );
        } catch ( \InvalidArgumentException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 500 ]
            );
        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }

    public static function read_transaction( \WP_REST_Request $Request ) {

        try {

            $Storage = new Storage();
            $Trn = new Transaction();            
            $arr_params = $Request->get_url_params();

            if ( isset( $arr_params["_id"] ) ) {
                $Trn->set_id( intval( $arr_params["_id"] ) );
            }

            $read_result = $Storage->read( $Trn );

            if ( is_null( $read_result ) || $Trn->is_deleted() ) {
                return new \WP_Error( 
                    "error", "This transaction does not exist.", [ "status" => 404 ]
                );
            }
            
            $arr_return = $Trn->to_array_extra( 
                [
                    "debit", 
                    "credit",
                    "is_deleted",
                    "is_hidden",
                ]
            );

            header( "Cache-Control: public" );
            header( "Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + ( 1 ) ) );
            header( "Etag: " . md5( json_encode( $arr_return ) ) );

            return $arr_return;

        } catch ( \InvalidArgumentException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 500 ]
            );
        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }

    public static function update_transaction( \WP_REST_Request $WP_REST_Request ) {

        try {

            $Html = new Html();
            $Http = new Http();
            $Storage = new Storage();
            $Transaction_Check = new Transaction();
            $DateTime_Now = new \DateTime();

            $array_params = $WP_REST_Request->get_params();
            $do_update = false;
            $fn_id = $Html->form_field_name( "transaction-id" );
            $fn_datetime_created = $Html->form_field_name( "transaction-datetime-created" );
            $fn_user_id = $Html->form_field_name( "transaction-user-id" );
            $fn_user_email = $Html->form_field_name( "transaction-user-email" );
            $fn_product_display = $Html->form_field_name( "transaction-product-display" );
            $fn_product_id = $Html->form_field_name( "transaction-product-id" );
            $fn_product_name = $Html->form_field_name( "transaction-product-name" );
            $fn_product_sku = $Html->form_field_name( "transaction-product-sku" );
            $fn_stock_amount = $Html->form_field_name( "transaction-stock-amount" );
            $fn_stock_change = $Html->form_field_name( "transaction-stock-change" );
            $fn_order_id = $Html->form_field_name( "transaction-order-id" );
            $fn_note = $Html->form_field_name( "transaction-note" );
            $fn_sync_levels = $Html->form_field_name( "do-sync-levels" );

            $Http->validate_request_param( "_id", $array_params );

            $Transaction_Check->set_id( $array_params["_id"] );
            $Storage->read( $Transaction_Check );

            if ( $Transaction_Check->is_manual() ) {

                $Http->validate_request_param( $fn_datetime_created, $array_params );
                $Http->validate_request_param( "user", $array_params );
                $Http->validate_request_param( "product", $array_params );
                $Http->validate_request_param( "stock", $array_params );
                $Http->validate_request_param( $fn_order_id, $array_params, false );
                $Http->validate_request_param( $fn_note, $array_params, false );
                $Http->validate_request_param( $fn_sync_levels, $array_params, false );

                $Transaction_Update = new Transaction();
                $Transaction_Update->set_id( $array_params["_id"] );
                $DateTime_Now = new \DateTime();

                $Transaction_Update->set_timestamp_updated( $DateTime_Now->getTimestamp() );

                if ( $Transaction_Check->get_user_id() 
                !== intval( $array_params[$fn_user_id] ) ) {

                    $do_update = true;
                    $Transaction_Update->set_user_id( intval( $array_params[$fn_user_id] ) );

                }

                /*
                if ( $Transaction_Check->get_product_id() 
                !== intval( $array_params[$fn_product_id] ) ) {

                    $do_update = true;
                    $Transaction_Update->set_product_id( $array_params[$fn_product_id] );

                }*/

                if ( $array_params[$fn_stock_change] === "decrease" ) {
                    $signed_stock_change = intval( $array_params[$fn_stock_amount] ) * -1;
                } else {
                    $signed_stock_change = intval( $array_params[$fn_stock_amount] );
                }
                if ( $Transaction_Check->get_change() !== $signed_stock_change ) {

                    $do_update = true;
                    $Transaction_Update->set_change( $signed_stock_change );

                }
                
                if ( isset( $array_params[$fn_order_id] ) && ! empty( $array_params[$fn_order_id] ) ) {

                    if ( $Transaction_Check->get_order_id() 
                    !== intval( $array_params[$fn_order_id] ) ) {

                        $do_update = true;
                        $Transaction_Update->set_order_id( $array_params[$fn_order_id] );

                    }

                }

                if ( isset( $array_params[$fn_note] ) ) {

                    if ( $Transaction_Check->get_note() !== $array_params[$fn_note]  ) {

                        $do_update = true;
                        $Transaction_Update->set_note( $array_params[$fn_note] );

                    }

                }

            } else {

                $Http->validate_request_param( $fn_note, $array_params, false );

                $Transaction_Update = new Transaction();
                $Transaction_Update->set_id( $array_params["_id"] );

                if ( isset( $array_params[$fn_note] ) ) {

                    if ( $array_params[$fn_note] === "" ) {
                        $null_check_fn_note = null;    
                    } else {
                        $null_check_fn_note = $array_params[$fn_note];
                    }                    

                    if ( $Transaction_Check->get_note() !== $null_check_fn_note  ) {

                        $do_update = true;
                        $Transaction_Update->set_note( $array_params[$fn_note] );

                    }

                }

            }

            if ( ! $do_update ) {

                return new \WP_REST_Response( [
                    "status" => 200,
                    "isUpdated" => false
                ] );

            }

            $rows_changed = $Storage->update( $Transaction_Update );

            if ( ! $rows_changed ) {
                throw new \RuntimeException( "Could not update transaction." );
            }

            if ( isset( $array_params[$fn_sync_levels] ) 
            && $array_params[$fn_sync_levels] == true ) {

                $Inventory = new Inventory();

                // product changed; sync both old and new products.
                if ( $Transaction_Update->get_product_id() ) {

                    $delta = (  
                        $Transaction_Check->get_change() - $Transaction_Update->get_change() 
                    );

                    // Sync the changes on the new product.
                    $Transaction_New_Sync = new Transaction();
                    $Transaction_New_Sync->set_product_id( $Transaction_Update->get_product_id() );
                    $Transaction_New_Sync->set_change( $delta );
                    $Inventory->set_product_stock( 
                        $Transaction_New_Sync->get_product_id(),
                        $Transaction_New_Sync->get_change(),
                        $Transaction_New_Sync->is_decrease()
                    );

                    // Reverse the changes on the prior product.
                    $Transaction_Prior_Sync = new Transaction();
                    $Transaction_Prior_Sync->set_product_id(
                        $Transaction_Check->get_product_id()
                    );
                    $Transaction_Prior_Sync->set_change( $delta * -1 );
                    $Inventory->set_product_stock( 
                        $Transaction_Prior_Sync->get_product_id(),
                        $Transaction_Prior_Sync->get_change(),
                        $Transaction_Prior_Sync->is_decrease()
                    );

                } else {

                    $delta = ( 
                        $Transaction_Check->get_change() - $Transaction_Update->get_change() 
                    ) * -1;

                    // Sync the changes only.
                    $Transaction_New_Sync = new Transaction();
                    $Transaction_New_Sync->set_product_id( $Transaction_Check->get_product_id() );
                    $Transaction_New_Sync->set_change( $delta );
                    $Inventory->set_product_stock( 
                        $Transaction_New_Sync->get_product_id(),
                        $Transaction_New_Sync->get_change(),
                        $Transaction_New_Sync->is_decrease()
                    );

                }

            }

            return new \WP_REST_Response( [
                "status" => 200,
                "isUpdated" => true
            ] );

        } catch ( HttpRequestValidationException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 400 ] 
            );    
        } catch ( \InvalidArgumentException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 500 ]
            );
        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }        

    }

    public static function delete_transaction( \WP_REST_Request $Request ) {

        try {

            $array_params = $Request->get_params();

            $Http = new Http();
            $Storage = new Storage();
            $Transaction_Delete = new Transaction();
            $DateTime_Now = new \DateTime();

            $Html = new Html();
            $fn_sync_levels = $Html->form_field_name( "do-sync-levels" );
            
            $Http->validate_request_param( "_id", $array_params );

            $Transaction_Delete->set_id( $array_params["_id"] );
            $Storage->read( $Transaction_Delete );

            if ( ! $Transaction_Delete->is_manual() ) {
                throw new HttpRequestValidationException( 
                    "Cannot delete a non manual transaction." 
                );
            }

            $Transaction_Delete->is_deleted( true );
            $rows_changed = $Storage->purge( $Transaction_Delete );

            if ( ! $rows_changed ) {
                throw new \RuntimeException( "Could not delete transaction." );
            }

            if ( isset( $array_params[$fn_sync_levels] ) && $array_params[$fn_sync_levels] ) {

                $Inventory = new Inventory();

                $Inventory->set_product_stock( 
                    $Transaction_Delete->get_product_id(),
                    $Transaction_Delete->get_change(),
                    ! $Transaction_Delete->is_decrease()
                );

            }

            return new \WP_REST_Response( [
                "status" => 200,
                "isDeleted" => true
            ] );

        } catch ( HttpRequestValidationException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 400 ] 
            );    
        } catch ( \InvalidArgumentException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 500 ]
            );
        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }

    public static function search_transactions( \WP_REST_Request $Request ) {

        try {

            $Storage = new Storage();
            $Transaction_Search = new Transaction_Search();
            $query_array = $Request->get_query_params();
            $arr_return = [];

            $limit = 10;
            $offset = 0;
            $page = 0;

            $Html = new Html();
            $fn_page = $Html->form_field_name( "display-page" );
            $fn_sortby = $Html->form_field_name( "display-sortby" );
            $fn_sortdir = $Html->form_field_name( "display-sortdir" );
            $fn_limit = $Html->form_field_name( "display-limit" );
            $fn_tstamp_after = $Html->form_field_name( "filter-transaction-tstamp-created-after" );
            $fn_tstamp_before = $Html->form_field_name( "filter-transaction-tstamp-created-before" );
            $fn_product = $Html->form_field_name( "filter-transaction-product-keywords" );
            $fn_user = $Html->form_field_name( "filter-transaction-user-keywords" );
            $fn_note = $Html->form_field_name( "filter-transaction-note-keywords" );
            $fn_order = $Html->form_field_name( "filter-transaction-order-id" );
            $fn_do_manual = $Html->form_field_name( "do-filter-only-manual" );

            // Calculate pagination.
            // $page is decremented because its incremented for display
            // Example: Page 1 instead of Page 0.
            if ( isset( $query_array[$fn_page] ) ) {

                $page = abs( intval( $query_array[$fn_page] ) );
                if ( $page >= 1 ) {
                    $page--;
                }

            }

            if ( isset( $query_array[$fn_limit] ) ) {

                if ( $query_array[$fn_limit] !== "" ) {
                    $limit = abs( intval( $query_array[$fn_limit] ) );
                }

            }

            if ( $limit > 0 ) {
                $offset = $page * $limit;
            }

            $Transaction_Search->set_search_limit( $limit );
            $Transaction_Search->set_search_offset( $offset );            

            //
            // Sorting
            //

            if ( isset( $query_array[$fn_sortby] ) ) {

                switch( $query_array[$fn_sortby] ) {

                    case "id":
                        $Transaction_Search->set_search_order( "id" );
                        break;

                    case "date":
                        $Transaction_Search->set_search_order( "date_created" );
                        break;

                    case "order":
                        $Transaction_Search->set_search_order( "order_id" );
                        break;

                    case "product_name":
                        $Transaction_Search->set_search_order( "product_id" );
                        break;

                    case "change":
                        $Transaction_Search->set_search_order( "balance" );
                        break;

                    case "note":
                        $Transaction_Search->set_search_order( "note" );
                        break;

                    case "user":
                        $Transaction_Search->set_search_order( "user_id" );
                        break;

                }

            } 

            if ( isset( $query_array[$fn_sortdir] ) ) {

                switch ( $query_array[$fn_sortdir] ) {

                    case "ascending":
                    case "a":
                        $Transaction_Search->is_search_order_descending( false );
                        break;

                    default:
                    case "descending":
                    case "d":
                        $Transaction_Search->is_search_order_descending( true );
                        break;

                }

            }

            // 
            // Filters
            // 

            if ( isset( $query_array[$fn_tstamp_after] ) ) {

                if ( ! empty( $query_array[$fn_tstamp_after] ) ) {
                    $Transaction_Search->set_timestamp_created_after( 
                        $query_array[$fn_tstamp_after] 
                    );
                }

            }

            if ( isset( $query_array[$fn_tstamp_before] ) ) {

                if ( ! empty( $query_array[$fn_tstamp_before] ) ) {
                    $Transaction_Search->set_timestamp_created_before(
                        $query_array[$fn_tstamp_before]
                    );
                }

            }

            if ( isset( $query_array[$fn_product] ) ) {

                if ( ! empty( $query_array[$fn_product] ) ) {
                    $Transaction_Search->set_product_keywords( $query_array[$fn_product] );
                }

            }

            if ( isset( $query_array[$fn_note] ) ) {

                if ( ! empty( $query_array[$fn_note] ) ) {
                    $Transaction_Search->set_note( $query_array[$fn_note] );
                }

            }


            if ( isset( $query_array[$fn_order] ) ) {

                if ( ! empty( $query_array[$fn_order] ) ) {
                    $Transaction_Search->set_order_id( intval( $query_array[$fn_order] ) );
                }

            }

            if ( isset( $query_array[$fn_user] ) ) {

                if ( ! empty( $query_array[$fn_user] ) ) {
                    $Transaction_Search->set_aux_email( $query_array[$fn_user] );
                }

            }

            if ( isset( $query_array[$fn_do_manual] ) ) {

                if ( $query_array[$fn_do_manual] == 1 ) {
                    $Transaction_Search->is_manual( true );
                }

            }

            if ( isset( $query_array["do-balances"] ) ) {
                if ( $query_array["do-balances"] == 1 ) {
                    $Transaction_Search->do_product_balances( true );
                }
            }

            $Transaction_Search->is_hidden( false );
            $Transaction_Collection = $Storage->search( $Transaction_Search );

            //
            // Results formatting and preparation
            //

            $arr_return["data"] = [];

            // .. data
            foreach ( $Transaction_Collection->get_collection() as $Transaction ) {
                $arr_return["data"][] = $Transaction->to_array_extra(
                    [ 
                        "debit", 
                        "credit",
                        "is_deleted",
                        "is_hidden",
                    ]
                );
            }

            // ... pagination
            if ( $limit > 0 ) {
                $arr_return["pagination"] = [
                    "page" => ceil( $offset / $limit ) + 1,
                    "pages" => ceil( $Transaction_Collection->get_total() / $limit )
                ];
            }

            // ... totals
            $arr_return["total_results"] = $Transaction_Collection->get_total();

            /// ... balances
            if ( $Transaction_Collection->get_balances() ) {
                
                foreach ( $Transaction_Collection->get_balances() as $balance ) {
                    $WC_Product = wc_get_product( $balance["id"] );
                    $balance["product_name"] = $WC_Product->get_title();
                    $balance["product_sku"] = $WC_Product->get_sku();
                    $arr_return["balances"][] = $balance;
                }

            }

            header( "Cache-Control: public" );
            header( "Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + ( 3 ) ) );
            header( "Etag: " . md5( json_encode( $arr_return ) ) );
            
            return $arr_return;

        } catch ( HttpRequestValidationException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 400 ] 
            );
        } catch ( \InvalidArgumentException $e ) {
            return new \WP_Error( 
                "error", $e->getMessage(), [ 'status' => 500 ] 
            );
        } catch ( \Exception $e ) {
            return new \WP_Error( 
                "error", icwcsr_general_exception_handler( $e ), [ 'status' => 500 ] 
            );
        }

    }

}
