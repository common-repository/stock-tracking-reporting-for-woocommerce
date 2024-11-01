<?php

namespace Innocow\Stock_Records\Hooks;

use Innocow\Stock_Records\Views\Admin\Loader;

/**
 * An intermediate layer for calling non-static objects given how WP instantiates its hook system.
 */
class Reporter {

    public static function af_plugin_row_meta( $links_array, $calling_plugin_path_fragment ) {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $plugin_file_path = $Stock_Records->get_plugin_file_name_and_path();
        $menu_slug = $Stock_Records->get_plugin_slug();
        $tr_key = $Stock_Records->get_translation_key();

        $path_fragment = $Stock_Records->get_plugin_slug() . DIRECTORY_SEPARATOR;
        $path_fragment .= $Stock_Records->get_plugin_file_name();

        if ( $calling_plugin_path_fragment !== $path_fragment ) {
            return $links_array;
        }

        $admin_url = admin_url( "admin.php" );

        $about_txt = __( "About & Help", $tr_key );
        $about_url = $admin_url . "?page=" . $menu_slug;
        $about_url .= "&load=about";

        $additional_links_array = [
            "about" => "<a href='$about_url'>$about_txt</a>"
        ];

        return array_merge( $links_array, $additional_links_array );

    }

    /**
     * Filter out subpages you don't want to show and have it highlight another page.
     * 
     * Brilliantly from: https://stackoverflow.com/questions/3902760/
     */
    public static function af_submenu_file( $submenu_file ) {

        global $plugin_page;        
        $Stock_Records = $GLOBALS["ICWCSR"];
        $menu_slug = $Stock_Records->get_plugin_slug();

        $hidden_submenus = [
            $menu_slug."-delete" => true,
            $menu_slug."-premium-license" => true,
        ];

        // Select another submenu item to highlight (optional).
        if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
            $submenu_file = $menu_slug;
        }        

        // Hide the submenu.
        foreach ( $hidden_submenus as $submenu => $unused ) {
            remove_submenu_page( $menu_slug, $submenu );
        }    

        return $submenu_file;

    }

    /**
     * From action: init
     */
    public static function aa_init() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $plugin_file_path = $Stock_Records->get_plugin_file_name_and_path();
        $plugin_slug = $Stock_Records->get_plugin_slug();

        $arr_plugin_headers = get_file_data( 
            $plugin_file_path, 
            [
                "text_domain" => "Text Domain",
                "domain_path" => "Domain Path" 
            ]
        );

        load_plugin_textdomain(
            $arr_plugin_headers["text_domain"],
            false,
            $plugin_slug . $arr_plugin_headers["domain_path"]
        );
        
    }

    /**
     * From action: admin_menu
     */
    public static function aa_admin_menu() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $code = $Stock_Records->get_plugin_code();
        $menu_slug = $Stock_Records->get_plugin_slug();
        $tr_key = $Stock_Records->get_translation_key();

        $menu_page_title =  __( "Stock Tracking & Reporting for Woocommerce", $tr_key );
        $menu_sidebar_title = __( "Stock Tracking", $tr_key );
        
        add_menu_page( 
            __( $menu_page_title, $tr_key ),
            __( $menu_sidebar_title, $tr_key ),
            "manage_woocommerce", // capability
            $menu_slug,
            function() { Loader::load( "search" ); },
            "dashicons-clipboard", // icon url
            58
        );

        add_submenu_page( 
            $menu_slug,
            __( "Add Transaction", $tr_key ), 
            __( "Add Transaction", $tr_key ), 
            "manage_woocommerce",
            $menu_slug . "-manage", 
            function() { Loader::load( "addedit" ); }
        );

        add_submenu_page( 
            $menu_slug,
            __( "Delete Transaction", $tr_key ), 
            __( "Delete Transaction", $tr_key ), 
            "manage_woocommerce",
            $menu_slug . "-delete", 
            function() { Loader::load( "delete" ); }
        );        

        add_submenu_page( 
            $menu_slug,
            __( "Reports", $tr_key ), 
            __( "Reports", $tr_key ), 
            "manage_woocommerce",
            $menu_slug . "-reports", 
            function() { Loader::load( "reports" ); }
        );

        add_submenu_page( 
            $menu_slug,
            __( "Settings", $tr_key ), 
            __( "Settings", $tr_key ), 
            "manage_woocommerce",
            $menu_slug . "-settings", 
            function() { Loader::load( "settings" ); }
        );

        add_submenu_page( 
            $menu_slug,
            __( "About & Help", $tr_key ), 
            __( "About & Help", $tr_key ), 
            "manage_woocommerce",
            $menu_slug . "-about", 
            function() { Loader::load( "about" ); }
        );

        do_action( $code . "_admin_menu" ); 
        
    }

    /**
     * From action: admin_enqueue_scripts
     */
    public static function aa_admin_enqueue_scripts( $hook_suffix ) {
        
        $Stock_Records = $GLOBALS["ICWCSR"];
        $plugin_code = $Stock_Records->get_plugin_code();
        $plugin_file_path = $Stock_Records->get_plugin_file_name_and_path();

        $arr_js = [
            [
                "id" => "transaction",
                "file" => "/views/admin/js/transaction.js",
            ],
            [
                "id" => "http-rest",
                "file" => "/views/admin/js/http-rest.js",
            ],
            [
                "id" => "http-url",
                "file" => "/views/admin/js/http-url.js",
            ],
            [
                "id" => "html",
                "file" => "/views/admin/js/html.js",
            ],
            [
                "id" => "html-table",
                "file" => "/views/admin/js/html-table.js",
            ],
            [
                "id" => "html-settings-mapper",
                "file" => "/views/admin/js/html-settings-mapper.js",
                "deps" => [
                    $plugin_code . "-html",
                ]
            ],
        ];

        $arr_css = [
            [
                "id" => "admin",
                "file" => "/views/admin/css/admin.css"
            ],
            [
                "id" => "form",
                "file" => "/views/admin/css/form.css"
            ],
            [
                "id" => "table",
                "file" => "/views/admin/css/table.css"
            ]
        ];

        wp_enqueue_script( "jquery" );
        wp_enqueue_script( "jquery-ui-autocomplete" );

        foreach( $arr_js as $js ) {

            $js_ver = filemtime( __DIR__ . "/.." . $js["file"] );
            $js_deps = isset( $js["deps"] ) ? $js["deps"] : [];
            wp_enqueue_script(
                $plugin_code . "-" . $js["id"],
                plugins_url( "/src" . $js["file"], $plugin_file_path ),
                $js_deps,
                $js_ver,
                true // load in footer
            );

        }        

        foreach ( $arr_css as $css ) {

            $css_ver = filemtime( __DIR__ . "/.." . $css["file"] );
            $css_deps = isset( $css["deps"] ) ? $css["deps"] : [];
            wp_enqueue_style( 
                $plugin_code . "-" . $css["id"],
                plugins_url( "/src" . $css["file"], $plugin_file_path ),
                null,
                $css_ver,
                "all" // media attribute for <link>.
            );

        }

    }

}