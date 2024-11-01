<?php

/**
 * Class Loader | src/views/admin/loader.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Views\Admin;

use Innocow\Stock_Records\Views\Admin\Pages\About;
use Innocow\Stock_Records\Views\Admin\Pages\Addedit;
use Innocow\Stock_Records\Views\Admin\Pages\Delete;
use Innocow\Stock_Records\Views\Admin\Pages\Reports;
use Innocow\Stock_Records\Views\Admin\Pages\Search;
use Innocow\Stock_Records\Views\Admin\Pages\Settings;

class Loader {

    public static function js_init() {

        $Stock_Records = $GLOBALS["ICWCSR"];

        $rest_namespace = $Stock_Records->get_rest_namespace();
        $rest_urlroot =  esc_url_raw( rest_url() );
        $rest_nonce = wp_create_nonce( 'wp_rest' );

        $session_user_id = intval( get_current_user_id() );
        $session_user_email = ( get_userdata( get_current_user_id() ) )->get( "user_email" );
        $plugin_slug = $Stock_Records->get_plugin_slug();
        $url_admin = admin_url( "admin.php" );
        $locale = get_locale();
        
        $tz_offset = intval( get_option( "gmt_offset" ) );
        $tz_string = get_option( "timezone_string" );

        if ( $tz_string == "" && $tz_offset == 0 ) {
            $tz_string = "UTC";
        }


        $js = <<< HTML

<script type="text/javascript">
    
    var Innocow = Innocow || {};

    Innocow.WP = class WP {

        constructor() {

            this.urlAdmin = "$url_admin";

            this.locale = {
                withUnderscore: "$locale",
                withHyphen: "$locale".replace('_','-'),
                language: "$locale".split('_')[0],
                region: "$locale".split('_')[1],
            }

            this.timezone = {
                offsetHours: $tz_offset,
                offsetMinutes: ($tz_offset * 60),
                string: "$tz_string",
            }

            this.session = {
                userId: $session_user_id,
                userEmail: "$session_user_email",
            }

            this.plugin = {
                slug: "$plugin_slug",
            }

            this.rest = {
                nonce: "$rest_nonce",
                namespace: "$rest_namespace",
                url: "$rest_urlroot",
            }

        }

        createPluginUrl() {
            let params = new URLSearchParams( { "page": this.plugin.slug } );
            return this.urlAdmin + "?" + params.toString();
        }

    };

</script>
HTML;

        return $js;

    }

    public static function html_title() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $tr_key = $Stock_Records->get_translation_key();

        $title = __( "Stock Tracking for Woocommerce", $tr_key );

        $html_title = <<< HTML

        <h1 class="wp-heading-inline">
            $title
        </h1>

HTML;

        return $html_title;

    }

    public static function html_nav() {
        return "";
    }

    public static function load( $page=null ) {

        // Initialise our JS namespace and helper properties.
        echo self::js_init();

        $Stock_Records = $GLOBALS["ICWCSR"];

        switch( $page ) {

            case "addedit":
                if ( icwcsr_is_premium_loaded() 
                && class_exists( "\Innocow\Stock_Records_Premium\Views\Admin\Pages\AddEdit" ) ) {
                    $AddEditPage = new \Innocow\Stock_Records_Premium\Views\Admin\Pages\AddEdit();
                } else {
                    $AddEditPage = new Addedit();
                }
                echo $AddEditPage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );
                break;

            case "delete":
                if ( icwcsr_is_premium_loaded() 
                && class_exists( "\Innocow\Stock_Records_Premium\Views\Admin\Pages\Delete" ) ) {
                    $DeletePage = new \Innocow\Stock_Records_Premium\Views\Admin\Pages\Delete(); 
                } else {            
                    $DeletePage = new Delete();
                }
                echo $DeletePage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );
                break;                

            case "reports":
                if ( icwcsr_is_premium_loaded() 
                && class_exists( "\Innocow\Stock_Records_Premium\Views\Admin\Pages\Reports" ) ) {
                    $ReportsPage = new \Innocow\Stock_Records_Premium\Views\Admin\Pages\Reports();
                } else {
                    $ReportsPage = new Reports();
                }
                echo $ReportsPage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );
                break;            

            default:
            case "search":
                $SearchPage = new Search();
                echo $SearchPage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );
                break;

            case "settings":
                $SettingsPage = new Settings();
                echo $SettingsPage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );
                break;

            case "about":
                $AboutPage = new About();
                echo $AboutPage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );
                break;

            case "license":
                if ( icwcsr_is_premium_loaded() 
                && class_exists( "\Innocow\Stock_Records_Premium\Views\Admin\Pages\License" ) ) {
                    $LicensePage = new \Innocow\Stock_Records_Premium\Views\Admin\Pages\License();
                } else {
                    $LicensePage = new About();
                }
                echo $LicensePage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );                
                break;

        }

    }

}