<?php

/**
 * Version: 1.1.3
 * Plugin Name: Stock Tracking & Reporting for Woocommerce
 * Plugin URI: https://innocow.com
 * Description: A plugin to track all stock/inventory changes with your store.
 * Author: Innocow
 * Author URI: http://innocow.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: icwcsr
 * Domain Path: /lang
 * 
 * Requires at least: 5.2
 * Requires PHP: 5.4
 * WC tested up to: 5.1.0
 * WC requires at least: 3.9
 **/

if ( ! defined( "ABSPATH" ) ) { exit; }

// To force the translation of the header text.
$translate = __( "A plugin to track all stock/inventory changes with your store.", "icwcsr" );

//
// Plugin start
//

try {

    require( __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php" );

    icwcsr_modify_autoloader( __FILE__, "Innocow\\Stock_Records" );
    icwcsr_check_prerequisites();
    icwcsr_set_constants( [] );

    $Stock_Records = new \Innocow\Stock_Records\Stock_Records( __FILE__, WP_PLUGIN_DIR );
    $Stock_Records->set_rest_namespace_version( "1" );
    $Stock_Records->set_plugin_code( "icwcsr" );
    $Stock_Records->set_translation_key( "icwcsr" );

    $GLOBALS["ICWCSR"] = $Stock_Records;

    add_action( "plugins_loaded", function() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $Stock_Records->run_monitor();

        if ( is_admin() || icwcsr_is_rest_context() ) {
            $Stock_Records->run_reporter();
        }

    } );

} catch( \Exception $e ) {
    // Note, the above try block only catches errors during the plugin
    // initialisation. Any subsequent hooks (like REST API paths) aren't
    // caught here.
    icwcsr_init_exception_handler( $e );
}

