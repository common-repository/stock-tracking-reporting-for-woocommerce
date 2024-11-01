<?php

/**
 * Class Translations | src/services/translations.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Services;

class Translations {

    private $arr_translations = [];

    public function __construct() {

        $Stock_Records = $GLOBALS["ICWCSR"];
        $tr_key = $Stock_Records->get_translation_key();        

        $this->arr_translations["no_transactions_found"] = __( "No transactions found", $tr_key );
        $this->arr_translations["only_manual"] = __( "Only manual transactions", $tr_key );
        $this->arr_translations["update_inventory_in_wc"] = __(
            "Update inventory in Woocommerce",
            $tr_key
        );

        $this->arr_translations["date"] = __( "Date", $tr_key );
        
        $this->arr_translations["change"] = __( "Change", $tr_key );
        $this->arr_translations["email"] = __( "Email", $tr_key );
        $this->arr_translations["end"] = __( "End", $tr_key );
        $this->arr_translations["id"] = __( "ID", $tr_key );
        $this->arr_translations["note"] = __( "Note", $tr_key );
        $this->arr_translations["order"] = __( "Order", $tr_key );
        $this->arr_translations["product"] = __( "Product", $tr_key );
        $this->arr_translations["product_name"] = __( "Product Name", $tr_key );
        $this->arr_translations["product_name_and_sku"] = __( "Product & SKU", $tr_key );
        $this->arr_translations["product_sku"] = __( "Product SKU", $tr_key );
        $this->arr_translations["result"] = __( "Result", $tr_key );
        $this->arr_translations["results"] = __( "Results", $tr_key );
        $this->arr_translations["sku"] = __( "SKU", $tr_key );
        $this->arr_translations["start"] = __( "Start", $tr_key );
        $this->arr_translations["transaction"] = __( "Transaction", $tr_key );
        $this->arr_translations["transactions"] = __( "Transactions", $tr_key );
        $this->arr_translations["balances"] = __( "Balances", $tr_key );
        $this->arr_translations["user"] = __( "User", $tr_key );
        $this->arr_translations["customer"] = __( "Customer", $tr_key );
        $this->arr_translations["format"] = __( "Format", $tr_key );

        $this->arr_translations["navigation"] = __( "Navigation", $tr_key );
        $this->arr_translations["sortby"] = __( "Sort By", $tr_key );
        $this->arr_translations["ascending"] = __( "Ascending", $tr_key );
        $this->arr_translations["descending"] = __( "Descending", $tr_key );
        $this->arr_translations["increase"] = __( "Increase", $tr_key );
        $this->arr_translations["decrease"] = __( "Decrease", $tr_key );

        $this->arr_translations["submit"] = __( "Submit", $tr_key );
        $this->arr_translations["add"] = __( "Add", $tr_key );
        $this->arr_translations["update"] = __( "Update", $tr_key );
        $this->arr_translations["edit"] = __( "Edit", $tr_key );
        $this->arr_translations["delete"] = __( "Delete", $tr_key );
        $this->arr_translations["search"] = __( "Search", $tr_key );
        $this->arr_translations["reset"] = __( "Reset", $tr_key );
        $this->arr_translations["back"] = __( "Back", $tr_key );
        $this->arr_translations["options"] = __( "Options", $tr_key );
        $this->arr_translations["after"] = __( "After", $tr_key );
        $this->arr_translations["before"] = __( "Before", $tr_key );

    }

    public function to_array() {
        return $this->arr_translations;
    }

    public function to_json() {
        return json_encode( $this->arr_translations );
    }

}



