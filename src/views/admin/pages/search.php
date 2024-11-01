<?php

/**
 * Class Search | src/views/pages/search.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Views\Admin\Pages;

use Innocow\Stock_Records\Services\Html;
use Innocow\Stock_Records\Services\Translations;
use Innocow\Stock_Records\Services\WP_Options;

class Search {

    private $arr_content = [];

    public function __construct() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $tr_key = $Stock_Records->get_translation_key();

        $this->arr_content["err_pageinit"] = __( 
            "Page is unavailable. Unexpected error with page initialisation.",
            $tr_key
        );
        $this->arr_content["err_searchquery"] = __( 
            "Transactions log is unavailable. Unexpected error with transactions query.",
            $tr_key
        );
        $this->arr_content["label_filters"] = __( "Filtering Options", $tr_key );
        $this->arr_content["preamble1"] = __( 
            "Note that this plugin can only keep track of transactions made after it has been installed and activated."
            , $tr_key 
        );
        $this->arr_content["dont_show_again"] = __( "Don't show again", $tr_key );

    }

    private function javascript( $use_echo=false ) {

        $Html = new Html();
        $Translations = new Translations();
        $WP_Options = new WP_Options();

        $js = <<< JAVASCRIPT

document.addEventListener( "DOMContentLoaded", function( eventDocumentLoaded ) {

    let cnButtonFilterOptions = "button-options";
    let cnButtonReset = "button-reset";
    let cnTableTransactions = "transactions";
    let cnTableDisplayOptions = "tablenav-displayopts";
    let cnTableTotalResults = "displaying-num";
    let cnTablePagination = "pagination-links";
    let fnPage = "{$Html->form_field_name( 'display-page' )}";
    let fnSortBy = "{$Html->form_field_name( 'display-sortby' )}";
    let fnSortDir = "{$Html->form_field_name( 'display-sortdir' )}";
    let fnLimit = "{$Html->form_field_name( 'display-limit' )}";

    let noticesJSON = {$WP_Options->notices_as_json()}
    let columnsJSON = {$WP_Options->table_columns_preferred_as_json()}
    let translationsJSON = {$Translations->to_json()};

    let form = document.getElementById( "form-search" );
    let formFilters = document.getElementById( "form-search-filter" );
    let formNotices = document.getElementById( "form-notices" );    
    let innocowWP = new Innocow.WP();
    let page = new Innocow.WC.StockRecords.Html( document.getElementById( "page-search" ) );
    let table = new Innocow.WC.StockRecords.HtmlTable();
    let httpUrl = new Innocow.WC.StockRecords.HttpUrl( innocowWP.plugin.slug, innocowWP.urlAdmin );
    let httpRest = new Innocow.WC.StockRecords.HttpRest( 
        innocowWP.rest.url, 
        innocowWP.rest.namespace, 
        innocowWP.rest.nonce 
    );

    let initialiseFlatpickr = function() {

        let locale = innocowWP.locale.language || "en-US";

        if ( typeof( flatpickr ) != "undefined" ) {

            flatpickr( 
                ".flatpickr",
                {
                    locale: locale,
                    enableTime: true,
                    altInput: true,
                    dateFormat: "U",
                }
            );

        }

    }

    let resetForm = function() {

        form.reset();
        
        Array.from( form.getElementsByClassName( "flatpickr" ) ).map( el => {
            el.flatpickr().clear();
        } )

        initialiseFlatpickr();

    }

    let resetTableCanvas = function() {

        let canvases = [ 
            cnTableTransactions, 
            cnTableTotalResults, 
            cnTablePagination 
        ];

        canvases.map( canvas => { 
            Array.from( document.getElementsByClassName( canvas ) ).map( element => {
                element.innerHTML = "";
            } );
        } );

    }

    let showNotices = function() {

        let notices = document.querySelectorAll( ".notice" );
        Array.from( notices ).map( notice => { 

            if ( notice.classList.contains( "few-results" )
            && noticesJSON.hide_few_results == false ) {
                notice.style.display = "block";
            }

        } );

    }

    let updateNotice = function( element ) {

        let formData = new FormData();
        formData.append( element.name, 1 );
        formData.append( "set", "notices" );

        httpRest.putSettings( formData )
        .then( response => httpRest.parseNetworkResponse(
            response,
            function( e ) { throw new Error( e ); }
        ) )
        .then( responseJSON => {

            if ( responseJSON.hasOwnProperty( "isUpdated" ) && responseJSON.isUpdated ) { 
                return responseJSON;
            }

            throw new Error( responseJSON );            
            
        } )
        .catch( error => { 
            console.error( error );
        } );

    }    

    let lookupRowValue = function( header, record ) {
                    
        let transaction = new Innocow.WC.StockRecords.Transaction( record );
        transaction.locale = this.options.locale || "en-US";
        let value = transaction.lookupValueByHeader( header );

        if ( ! value ) {

            if ( header === "edit" ) {

                let iconEdit = document.createElement( "span" );
                iconEdit.classList.add( "dashicons", "dashicons-edit" );

                return httpUrl.createHtmlLink(
                    httpUrl.createDashUrlPluginEdit( record.id ),
                    iconEdit 
                );

            }

            if ( header === "delete" ) {

                let iconDelete = document.createElement( "span" );
                iconDelete.classList.add( "dashicons", "dashicons-trash" );

                return httpUrl.createHtmlLink(
                    httpUrl.createDashUrlPluginDelete( record.id ),
                    iconDelete
                );

            }

        }

        return value;

    }

    let addActionableColumns = function( columns ) {

        let newColumns = [];

        if ( Array.from( columns ).length > 0 ) {

            newColumns = columns.slice(0);
            newColumns.push( "edit", "delete" );
            
        }

        return newColumns;

    }

    let queryAndBuildTable = function( useDisplayOptions=false ) {

        httpRest.searchTransactions( form )
        .then( response => httpRest.parseNetworkResponse(
            response,
            function( e ) { page.displayErrorStatus( e ) }
        ) )
        .then( responseJSON => {

            if ( typeof( responseJSON ) !== "object" ) {
                throw new Error( responseJSON );
            }

            resetTableCanvas();

            table.translations = translationsJSON;
            table.options.doTranslation = true;
            table.options.locale = innocowWP.locale.withHyphen || "en-US";
            table.fieldNames.pagination.page = fnPage;
            table.fieldNames.displayOptions.sortBy = fnSortBy
            table.fieldNames.displayOptions.sortDir = fnSortDir;
            table.fieldNames.displayOptions.limit = fnLimit;
            table.callbackLookupRowValue = lookupRowValue;

            if ( responseJSON.hasOwnProperty( "data" ) ) {
                table.dataset = responseJSON.data;
            }

            if ( responseJSON.hasOwnProperty( "total_results" ) ) {
                if ( responseJSON.total_results > 0 ) {
                    table.appendTotalResults(
                        cnTableTotalResults,
                        responseJSON.total_results
                    );
                }
            }

            if ( responseJSON.hasOwnProperty( "pagination" ) ) {
                if ( responseJSON.pagination.pages > 1 ) {
                    table.appendPagination( 
                        cnTablePagination,
                        responseJSON.pagination.page, 
                        responseJSON.pagination.pages 
                    );
                }
            }

            if ( Array.from( columnsJSON ).length > 0 ) {
                table.headers = addActionableColumns( columnsJSON );
            }

            if ( useDisplayOptions ) {
                table.appendDisplayOptions( cnTableDisplayOptions, columnsJSON );
            }

            table.append( cnTableTransactions );

        } )
        .catch( error => {

            resetTableCanvas();
            page.displayErrorStatus( "{$this->arr_content['err_searchquery']}" );
            console.error( error );            

        } );

    }

    try {

        if ( noticesJSON ) {
            showNotices();
        }

        formNotices.addEventListener( "click", function( eventClick ) {

            if ( eventClick.target.name 
            === "{$Html->form_field_name('opt-notice-hide-few-results')}" ) {
                updateNotice( eventClick.target );                
            }

        } );

        form.addEventListener( "submit", function( eventSubmit ) {

            if ( eventSubmit.target.id === form.id ) {

                eventSubmit.preventDefault();
                table.syncCurrentPage( 1 );
                queryAndBuildTable();

            }

        } );

        form.addEventListener( "click", function( eventClick ) {

            if ( eventClick.target.classList.contains( cnButtonReset ) ) {
                resetForm();
            }
            
            if ( eventClick.target.classList.contains( cnButtonFilterOptions ) ) {

                if ( formFilters.style.display === "none" ) {
                    formFilters.style.display = "block";
                } else {
                    formFilters.style.display = "none";
                }

            }

            if ( eventClick.target.classList.contains( table.classNames.pagination.button ) ) {

                table.syncCurrentPage( eventClick.target.dataset.page );
                queryAndBuildTable();

            }

        } );

        form.addEventListener( "change", function( eventChange ) {

            if ( eventChange.target.classList.contains( table.classNames.displayOptions.element ) ) {

                table.syncDisplayOption( eventChange.target.name, eventChange.target.value );
                queryAndBuildTable();

            }

        } );

        form.addEventListener( "keydown", function( eventKeydown ) {

            if ( eventKeydown.target.classList.contains( table.classNames.pagination.detailsCurrent ) ) {

                table.syncCurrentPage( eventKeydown.target.value );

                if ( eventKeydown.code === "Enter" ) {
                                    
                    queryAndBuildTable();
                    eventKeydown.preventDefault();                    

                }

            }

        } );

        initialiseFlatpickr();
        queryAndBuildTable( true );

    } catch ( error ) {
        
        page.displayErrorStatus( "{$this->arr_content['err_pageinit']}" );
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

        $Translations = new Translations();
        $arr_translations = $Translations->to_array();

        $Html = new Html();
        $fn_hide_few_results = $Html->form_field_name( "opt-notice-hide-few-results" );

        if ( icwcsr_is_premium_loaded() 
        && class_exists( "\Innocow\Stock_Records_Premium\Views\Admin\Pages\Search_Filter" ) ) {
            $Search_Filter = new \Innocow\Stock_Records_Premium\Views\Admin\Pages\Search_Filter();
        } else {
            $Search_Filter = new Search_Filter();    
        }
        $html_filter_form = $Search_Filter->html();

        $html = <<< HTML

        <div id="page-search" class="wrap">

        $html_title

        <hr class="wp-header-end">

        $html_nav

        <div class="notice warning few-results" style="display:none;">
          <p>{$this->arr_content['preamble1']}</p>
          <form id="form-notices">
            <div class="dont-show-again">
              <input type="checkbox" name="{$fn_hide_few_results}">
              {$this->arr_content['dont_show_again']}
            </div>        
          </form>
        </div>
          
        <div class="submit-status" style="display:none;"></div>        

        <form id="form-search">

          {$html_filter_form}
       
          <div class="tablenav top">
            <div class="alignleft">
              <span class="tablenav-displayopts"></span>
              <input id="bttn-toggle-options" type="button" class="button button-options" value="{$this->arr_content['label_filters']}">
            </div>
            
            <div class="tablenav-pages tablenav-pages-top">
              <span class="displaying-num"></span>
              <span class="pagination-links"></span>
            </div>
          </div>


          <div class="transactions">
          </div>

          <div class="tablenav bottom">
            <div class="tablenav-displayopts"></div>
            <div class="tablenav-pages tablenav-pages-bottom">
                <span class="pagination-links"></span>
            </div>
          </div>

        </form>
        </div>

        <script>$js</script>

HTML;

        if ( ! $use_echo ) {
            return $html;
        } 

        echo $html;

    }

}
