<?php

/**
 * Class Reports | src/views/pages/reports.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Views\Admin\Pages;

use Innocow\Stock_Records\Services\Html;
use Innocow\Stock_Records\Services\Translations;

class Reports {

    private $arr_content = [];

    public function __construct() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $tr_key = $Stock_Records->get_translation_key();

        $this->arr_content["url_addon"] = "https://innocow.com/software";
        $this->arr_content["header"] = __( "Reports", $tr_key );
        $this->arr_content["free_version"] = __( 
            "<p>This feature is available in the premium add-on.</p>"
            ."<p><a href='%s' target='_blank'>Upgrade your plugin &rarr;</a></p>"
            ,$tr_key 
        );

    }

    private function javascript( $use_echo=false ) {

        $Translations = new Translations();
        $arr_translations = $Translations->to_array();

        $js = <<< JAVASCRIPT

JAVASCRIPT;

        if ( ! $use_echo ) {
            return $js;
        }

        echo $js;

    }

    public function html( array $page_elements, $use_echo=false ) {

        $html_nav = isset( $page_elements["nav"] ) ? $page_elements["nav"] : "";
        $html_title = isset( $page_elements["title"] ) ? $page_elements["title"] : "";
        $js = $this->javascript();

        $html_notice = sprintf( 
            $this->arr_content["free_version"], $this->arr_content["url_addon"] 
        );

        $html = <<< HTML

        <div id="page-addedit" class="wrap">

        $html_title

        <h2> {$this->arr_content['header']} </h2>

        <hr class="wp-header-end">

        $html_nav

        <div class="announcement">
            $html_notice
        </div>

        </div>

        <script>$js</script>

HTML;

        if ( ! $use_echo ) {
        
            return $html;

        } 

        echo $html;

    }

}

