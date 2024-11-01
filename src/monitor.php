<?php

/**
 * Class Monitor.php | src/monitor.php
 * 
 * This class is the entry object for collecting inventory movement that isn't collected 
 * or inadequtely collected by Woocommerce.
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records;

class Monitor {

     /*
      * Process unique instance of the ICWPSA class (Singleton model.)
      *
      * @var ICWPSA
      */ 
    protected static $instance = null;
    
    /**
     * Initialise class to object.
     */
    public function __construct() {
        
        $this->wc_hooks();

    }

    /**
     * Gets an already initialised session instance of this class.
     * 
     * Note the static methods required with Singleton patterns.
     *
     * @return Monitor The session instance of the object.
     */
    public static function get_instance() {

        // If this class"s reference is null.
        if ( is_null( self::$instance ) ) {

            self::$instance = new self();

        }

        return self::$instance;

    }

    /**
     * Hook into Woocommerce functionality.
     */
    private function wc_hooks() {

        // Order completed (sale)
        add_action( 
            "woocommerce_reduce_order_stock", 
            array( __NAMESPACE__ . "\\Hooks\Monitor", "aa_wc_reduce_order_stock" )
        );

        // Order cancellation - cancelled
        add_action( 
            // Note, when stopping orders there are three options: cancelled, failed, and refunded.
            // Currently, WC only restocks when the order is cancelled, but it doesn't restock
            // when the order is failed or refunded. For future reference, the other hooks for the 
            // status change are:
            //     woocommerce_order_status_failed
            //     woocommerce_order_status_refunded          
            "woocommerce_order_status_cancelled", 
            array( __NAMESPACE__ . "\\Hooks\Monitor", "aa_woocommerce_order_status_cancelled" )
        );

        if ( is_admin() ) {

            // Manual stock changes - As this hook is called also on a sale, only enable the 
            // hook within the admin dashboard.
            add_action( 
              "woocommerce_product_set_stock", 
              array( __NAMESPACE__ . "\\Hooks\Monitor", "aa_woocommerce_product_set_stock" )
            );

        }

    }

}