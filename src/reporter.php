<?php

/**
 * Class Reporter.php | src/reporter.php
 * 
 * This class is the entry object for querying and producing inventory reports.
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records;

class Reporter {

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
        $this->wp_hooks();
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
     * Checks Wordpress capabilities and roles for REST authorisation.
     * 
     * @return boolean
     */
    protected static function is_user_permissible() {

        if ( current_user_can( 'edit_users' ) ) {
            return true;
        }

        return false;

    }

    /**
     * Hook into Wordpress functionality.
     */
    private function wp_hooks() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $rest_namespace = $Stock_Records->get_rest_namespace();

        // Note filters run after actions.
        add_filter( 
            "submenu_file", 
            array( __NAMESPACE__ . "\\Hooks\\Reporter", "af_submenu_file" )
        );        

        add_action(
            "init",
            array( __NAMESPACE__ . "\\Hooks\\Reporter", "aa_init" ),
            10
        );

        add_action( 
            "admin_menu", 
            array( __NAMESPACE__ . "\\Hooks\\Reporter", "aa_admin_menu" )
        );

        add_action( 
            "admin_enqueue_scripts", 
            array( __NAMESPACE__ . "\\Hooks\\Reporter", "aa_admin_enqueue_scripts" )
        );

        // GET resources/translations
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "resources/translations",
                array(
                    "methods" => "GET",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Resources",  "translations"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

        // GET resources/products
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "resources/products",
                array(
                    "methods" => "GET",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Resources",  "products"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

        // GET resources/products
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "resources/users",
                array(
                    "methods" => "GET",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Resources",  "users"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

        // GET settings
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "settings",
                array(
                    "methods" => "GET",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Settings",  "read_settings"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

        // PUT settings
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "settings",
                array(
                    "methods" => "PUT",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Settings",  "update_settings"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );        

        // GET transactions
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "transactions",
                array(
                    "methods" => "GET",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Transactions",  "search_transactions"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

        // POST transactions
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "transactions",
                array(
                    "methods" => "POST",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Transactions",  "create_transaction"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

        // GET transactions/<id>
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "transactions/(?P<_id>\d+)",
                array(
                    "methods" => "GET",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Transactions",  "read_transaction"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

        // PUT transactions/<id>
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "transactions/(?P<_id>\d+)",
                array(
                    "methods" => "PUT",
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Transactions",  "update_transaction"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

        // DELETE transactions/<id>
        add_action( 
            "rest_api_init",
            function() use ( $rest_namespace ) { register_rest_route(
                $rest_namespace, 
                "transactions/(?P<_id>\d+)",
                array(
                    "methods" => 'DELETE',
                    "callback" => array( 
                        __NAMESPACE__ . "\\Hooks\\Rest_Transactions",  "delete_transaction"
                    ),
                    "permission_callback" => function() { return self::is_user_permissible(); }
                )
            ); }
        );

    }

}