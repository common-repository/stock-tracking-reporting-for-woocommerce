<?php

/**
 * Class Search_Filter | src/views/pages/search_filter.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Views\Admin\Pages;

use Innocow\Stock_Records\Services\Html;
use Innocow\Stock_Records\Services\Translations;

class Search_Filter {

    private $arr_content = [];

    public function __construct() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $tr_key = $Stock_Records->get_translation_key();

        $this->arr_content["url_addon"] = "https://innocow.com/software";
        $this->arr_content["free_version"] = __( 
            "<p>This feature is available in the premium add-on.</p>"
            ."<p><a href='%s' target='_blank'>Upgrade your plugin &rarr;</a></p>"
            ,$tr_key 
        );

    }

    public function html() {

        $html_notice = sprintf( 
            $this->arr_content["free_version"], $this->arr_content["url_addon"] 
        );        

        $html = <<< HTML

      <div class="filters" id="form-search-filter" style="display:none;padding:0px">
        <div class="announcement">
            $html_notice
        </div>
      </div>

HTML;

        return $html;

    }

}