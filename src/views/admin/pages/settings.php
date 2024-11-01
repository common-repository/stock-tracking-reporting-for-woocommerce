<?php

/**
 * Class Settings | src/views/pages/settings.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Views\Admin\Pages;

use Innocow\Stock_Records\Services\Html;
use Innocow\Stock_Records\Services\Translations;
use Innocow\Stock_Records\Services\WP_Options;

class Settings {

    private $arr_content = [];

    public function __construct() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $tr_key = $Stock_Records->get_translation_key();        

        $this->arr_content["err_pageinit"] = __( 
            "Page is unavailable. Unexpected error with page initialisation",
            $tr_key
        );
        $this->arr_content["err_updating"] = __( 
            "Settings update failed due to an unexpected error",
            $tr_key
        );
        $this->arr_content["display_columns_tables"] = __( 
            "Display Columns in Transactions Tables", 
            $tr_key 
        );
        $this->arr_content["header"] = __( "Settings", $tr_key );
        $this->arr_content["ok_updated"] = __( "Settings updated", $tr_key );

    }

    private function javascript( $use_echo=false ) {

        $WP_Options = new WP_Options();

        $js = <<< JAVASCRIPT

document.addEventListener( "DOMContentLoaded", function( eventDOMContentLoaded ) {

    let form = document.getElementById( "form-settings" );
    let arrayOfOrderedColumns = {$WP_Options->table_columns_preferred_as_json()};
    let innocowWP = new Innocow.WP();    
    let page = new Innocow.WC.StockRecords.Html( document.getElementById( "page-plugin" ) );
    let formMapper = new Innocow.WC.StockRecords.HtmlSettingsMapper( form, arrayOfOrderedColumns );
    let httpRest = new Innocow.WC.StockRecords.HttpRest( 
        innocowWP.rest.url, 
        innocowWP.rest.namespace, 
        innocowWP.rest.nonce 
    );

    let updateOptions = function() {

        httpRest.putSettings( form )
        .then( response => httpRest.parseNetworkResponse(
            response,
            function( e ) { page.displayErrorStatus( e ) }
        ) )
        .then( responseJSON => {

            if ( responseJSON.hasOwnProperty( "isUpdated" ) && responseJSON.isUpdated ) { 
                
                page.displayOkStatus( "{$this->arr_content['ok_updated']}." );
                return responseJSON;

            }

            throw new Error( responseJSON );            
            
        } )
        .catch( error => { 

            page.displayErrorStatus( "{$this->arr_content['err_updating']}." );
            console.error( error );

        } );

    }

    try {

        formMapper.populateColumns();

        form.addEventListener( "submit", eventSubmit => {

            eventSubmit.preventDefault();
            updateOptions();

        } );

    } catch ( error ) {

        page.displayErrorStatus( "{$this->arr_content['err_pageinit']}." );
        console.error( error );

    }

} );

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

        $Html = new Html();
        $fn_display_id = $Html->form_field_name( "opt-do-table-display-id" );
        $fn_display_email = $Html->form_field_name( "opt-do-table-display-email" );
        $fn_display_product = $Html->form_field_name( "opt-do-table-display-product" );
        $fn_display_product_sku = $Html->form_field_name( "opt-do-table-display-product-sku" );
        $fn_display_order = $Html->form_field_name( "opt-do-table-display-order" );
        $fn_display_note = $Html->form_field_name( "opt-do-table-display-note" );

        $Translations = new Translations();
        $arr_translations = $Translations->to_array();

        $html = <<< HTML

        <div id="page-plugin" class="wrap">

        $html_title

        <hr class="wp-header-end">

        $html_nav

        <h2> {$this->arr_content['header']} </h2>

        <p class="preamble"></p>

          <div class="submit-status" style="display:none;"></div>

          <div id="content">

            <form id="form-settings">
            <input type="hidden" name="set" value="display">
    
            <fieldset class="border">
              <legend>{$this->arr_content['display_columns_tables']}:</legend>

                <div class="flex-row">
                  <div class="flex-column flex-column-1">

                    <div class="flex-row">
                      <span class="form-control-wrapper form-control-checkbox-line">
                          <input type="checkbox" id="$fn_display_id" name="$fn_display_id" value="1"> 
                          {$arr_translations['id']}
                      </span>
                    </div>
                    
                    <div class="flex-row">
                      <span class="form-control-wrapper form-control-checkbox-line">
                          <input type="checkbox" id="$fn_display_email" name="$fn_display_email" value="1"> 
                          {$arr_translations['email']}
                      </span>
                    </div>

                    <div class="flex-row">
                      <span class="form-control-wrapper form-control-checkbox-line">
                          <input type="checkbox" id="$fn_display_order" name="$fn_display_order" value="1"> 
                          {$arr_translations['order']}
                      </span>
                    </div>                    

                    <div class="flex-row">
                      <span class="form-control-wrapper form-control-checkbox-line">
                          <input type="checkbox" id="$fn_display_product_sku" name="$fn_display_product_sku" value="1"> 
                          {$arr_translations['product_sku']}
                      </span>
                    </div>

                    <div class="flex-row">
                      <span class="form-control-wrapper form-control-checkbox-line">
                          <input type="checkbox" id="$fn_display_note" name="$fn_display_note" value="1"> 
                          {$arr_translations['note']}
                      </span>
                    </div>

                  </div>
                </div>

            </fieldset>

            <fieldset>
            <div class="flex-row">
                <div class="flex-column">
                    <input class="button button-primary button-submit" type="submit" value="{$arr_translations['update']}">
                </div>
            </div>

            </form>

        </div>
        
        </div> <!-- /wrap -->

        <script>$js</script>

HTML;

        if ( ! $use_echo ) {
            return $html;
        } 

        echo $html;

    }

}

