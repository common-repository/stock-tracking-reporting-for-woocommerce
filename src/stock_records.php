<?php

/**
 * Class Stock_Records | src/stock_records.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records;

class Stock_Records {

    public $Monitor = null;

    public $Reporter = null;

    public $rest_namespace_version = null;

    public $translation_key = null;

    protected $plugin_code = null;

    protected $plugin_slug = null;

    protected $plugin_path = null;

    protected $plugin_file_name = null;

    /**
     * Initialise class to object.
     */
    public function __construct( $plugin_file_and_path=null, $plugins_path=null ) {

        if ( ! is_null( $plugin_file_and_path ) && ! is_null( $plugins_path ) ) {
            
            $this->prepare_plugin_variables( $plugin_file_and_path, $plugins_path );
            $this->wp_activation_panel_hooks();

        }

    }
  
    /**
     * Gets the plugin namespace version.
     *
     * @return string The namespace version.
     */
    public function get_namespace_version() {
        return $this->namespace_version;
    }
 
    /**
     * Sets the plugin namespace version.
     *
     * @param mixed $namespace_version the namespace version
     *
     * @return self
     */
    public function set_rest_namespace_version( string $rest_namespace_version ) {
        $this->rest_namespace_version = $rest_namespace_version;
    }
 
    /**
    * Gets the plugin translation key.
    *
    * @return mixed
    */
    public function get_translation_key() {
        return $this->translation_key;
    }
 
    /**
    * Sets the value of translation_key.
    *
    * @param mixed $translation_key the translation key
    *
    * @return self
    */
    public function set_translation_key( string $translation_key ) {
        $this->translation_key = $translation_key;
    }
 
    /**
     * Gets the value of plugin_code.
     *
     * @return mixed
     */
    public function get_plugin_code() {
        return $this->plugin_code;
    }
 
    /**
     * Sets the value of plugin_code.
     *
     * @param mixed $plugin_code the plugin code
     *
     * @return self
     */
    public function set_plugin_code( string $plugin_code ) {
        $this->plugin_code = $plugin_code;
    }

    /**
    * Gets the value of plugin_slug.
    *
    * @return mixed
    */
    public function get_plugin_slug() {
        return $this->plugin_slug;
    }
 
    /**
    * Sets the value of plugin_slug.
    *
    * @param mixed $plugin_slug the plugin slug
    *
    * @return self
    */
    public function set_plugin_slug( string $plugin_slug ) {
        $this->plugin_slug = $plugin_slug;
    }
 
    /**
    * Gets the value of plugin_path.
    *
    * @return mixed
    */
    public function get_plugin_path() {
        return $this->plugin_path;
    }
 
    /**
    * Sets the value of plugin_path.
    *
    * @param mixed $plugin_path the plugin path
    *
    * @return self
    */
    public function set_plugin_path( string $plugin_path ) {
        $this->plugin_path = $plugin_path;
    }
 
    /**
    * Gets the value of plugin_file_name.
    *
    * @return mixed
    */
    public function get_plugin_file_name() {
        return $this->plugin_file_name;
    }
 
    /**
    * Sets the value of plugin_file_name.
    *
    * @param mixed $plugin_file_name the plugin file name
    *
    * @return self
    */
    public function set_plugin_file_name( string $plugin_file_name ) {
        $this->plugin_file_name = $plugin_file_name;
    }

    /**
     * Gets the version of the plugin from the plugin header file.
     *
     * @return mixed
     */
    public function get_version() {

        if ( ! function_exists( "get_file_data" ) ) {
            throw new \RuntimeException( "Wordpress function get_file_data() missing." );
        }

        $plugin_headers = get_file_data(
            $this->get_plugin_file_name_and_path(),
            [ "version" => "Version" ]
        );

        if ( ! isset( $plugin_headers["version"] ) ) {
            return false;
        }

        return $plugin_headers["version"];
        
    }

    public function get_rest_namespace() {
        return $this->plugin_slug . "/v" . $this->rest_namespace_version;
    }

    public function get_plugin_file_name_and_path() {
        return $this->plugin_path . "/" . $this->plugin_file_name;
    }

    /**
     * Returns the singleton instance of the Monitor object.
     * 
     * @return Monitor
     */
    public function run_monitor() {

        $this->Monitor = Monitor::get_instance();
        return $this->Monitor;

    }

    /**
     * Returns the singleton instane of the Reporter object.
     * 
     * @return Reporter
     */
    public function run_reporter() {

        $this->Reporter = Reporter::get_instance();
        return $this->Reporter;

    }

    public function prepare_plugin_variables( $plugin_pathed_file, $plugins_path ) {

        $this->set_plugin_slug( pathinfo( $plugin_pathed_file )["filename"] );
        $this->set_plugin_file_name( basename( $plugin_pathed_file) );
        $this->set_plugin_path(
            $plugins_path . "/" . pathinfo( $plugin_pathed_file )["filename"]
        );

    }

    /**
     * Activation panel_WP hooks.
     */
    public function wp_activation_panel_hooks() {

        register_activation_hook( 
            $this->get_plugin_file_name_and_path(),
            array( __NAMESPACE__ . "\\Hooks\Monitor", "register_activation_hook_activate_plugin" ) 
        );

        add_filter( 
            "plugin_row_meta", 
            array( __NAMESPACE__ . "\\Hooks\Reporter", "af_plugin_row_meta" ),
            10,
            2
        );

    }


}
