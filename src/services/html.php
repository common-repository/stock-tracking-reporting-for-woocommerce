<?php

/**
 * Class Html | src/services/html.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Services;

class Html {

    private $arr_form_field_names = [];

    public function __construct() {

        $this->arr_form_field_names = [
            "transaction-id" => "t-id",
            "transaction-datetime-created" => "t-datetime-created",
            "transaction-datetime-updated" => "t-datetime-updated",
            "transaction-user-id" => "t-user-id",
            "transaction-user-email" => "t-user-email",
            "transaction-customer-email" => "t-customer-email",
            "transaction-product-display" => "t-product-display",
            "transaction-product-id" => "t-product-id",
            "transaction-product-name" => "t-product-name",
            "transaction-product-sku" => "t-product-sku",
            "transaction-stock-amount" => "t-stock-amount",
            "transaction-stock-change" => "t-stock-change",
            "transaction-order-id" => "t-order-id",
            "transaction-note" => "t-note",
            "filter-transaction-tstamp-created-after" => "tstamp-after",
            "filter-transaction-tstamp-created-before" => "tstamp-before",
            "filter-transaction-product-keywords" => "product-keywords",
            "filter-transaction-user-keywords" => "user-keywords",
            "filter-transaction-note-keywords" => "note-keywords",
            "filter-transaction-order-id" => "order-id",
            "display-sortby" => "sortby",
            "display-sortdir" => "sortdir",
            "display-limit" => "limit",
            "display-page" => "page",
            "do-sync-levels" => "do-sync-levels",
            "do-filter-only-manual" => "do-filter-only-manual",
            "opt-do-table-display-id" => "display-id",
            "opt-do-table-display-order" => "display-order",
            "opt-do-table-display-product-sku" => "display-product-sku",
            "opt-do-table-display-email" => "display-email",
            "opt-do-table-display-note" => "display-note",
            "exp-timestamp-start" => "export-timestamp-start",
            "exp-document-type" => "export-document-type",
            "do-balances" => "do-balances",
            "opt-notice-hide-few-results" => "hide-few-results",
        ];

    }

    public function form_field_name( $key ) {

        if ( isset( $this->arr_form_field_names[$key] ) ) {
            return $this->arr_form_field_names[$key];
        }

        return null;

    }

}
