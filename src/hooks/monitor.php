<?php

/**
 * Class Monitor | src/hooks/monitor.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Hooks;

use Innocow\Stock_Records\Exceptions\WPActivationException;
use Innocow\Stock_Records\Services\Activation;
use Innocow\Stock_Records\Services\Inventory;

/**
 * An intermediate layer for calling non-static objects given how WP instantiates its hook system.
 */
class Monitor {

    /**
     * From hook: register_activation_hook_activate_plugin
     * 
     * If activation fails, send an error through PHP to WP to halt any further activation 
     * functions and stop the plugin for being set to activated.
     */
    public static function register_activation_hook_activate_plugin() {

        try {

            $Activate = new Activation();
            $Activate->activate_plugin();

        } catch ( \Exception $e ) {
            trigger_error( $e->getMessage(), E_USER_ERROR );
        }

    }

    /**
     * From action: woocommerce_product_set_stock
     * 
     * @param WC_Product A WC_Product object.
     */
    public static function aa_woocommerce_product_set_stock( \WC_Product $WC_Product ) {

        try {

            // Only trigger on the 1.) product panel and 2.) the quick edit forms.
            if ( get_post_type() === "product" || empty( get_post_type() ) ) {

                if ( $WC_Product->get_manage_stock() ) {

                    $Inventory_Service = new Inventory;
                    return $Inventory_Service->add_new_quantity_from_dashboard(
                        $WC_Product,
                        get_current_user_id()
                    );

                }

            }

        } catch ( \Exception $e ) {
            icwcsr_general_exception_handler( $e );
        }

    }

    /**
     * From action: woocommerce_reduce_order_stock
     *
     * @param WC_Order $Order A WC_Order object.
     */
    public static function aa_wc_reduce_order_stock( \WC_Order $WC_Order ) {

        try {

            foreach( $WC_Order->get_items() as $WC_Order_Item_Product ) {

                $Inventory_Service = new Inventory;
                $Inventory_Service->credit_quantity_from_order( $WC_Order_Item_Product );

            }

        } catch ( \Exception $e ) {
            icwcsr_general_exception_handler( $e );
        }

    }

    /** 
     * From action: woocommerce_order_status_cancelled
     *
     * @param int order_id The integer of the order id.
     */
    public static function aa_woocommerce_order_status_cancelled( int $order_id ) {

        try {

            $WC_Order = new \WC_Order( $order_id );

            foreach ( $WC_Order->get_items() as $WC_Order_Item_Product ) {

                $Inventory_Service = new Inventory;
                $Inventory_Service->debit_quantity_from_order( 
                    $WC_Order_Item_Product, 
                    "Order cancelled." 
                );

            }

        } catch ( \Exception $e ) {
            icwcsr_general_exception_handler( $e );
        }

    }

    /**
     * From action: woocommerce_order_status_failed
     *
     * @param int order_id The integer of the order id.
     */
    public static function aa_woocommerce_order_status_failed( int $order_id ) {

        try {

            $WC_Order = new \WC_Order( $order_id );

            foreach ( $WC_Order->get_items() as $WC_Order_Item_Product ) {

                $Inventory_Service = new Inventory;
                $Inventory_Service->debit_quantity_from_order( 
                    $WC_Order_Item_Product, 
                    "Order failed." 
                );

            }

        } catch ( \Exception $e ) {
            icwcsr_general_exception_handler( $e );        
        }
    }

    /**
     * From action: woocommerce_order_status_refunded
     *
     * @param int order_id The integer of the order id.
     */
    public static function aa_woocommerce_order_status_refunded( int $order_id ) {

        try {

            $WC_Order = new \WC_Order( $order_id );

            foreach ( $WC_Order->get_items() as $WC_Order_Item_Product ) {

                $Inventory_Service = new Inventory;
                $Inventory_Service->debit_quantity_from_order( 
                    $WC_Order_Item_Product, 
                    "Order refunded." 
                );

            }

        } catch ( \Exception $e ) {
            icwcsr_general_exception_handler( $e );
        }

    }

}
