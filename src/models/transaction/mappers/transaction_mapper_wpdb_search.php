<?php

/**
 * Class Transaction_WPDB_Search | models/herd-transaction/mapper/herd-transaction-wpdb-search.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Models\Transaction\Mappers;

use Innocow\Stock_Records\Models\Transaction\Transaction;
use Innocow\Stock_Records\Models\Transaction\Transaction_Search;
use Innocow\Stock_Records\Models\Transaction\Collections\Transaction_Collection;

/**
 * 
 */
class Transaction_Mapper_WPDB_Search extends Transaction_Mapper_WPDB {

    /**
     * Searches for the first match.
     *
     * @param Transaction_Search $Transaction_Search The transaction search
     * 
     * @todo Change typehint to interface.
     */
    public function search( Transaction_Search $Transaction_Search ) {

        $Transaction_Collection = new Transaction_Collection();

        $this->get_totals( $Transaction_Search, $Transaction_Collection );

        if ( $Transaction_Collection->get_total() > 0 ) {
            $this->get_results( $Transaction_Search, $Transaction_Collection );
        }

        if ( $Transaction_Search->do_product_balances() ) {
            $this->get_product_balances( $Transaction_Search, $Transaction_Collection );
        }
        
        return $Transaction_Collection;

    }

    private function get_totals( $Transaction_Search, $Transaction_Collection ) {

        $table_name = $this->get_tablename();

        $sql = 
            "SELECT "
            . " COUNT( id ) AS collection_total, "
            . " COALESCE( SUM( debit ), 0 ) AS collection_total_sum_debits, "
            . " COALESCE( SUM( credit ), 0 ) AS collection_total_sum_credits "
            . " FROM $table_name ";
        $sql .= $this->build_query_criteria( $Transaction_Search );

        $Resultset = $this->get_wpdb()->get_row( $sql );

        $Transaction_Collection->set_total( $Resultset->collection_total );
        $Transaction_Collection->set_total_sum_debits( $Resultset->collection_total_sum_debits );
        $Transaction_Collection->set_total_sum_credits( $Resultset->collection_total_sum_credits );

        return true;

    }

    private function get_product_balances( $Transaction_Search, $Transaction_Collection ) {

        $table_name = $this->get_tablename();        
        $balances = [];

        $products = $this->get_distinct_products( $Transaction_Search, $Transaction_Collection );

        foreach ( $products as $id ) {

            $sql_template = 
                "SELECT "
                . " COALESCE( SUM( debit ), 0 ) - COALESCE( SUM( credit ), 0 ) AS balance "
                . " FROM $table_name ";
            $start = 0;
            $end = 0;

            if ( $Transaction_Search->get_timestamp_created_after() ) {

                $Start_Balance_Search = clone( $Transaction_Search );
                $Start_Balance_Search->reset_date_filters();
                $Start_Balance_Search->reset_is_hidden();
                $Start_Balance_Search->set_product_id( $id );
                $Start_Balance_Search->set_timestamp_created_before( 
                    $Transaction_Search->get_timestamp_created_after()
                );

                $sql = $sql_template . $this->build_query_criteria( $Start_Balance_Search );

                $Result = $this->get_wpdb()->get_row( $sql );
                $start = $Result->balance;

            }

            if ( $Transaction_Search->get_timestamp_created_before() ) {

                $End_Balance_Search = clone( $Transaction_Search );
                $End_Balance_Search->reset_date_filters();
                $End_Balance_Search->reset_is_hidden();
                $End_Balance_Search->set_product_id( $id );
                $End_Balance_Search->set_timestamp_created_before( 
                    $Transaction_Search->get_timestamp_created_before()
                );
                
                $sql = $sql_template . $this->build_query_criteria( $End_Balance_Search );

                $Result = $this->get_wpdb()->get_row( $sql );
                $end = $Result->balance;

            } else {

                $End_Balance_Search = clone( $Transaction_Search );
                $End_Balance_Search->reset_date_filters();
                $End_Balance_Search->reset_is_hidden();
                $End_Balance_Search->set_product_id( $id );

                $sql = $sql_template . $this->build_query_criteria( $End_Balance_Search );

                $Result = $this->get_wpdb()->get_row( $sql );
                $end = $Result->balance;

            }

            $balances[] = [
                "id" => $id,
                //"name" => ( wc_get_product( $id ) )->get_title(),
                "start" => $start,
                "end" => $end
            ];

        }

        $Transaction_Collection->set_balances( $balances );

        return $balances;

    }

    private function get_distinct_products( $Transaction_Search, $Transaction_Collection ) {

        $table_name = $this->get_tablename();

        $Product_Search = clone( $Transaction_Search );
        $Product_Search->reset_date_filters();
        $Product_Search->set_timestamp_created_before(
            $Transaction_Search->get_timestamp_created_before()
        );

        $sql =
            "SELECT "
            . " DISTINCT( product_id ) AS distinct_product_id"
            . " FROM $table_name ";
        $sql .= $this->build_query_criteria( $Product_Search );
        $Resultset = $this->get_wpdb()->get_results( $sql );

        $distinct_product_ids = [];

        foreach( $Resultset as $Result ) {
            $distinct_product_ids[] = $Result->distinct_product_id;
        }

        return $distinct_product_ids;

    }

    /**
     * Note, this function will not run unless the prior get_totals() function returns 
     * more than zero records.
     */
    private function get_results( $Transaction_Search, $Transaction_Collection ) {

        $table_name = $this->get_tablename();

        $sql = "SELECT * ";
        $sql .= ", ( IFNULL( debit, 0 ) - IFNULL( credit, 0 ) ) AS balance ";
        $sql .= " FROM $table_name ";

        $sql .= $this->build_query_criteria( $Transaction_Search );
        $sql .= $this->build_query_order( $Transaction_Search );
        $sql .= $this->build_query_controls( $Transaction_Search );        

        $Resultset = $this->get_wpdb()->get_results( $sql );

        foreach( $Resultset as $Result ) {

            $Transaction = new Transaction();
            $this->array_to_object( $Transaction, $Result );

            $Transaction_Collection->add( $Transaction );

        }

        return true;

    }

    private function build_query_criteria( $Transaction_Search ) {

        $tn_wp_users = $this->get_prefixed_tablename( "users" );
        $tn_wp_posts = $this->get_prefixed_tablename( "posts" );
        $tn_wp_postmeta = $this->get_prefixed_tablename( "postmeta" );

        $sql = "";
        $sql_values = array();

        $sql .= "WHERE is_deleted=%d ";
        $sql_values[] = $Transaction_Search->is_deleted();

        if ( ! is_null( $Transaction_Search->is_hidden() ) ) {
        
            $sql .= "AND is_hidden=%d ";
            $sql_values[] = $Transaction_Search->is_hidden();

        }

        if ( ! is_null( $Transaction_Search->is_manual() ) ) {
        
            $sql .= "AND is_manual=%d ";
            $sql_values[] = $Transaction_Search->is_manual();

        }

        if ( $Transaction_Search->get_product_id() ) {

            $sql .= "AND product_id=%d ";
            $sql_values[] = $Transaction_Search->get_product_id();

        }

        if ( $Transaction_Search->get_product_keywords() ) { 

            $pname_where = [];
            $psku_where = []; 

            foreach( $Transaction_Search->get_product_keywords_as_array() as $keyword ) {

                $pname_where[] = " LOWER( post_title ) LIKE LOWER( %s ) ";
                $sql_values[] = "%$keyword%";

            }

            foreach( $Transaction_Search->get_product_keywords_as_array() as $keyword ) {

                $psku_where[] = " LOWER( meta_value ) LIKE LOWER( %s ) ";
                $sql_values[] = "%$keyword%";

            }

            $_sql_pn = " SELECT id FROM $tn_wp_posts WHERE post_type='product' AND (";
            $_sql_pn .= implode( " OR ", $pname_where );
            $_sql_pn .= " ) ";

            $_sql_ps = " SELECT post_id FROM $tn_wp_postmeta WHERE meta_key='_sku' AND ( ";
            $_sql_ps .= implode( " OR ", $psku_where );
            $_sql_ps .= " ) ";
            
            $sql .= " AND ( product_id IN ( $_sql_pn ) ";
            $sql .= " OR product_id IN ( $_sql_ps ) ) ";

        }

        if ( ! is_null( $Transaction_Search->get_changed_minimum() ) ) {

            $sql .= "AND (IFNULL(debit, 0) - IFNULL(credit,0)) >= %d ";
            $sql_values[] = $Transaction_Search->get_changed_minimum();

        }

        if ( ! is_null( $Transaction_Search->get_changed_maximum() ) ) {

            $sql .= "AND (IFNULL(debit, 0) - IFNULL(credit,0)) <= %d ";
            $sql_values[] = $Transaction_Search->get_changed_maximum();

        }

        if ( $Transaction_Search->get_order_id() ) {

            $sql .= "AND order_id=%d ";
            $sql_values[] = $Transaction_Search->get_order_id();

        }

        if ( $Transaction_Search->get_user_id() ) {

            $sql .= "AND user_id=%d ";
            $sql_values[] = $Transaction_Search->get_user_id();

        }

        if ( $Transaction_Search->get_note() ) {

            $sql .= " AND ( ";
            $index = 1;
            $element_count = count( $Transaction_Search->get_note_as_array() );

            foreach( $Transaction_Search->get_note_as_array() as $element ) {

                $sql .= " LOWER(note) LIKE LOWER(%s) ";
                $sql_values[] = "%$element%";

                if ( $index < $element_count ) {

                    $sql .= " OR ";

                }

                $index++;

            }

            $sql .= " ) ";

        }

        if ( $Transaction_Search->get_timestamp_created_after() ) {

            $sql .= "AND UNIX_TIMESTAMP( date_created ) >= %d ";
            $sql_values[] = $Transaction_Search->get_timestamp_created_after();

        }

        if ( $Transaction_Search->get_timestamp_created_before() ) {

            $sql .= "AND UNIX_TIMESTAMP( date_created ) <= %d ";
            $sql_values[] = $Transaction_Search->get_timestamp_created_before();

        }

        if ( $Transaction_Search->get_timestamp_updated_after() ) {

            $sql .= "AND UNIX_TIMESTAMP( date_updated ) >= %d ";
            $sql_values[] = $Transaction_Search->get_timestamp_updated_after();

        }

        if ( $Transaction_Search->get_timestamp_updated_before() ) {

            $sql .= "AND UNIX_TIMESTAMP( date_updated ) <= %d ";
            $sql_values[] = $Transaction_Search->get_timestamp_updated_before();

        }

        if ( $Transaction_Search->get_aux_email() ) {

            $sql .= "AND ( ";
            $sql .= " user_id IN ( SELECT id FROM $tn_wp_users WHERE LOWER( user_email ) = LOWER( %s ) )";
            $sql .= " OR order_id IN ( SELECT post_id FROM $tn_wp_postmeta WHERE ";
            $sql .= "  meta_key = '_billing_email' AND LOWER( meta_value ) = LOWER( %s ) ";
            $sql .= " ) ";
            $sql .= ") ";
            $sql_values[] = $Transaction_Search->get_aux_email();
            $sql_values[] = $Transaction_Search->get_aux_email();

        }

        return $this->get_wpdb()->prepare( $sql, $sql_values );

    }

    private function build_query_controls( $Transaction_Search ) {

        $sql = "";
        $sql_values = array();

        if ( $Transaction_Search->get_search_limit() ) {
            
            $sql .= " LIMIT %d ";
            $sql_values[] = $Transaction_Search->get_search_limit();

        }

        if ( $Transaction_Search->get_search_offset() ) {
            
            $sql .= " OFFSET %d ";
            $sql_values[] = $Transaction_Search->get_search_offset();

        }

        return $this->get_wpdb()->prepare( $sql, $sql_values );

    }

    private function build_query_order( $Transaction_Search ) {

        $sql = "";

        if ( $Transaction_Search->is_search_order_descending() ) {

            $direction = "DESC";

        } else {

            $direction = "ASC";            

        }        

        switch( $Transaction_Search->get_search_order() ) {

            case "id":
                $sql = "ORDER BY id $direction ";
                break;

            case "product_id":
                $sql = "ORDER BY product_id $direction ";
                break;

            case "order_id":
                $sql = "ORDER BY order_id $direction ";
                break;

            case "note":
                $sql = "ORDER BY note $direction ";
                break;

            case "user_id":
                $sql = "ORDER BY user_id $direction ";
                break;

            case "balance":
                $sql = "ORDER BY balance $direction ";
                break;

            case "is_manual":
                $sql = "ORDER BY is_manual $direction ";
                break;

            case "is_manual":
                $sql = "ORDER BY is_manual $direction ";
                break;

            case "is_hidden":
                $sql = "ORDER BY is_hidden $direction ";
                break;

            case "is_deleted":
                $sql = "ORDER BY is_deleted $direction ";
                break;

            default:
            case "date_created":
                $sql = "ORDER BY date_created $direction, id $direction ";
                break;

            case "date_updated":
                $sql = "ORDER BY date_updated $direction, id $direction ";
                break;

        }

        return $sql;

    }

}
