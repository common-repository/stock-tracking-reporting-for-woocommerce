<?php

/**
 * Class Delete | src/views/pages/delete.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Views\Admin\Pages;

use Innocow\Stock_Records\Services\Translations;

class About {

    private $arr_content = [];

    public function __construct() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $tr_key = $Stock_Records->get_translation_key();

        $this->arr_content["url_addon"] = "https://innocow.com/software";
        $this->arr_content["licensing_link"] = __(
'<a class="navigation_item" name="licensing"><h3>Plugin Licensing</h3></a>
 <div class="licensing"><p>The plugin licensing details can be found on this <a href="%s">page</a>.</p></div>'
        , $tr_key );
        $this->arr_content["introduction"] = __( 
'<a class="navigation_item" name="about_plugin"><h3>About This Plugin</h3></a>
<div class="introduction">
  <p>This plugin gives you the ability to fully track all the inventory changes that occur with the products in your Woocommerce store.</p>
</div>
<div class="features">
  <p><b>Core version features include:</b></p>
  <ul class="free">
    <li>Viewing the tracked sales, refunds and product inventory increases and decreases in the products pages.</li>
  </ul>
  <p><b>Premium add-on features include:</b></p>
  <ul class="paid">
    <li>Searching and filtering the tracked transactions by date, product name, sku, customer email, and order numbers.</li>
    <li>Adding manual transactions for offline sales while keeping the product inventory levels synchronised.</li>
    <li>Generating a monthly report of transactions including the starting and ending balances of each product.</li>
    <li>Printing the reports or exporting them as CSV or TSV.</li>
  </ul>
  <p><b>Available in the following languages:</b></p>
  <ul class="translations">
    <li>French</li>
  </ul>
</div>'      
        , $tr_key );
        $this->arr_content["usage"] = __(
'<a class="navigation_item" name="usage"><h3> Plugin Usage </h3></a>
<div class="notes">
  <p>Some notes to keep in mind with using this plugin:</p>
  <ul class="">
    <li>This plugin can only track the transactions that happen when it is active.</li>
    <li>Upon activation, it will check if there are differences between its internal balances and the product balances and will make adjustments if there are any.</li>
    <li>Previous transactions, sales and orders cannot be tracked. Upon installation and activation, the transactions page will display a notice that no transactions have yet been recorded.</li>
    <li>Transactions that have been made in Woocommerce (such as sales, cancellations, quantity changes, etc) cannot be edited or deleted except for the note field.</li>
  </ul>
</div>'
        , $tr_key );
        $this->arr_content["about_us_header"] = __( "About Us ", $tr_key );
        $this->arr_content["about_us_company"] = __(
'<p><a href=\'https://innocow.com\' target=\'_blank\'>Innocow</a> is a boutique software and web development company based in Montréal, Canada.<p>'
        , $tr_key );
        $this->arr_content["about_us_upgrade"] = __(
"<p><b>If you found this plugin useful please make sure you support us by buying the premium add-on.</b></p><p><a href='%s' target='_blank'>Upgrade your plugin &rarr;</a></p>"
        , $tr_key );
        $this->arr_content["about_us_thanks"] = __(
'<p><b>Thanks for supporting this plugin and purchasing the professional version!</b></p>'
        , $tr_key );
        $this->arr_content["contact"] = __(
'<a class="navigation_item" name="usage"><h3> Contact </h3></a>
<div class="contact">
  <p>If you have any questions or comments, check out our <a href="https://innocow.com" target="_blank">website</a>.</p>
  <p>If you think you\'ve discovered a bug, report it at <a href="https://innocow.com/contact" target="_blank">our bugs page.</a>
</div>'
        , $tr_key );

        $this->arr_content["err_pageinit"] = __( 
            "Page is unavailable. Unexpected error with page initialisation."
            , $tr_key
        );

        $this->arr_content["header"] = __( "About & Help", $tr_key );

    }

    private function javascript( $use_echo=false ) {

        $Translations = new Translations();
        $arr_translations = $Translations->to_array();      

        $js = <<< JAVASCRIPT

document.addEventListener( "DOMContentLoaded", function( eventDOMContentLoaded ) {

    let buildNavigation = function() {

        let navItems = document.getElementsByClassName( "navigation_item" );
        let navSelects = document.getElementsByClassName( "navigation" );

        Array.from( navSelects ).map( select => {

            let optionTitle = document.createElement( "option" );
            let optionSpacer = document.createElement( "option" );

            optionTitle.innerHTML = "{$arr_translations['navigation']}";
            optionTitle.value = "";

            optionSpacer.innerHTML = "----";
            optionSpacer.value = "";

            select.append( optionTitle );
            select.append( optionSpacer );

            Array.from( navItems ).map( navItem => {
                
                let option = document.createElement( "option" );
                option.value = navItem.name;
                option.innerHTML = navItem.querySelector( "h3" ).innerText;

                select.append( option );

            } );

        } );

    }

    try {

        buildNavigation();

        document.addEventListener( "change", eventChange => {

            let element = eventChange.target;

            if ( element.classList.contains( "navigation" ) ) {
                
                let id = element.options[element.selectedIndex].value;
                if ( id ) {
                  window.location = (""+window.location).replace(/#[A-Za-z0-9_]*$/,'') + "#"+id;
                }

            }

        } );

    } catch( error ) {
        
        console.error( error );
        page.displayErrorStatus( "{$this->arr_content['err_pageinit']}" );

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

        $html_plugin_version = "";
        $Stock_Records = $GLOBALS["ICWCSR"];

        if ( icwcsr_is_premium_loaded() ) {

            $Premium = $GLOBALS["ICWCSR-PREMIUM"];
            $link_licensing = menu_page_url( $Premium->get_plugin_slug() . "-license", false );

            $html_plugin_version = $this->arr_content["about_us_thanks"];
            $html_licensing_link = sprintf( 
                $this->arr_content["licensing_link"],
                $link_licensing
            );
        
        } else {
        
            $html_plugin_version = sprintf(
                $this->arr_content["about_us_upgrade"],
                $this->arr_content["url_addon"]
            );
            $html_licensing_link = "";
        
        }

        $html = <<< HTML

        <div id="page-about" class="wrap">

        $html_title

        <hr class="wp-header-end">

        $html_nav

        <h2> {$this->arr_content["header"]} </h2>

        <select class="navigation">
        </select>

        <a class="navigation_item" name="about_us">
          <h3>{$this->arr_content["about_us_header"]}</h3>
        </a>
        <div class="announcement">
            {$this->arr_content["about_us_company"]}
            $html_plugin_version
        </div>

        {$this->arr_content["introduction"]}

        {$this->arr_content["usage"]}

        {$html_licensing_link}        

        {$this->arr_content["contact"]}

        <script>$js</script>

HTML;

        if ( ! $use_echo ) {
            return $html;
        } 

        echo $html;

    }

}