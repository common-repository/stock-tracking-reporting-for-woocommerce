<?php

/**
 * Class Activation | src/services/activation.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Services;

use Innocow\Stock_Records\Exceptions\WPActivationException;
use Innocow\Stock_Records\Models\Transaction\Mappers\Transaction_Mapper_WPDB;
use Innocow\Stock_Records\Models\Transaction\Transaction;
use Innocow\Stock_Records\Models\Transaction\Transaction_Search;
use Innocow\Stock_Records\Services\Inventory;
use Innocow\Stock_Records\Services\Storage;
use Innocow\Stock_Records\Services\WP_Options;

class Activation {

    private function create_storage_wpdb() {

        $Transaction_Mapper = new Transaction_Mapper_WPDB();
        $table_name = $Transaction_Mapper->get_tablename();
        $charset_collate = $Transaction_Mapper->get_wpdb()->get_charset_collate();

        // Inventory is considered an asset in accounting philosophy. Therefore
        // changes to an asset can be measured in debits and credits. A
        // debit to an asset is an increase (+) whereas a credit to an asset
        // is a decrease (-), this is important to note.

        $sql = "CREATE TABLE $table_name (
                    id BIGINT(20) NOT NULL AUTO_INCREMENT,
                    product_id BIGINT(20) NOT NULL,
                    order_id BIGINT(20),
                    user_id BIGINT(20),
                    customer_id BIGINT(20),
                    debit SMALLINT,
                    credit SMALLINT,
                    note TEXT,
                    is_manual TINYINT(1) DEFAULT 0,
                    is_hidden TINYINT(1) DEFAULT 0,
                    is_deleted TINYINT(1) DEFAULT 0,
                    date_created TIMESTAMP DEFAULT '0000-00-00 00:00:00', 
                    date_updated TIMESTAMP DEFAULT NOW() ON UPDATE NOW(),
                    PRIMARY KEY  ( id ),
                    KEY product_id ( product_id ),
                    KEY order_id ( order_id ),
                    KEY user_id ( user_id ),
                    KEY customer_id ( customer_id )
                ) $charset_collate;";

        // Required for dbDelta() function
        // https://developer.wordpress.org/reference/functions/dbdelta/
        require_once( ABSPATH . "wp-admin/includes/upgrade.php" );
        dbDelta( $sql );

    }

    private function sync_initial_quantities() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $tr_key = $Stock_Records->get_translation_key();

        $Inventory = new Inventory();
        $product_ids = $Inventory->get_products_as_ids_all();

        foreach( $product_ids as $product_id ) {

            // Get the product quantity from WooCommerce.
            $Product = new \WC_Product( $product_id );
            $stock_quantity = $Product->get_stock_quantity();

            $Activation_Transaction = new Transaction();
            $Activation_Transaction->set_product_id( $product_id );
            $Activation_Transaction->set_timestamp_created_as_dt( new \DateTime("now") );

            // The following logic will attempt to get the status of the products in 
            // the transaction table. If there are no records (first activation), it
            // will create the initial transactions for recording. If not, check if
            // the stock quantities are different, and if they are, add adjustments
            // so that they are synchronised.

            $Transaction_Search = new Transaction_Search();
            $Transaction_Search->set_product_id( $product_id );

            $Storage = new Storage();
            $Transactions = $Storage->search( $Transaction_Search );

            if ( $Transactions->is_initial_transaction() ) {

                $Activation_Transaction->set_note( 
                    __( "Balance on activation.", $tr_key )
                 );
                $Activation_Transaction->set_debit( $stock_quantity );
                $Activation_Transaction->is_hidden( true );
                $Storage->create( $Activation_Transaction );

            } else {

                if ( $stock_quantity !== $Transactions->get_balance() ) {

                    $difference = $stock_quantity - $Transactions->get_balance();

                    if ( $difference < 0 ) {

                        $Activation_Transaction->set_credit( abs( $difference ) );

                    } else {

                        $Activation_Transaction->set_debit( $difference );

                    }

                    $Activation_Transaction->set_note( 
                        __( "Activation adjustment.", $tr_key )
                    );
                    $Activation_Transaction->is_hidden( false );
                    $Storage->create( $Activation_Transaction );

                }

            }

        }

    }

    public function activate_plugin() {

        $this->create_storage_wpdb();
        $this->sync_initial_quantities();

        $WP_Options = new WP_Options();
        $WP_Options->create_default_options();

    }

}
