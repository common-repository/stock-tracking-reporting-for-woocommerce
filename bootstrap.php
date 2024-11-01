<?php

/**
 * Add to autoloader queue for given namespace.
 */
function icwcsr_modify_autoloader( $plugin_file_pathed, $namespace ) {

    spl_autoload_register( function( $fully_qualified_class ) 
    use ( $plugin_file_pathed, $namespace ) {

        if ( substr( $namespace, -1 ) !== "\\" ) {
            $namespace .= "\\";
        }

        if ( strpos( $fully_qualified_class, $namespace ) === false ) {
            return;
        }

        // Get plugin file elements
        $plugin_file_elements = pathinfo( $plugin_file_pathed );
        $plugin_directory = $plugin_file_elements["filename"]; // filename w/o ext.
        $plugin_src_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_directory . DIRECTORY_SEPARATOR . "src";
        $plugin_src_realpath = realpath( $plugin_src_path ); // expand any symlinks.

        // Get classname elements
        $class_path = str_replace( $namespace, '', $fully_qualified_class ); // class w/o namspace.
        $class_path_lc = strtolower( $class_path );

        $class_file_pathed = $plugin_src_path . DIRECTORY_SEPARATOR;
        $class_file_pathed .= str_replace( '\\', DIRECTORY_SEPARATOR, $class_path_lc ) . '.php';

        if ( ! file_exists( $class_file_pathed ) ) {
            throw new \RuntimeException( "Autoloader cannot find file " . $class_file_pathed );
        }

        include( $class_file_pathed );

    } );

}

function icwcsr_is_plugin_active( $plugin ) {

    $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
    return in_array( $plugin, $active_plugins );

}

/**
 * Check for required software before proceeding.
 */
function icwcsr_check_prerequisites() {

    if ( ! icwcsr_is_plugin_active( "woocommerce/woocommerce.php" ) ) {
        throw new \RuntimeException( 
            __( "Plugin aborted. Woocommerce is missing or inactive.", "icwcsr" )
        );
    }

    return true;

}

/**
 * Define constants for the plugin.
 */
function icwcsr_set_constants( array $constants=[] ) {

    foreach ( $constants as $constant_key => $constant_value ) {

        if ( ! defined( $constant_key ) ) {
            define( $constant_key, $constant_value );
        }

    }

}

/**
 * Determines whether the request is a REST request.
 * 
 * As of the time of this version, wordpress can't definitely give the
 * status of the context of the request. To minimize plugin loading when
 * it shouldn't, this custom function must be used.
 */
function icwcsr_is_rest_context() {

    if ( strpos( $_SERVER["REQUEST_URI"], "/wp-json/" ) !== false ) {
        return true;
    }

    if ( strpos( $_SERVER["REQUEST_URI"], "/index.php?rest_route=" ) !== false ) {
        return true;
    }

    return false;

}

/**
 * Check if the premium add-on has been loaded in WP.
 */
function icwcsr_is_premium_loaded() {

    return icwcsr_is_plugin_active( 
        "stock-tracking-reporting-for-woocommerce-premium/stock-tracking-reporting-for-woocommerce-premium.php"
    );

}

/**
 * Add a message to the admin_notices hook for WP.
 */
function icwcsr_plugin_page_notice( $message, $notice_type ) {

    add_action( "admin_notices", function() use ( $message, $notice_type ) {

        $valid_types = [ "error", "warning", "success", "info" ];

        $notice = in_array( $notice_type, $valid_types ) ? "notice-{$notice_type}" : "notice-info";
        
        $html = "<div class='notice $notice is-dismissible'><p>$message</p></div>";        
        $WP_Screen = get_current_screen(); 
        if ( $WP_Screen->id === "plugins" ) { // Don't spam the user w errors.
            echo $html;
        }

    } );

}

function icwcsr_init_exception_handler( \Exception $e ) {

    $prefix = __( "Stock Tracking for Woocommerce", "icwcsr" );
    $message = "<b>$prefix:</b>{$e->getMessage()}";
    icwcsr_plugin_page_notice( $e->getMessage(), "error" );

}

/**
 * General exception handler. Only show more detail if debug flags are on.
 */
function icwcsr_general_exception_handler( \Exception $e, $do_echo=false ) {

    $error_and_stacktrace =  $e->getMessage() . PHP_EOL . $e->getTraceAsString();

    if ( defined( "WP_DEBUG" ) && constant( "WP_DEBUG" ) == true ) {

        error_log( $error_and_stacktrace );

        if ( defined( "WP_DEBUG_DISPLAY" ) && constant( "WP_DEBUG_DISPLAY" ) == true ) {

            if ( $do_echo ) {
                echo ( $error_and_stacktrace );
            }

            return $error_and_stacktrace;

        }        

    } else {
        return "Plugin encountered a general, unhandled error.";
    }

}