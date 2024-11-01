<?php

/**
 * Class WC_Inventory | src/services/wc_inventory.php
 * 
 * Services class to interface between the WC backend and the Transaction model.
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Services;

use Innocow\Stock_Records\Models\Transaction\Transaction;

class Inventory {

    private $note_sale = null;
    private $note_updated_dashboard = null;

    public function __construct() {

        // Set a default translation key as fallback since its cruicial
        // for the db inserts this is set.
        $tr_key = "icwcsr";

        if ( isset( $GLOBALS["ICWCSR"] ) ) {

            $Stock_Records = $GLOBALS["ICWCSR"];

            if ( $Stock_Records->get_translation_key() ) {
                $tr_key = $Stock_Records->get_translation_key();
            }

        }

        $this->note_sale = __( "Sale.", $tr_key );
        $this->note_updated_dashboard = __( "Updated from dashboard.", $tr_key );

    }

    public function get_products( array $extra_parameters=[] ) {

        $default_parameters = [
            "orderby" => "id",
            "order" => "ASC",
            "return" => "objects",
            "status" => "published",
            "type" => "simple",
            "manage_stock" => true, // important, do not remove.
        ];

        $parameters = array_merge( $default_parameters, $extra_parameters );

        return wc_get_products( $parameters );

    }

    public function get_products_as_ids_all( array $extra_parameters=[] ) {

        $default_parameters = [
            "limit" => -1,
            "return" => "ids",
        ];

        $parameters = array_merge( $default_parameters, $extra_parameters );

        return $this->get_products( $parameters );

    }

    public function get_products_all( array $extra_parameters=[] ) {

        $default_parameters = [
            "limit" => -1,
            "return" => "objects",
        ];

        $parameters = array_merge( $default_parameters, $extra_parameters );

        return $this->get_products( $parameters );


    }    

    public function set_product_stock( $product_id, $quantity, $is_decrease=false ) {

        $WC_Product = wc_get_product( $product_id );
        $current_quantity = $WC_Product->get_stock_quantity();
        $safe_quantity_change = abs( intval( $quantity ) );
        $new_quantity = 0;

        if ( ! $is_decrease ) {

            $new_quantity = $current_quantity + $safe_quantity_change;

        } else {

            $new_quantity = $current_quantity - $safe_quantity_change;

        }

        $id = $WC_Product->set_stock_quantity( $new_quantity );
        return $WC_Product->save();

    }

    public function add_new_quantity_from_dashboard( \WC_Product $WC_Product, int $user_id ) {

        if ( empty( $WC_Product->get_changes() ) ) {

            throw new \InvalidArgumentException( 
                "Incomplete WC_Product object passed. Was this called from the right hook?" 
            );

        }

        $prior_qty = $WC_Product->get_data()["stock_quantity"];
        $new_qty = $WC_Product->get_changes()["stock_quantity"];
        $product_id = $WC_Product->get_id();
        $note = $this->note_updated_dashboard;

        $Transaction = new Transaction();
        $Transaction->set_product_id( $product_id );
        $Transaction->set_user_id( $user_id );
        $Transaction->set_note( $note );
        $Transaction->set_timestamp_created_as_dt( new \DateTime( "now" ) );

        $change = $prior_qty - $new_qty;

        // If increase then debit, if decrease, credit.
        if ( $change < 0 ) {

            // The change is an increase to the quantity. So, _debit_!
            $Transaction->set_debit( abs( $change ) );


        } elseif ( $change > 0 ) {

            // The change is a decrease to the quantity. So, _credit_!
            $Transaction->set_credit( abs( $change ) );

        }

        $Storage = new Storage();
        $rows_affected = $Storage->create( $Transaction );

        return $rows_affected;

    }

    public function credit_quantity_from_order( \WC_Order_Item_Product $WC_Order_Item_Product ) {

        return $this->add_transaction_from_order( 
            $WC_Order_Item_Product, 
            true, 
            $this->note_sale
        );

    }

    public function debit_quantity_from_order( \WC_Order_Item_Product $WC_Order_Item_Product, 
                                               $note=null ) {

        return $this->add_transaction_from_order(
            $WC_Order_Item_Product,
            false,
            $note
        );

    }

    private function add_transaction_from_order( \WC_Order_Item_Product $WC_Order_Item_Product,
                                                 bool $is_credit,
                                                 $note=null ) {

        $WC_Product = $WC_Order_Item_Product->get_product();
        $WC_Order = $WC_Order_Item_Product->get_order();

        if ( ! $WC_Product->managing_stock() ) {
            return false;
        }

        if ( ! $WC_Product->exists() ) {
            throw new \RuntimeException( "Invalid product, it does not exist." );
        }

        $Transaction = new Transaction();
        $Transaction->set_product_id( $WC_Product->get_id() );
        $Transaction->set_order_id( $WC_Order_Item_Product->get_order_id() );
        $Transaction->set_timestamp_created_as_dt( new \DateTime( "now" ) );

        // On a sale, if the customer is not a registered user WC will use '0' for customer id.
        // Keep in mind, $WC_Order->get_user_id() and $WC_Order->get_customer_id() are aliases.
        if ( intval( $WC_Order->get_user_id() ) !== 0 ) {

            $Transaction->set_user_id( $WC_Order->get_user_id() );

        }

        if ( $is_credit ) {
            $Transaction->set_credit( $WC_Order_Item_Product->get_quantity() );
        } else {
            $Transaction->set_debit( $WC_Order_Item_Product->get_quantity() );
        }

        if ( ! is_null( $note ) ) {
            $Transaction->set_note( $note );
        }

        $Storage = new Storage();
        $rows_affected = $Storage->create( $Transaction );

        return $rows_affected;

    }

    public function wc_product_to_array( \WC_Product $WC_Product, $format=null ) {

        $id = $WC_Product->get_id();
        $title = $WC_Product->get_title();
        $sku = $WC_Product->get_sku();
        $display = empty( $sku ) ? $title : "$title ($sku)";

        switch( $format ) {

            case "select2":
                $arr_return = [
                    "id" => $id,
                    "text" => $display
                ];
                break;

            case "jqautocomp":
                $arr_return = [
                    "id" => $id,
                    "sku" => $sku,
                    "name" => $title,
                    "value" => $display
                ];
                break;

            default:
                $arr_return = [
                    "id" => $id,
                    "sku" => $sku,
                    "name" => $title,
                ];                
                break;

        }

        return $arr_return;

    }

    public function wc_products_to_array( array $arr_of_wc_products, $format=null ) {

        $arr_simplified = [];

        foreach( $arr_of_wc_products as $WC_Product ) {
            $arr_simplified[] = $this->wc_product_to_array( $WC_Product, $format );
        }

        return $arr_simplified;

    }

    public function calc_product_balances_from_plugin(  int $product_id, 
                                                        int $ts_start, 
                                                        int $ts_end ) {

        $Storage = new Storage();
        $Transaction_Search = new Transaction_Search();
        
        $DT_Start = ( new \DateTime() )->setTimestamp( $ts_start );
        $DT_End = ( new \DateTime() )->setTimestamp( $ts_end );

        $Transaction_Search->set_product_id( $product_id );
        $arr_return = [];

        $Transaction_Search->set_timestamp_created_before( $DT_Start );
        $Collection = $Storage->search( $Transaction_Search );
        $arr_return["start"] = $Collection->get_balance();

        $Transaction_Search->set_timestamp_created_before( $DT_End );
        $Collection = $Storage->search( $Transaction_Search );
        $arr_return["end"] = $Collection->get_balance();

        return $arr_return;

    }

}