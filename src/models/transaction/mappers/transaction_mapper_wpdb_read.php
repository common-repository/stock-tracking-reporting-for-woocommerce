<?php

/**
 * Class Transaction_Mapper_WPDB_Read | src/models/transaction/mappers/transaction_mapper_wpdb_read.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Models\Transaction\Mappers;

use Innocow\Stock_Records\Models\Transaction\Transaction;

class Transaction_Mapper_WPDB_Read extends Transaction_Mapper_WPDB {

    /**
     * 
     * @return Transaction The Transaction object with properties populated.
     * @return null If no records were found.
     */
    public function read( Transaction $Transaction, $throw_exception=false ) {

        $table_name = $this->get_tablename();

        if ( ! $Transaction->get_id() ) {

            throw new \InvalidArgumentException( "Cannot read transaction without ID." );

        }

        $sql = "SELECT  * FROM $table_name WHERE id=%d";
        $sql_values = array( $Transaction->get_id() );

        // https://developer.wordpress.org/reference/classes/wpdb/prepare/
        $sanitised_sql = $this->get_wpdb()->prepare( $sql, $sql_values );

        // https://developer.wordpress.org/reference/classes/wpdb/get_row/
        $Resultset = $this->get_wpdb()->get_row( $sanitised_sql );

        if ( empty( $Resultset ) ) {

            if ( $throw_exception ) {
                throw new \InvalidArgumentException( 
                    "Empty result encountered. Transaction does not exist." 
                );
            }

            return null;

        }

        $this->array_to_object( $Transaction, $Resultset );

        return $Transaction;

    }

    public function read_id( Transaction $Transaction ) {

        $table_name = $this->get_tablename();

        $sql = 
            "SELECT "
            . " id "
            . "FROM "
            . " $table_name "
            . "WHERE "
            . " product_id=%d ";

        $sql_values = array(
            $Transaction->get_product_id()
        );

        //
        // The following method of query building is required because wpdb 
        // prepare() cannot handle null values properly (null escapes to an
        // empty string.)
        //

        if ( $Transaction->get_order_id() ) {
            
            $sql .= " AND order_id=%d";
            $sql_values[] = $Transaction->get_order_id();

        }

        if ( $Transaction->get_user_id() ) {
            
            $sql .= " AND user_id=%d";
            $sql_values[] = $Transaction->get_user_id();

        }
        
        if ( $Transaction->get_debit() ) {
            
            $sql .= " AND debit=%d";
            $sql_values[] = $Transaction->get_debit();

        }

        if ( $Transaction->get_credit() ) {
            
            $sql .= " AND credit=%d";
            $sql_values[] = $Transaction->get_credit();

        }

        if ( $Transaction->get_note() ) {
            
            $sql .= " AND note=%s";
            $sql_values[] = $Transaction->get_note();

        }

        if ( $Transaction->is_manual() ) {
            
            $sql .= " AND is_manual=%d";
            $sql_values[] = $Transaction->is_manual();

        }

        if ( $Transaction->is_hidden() ) {
            
            $sql .= " AND is_hidden=%d";
            $sql_values[] = $Transaction->is_hidden();

        }


        if ( $Transaction->is_deleted() ) {
            
            $sql .= " AND is_deleted=%d";
            $sql_values[] = $Transaction->is_deleted();

        }

        if ( $Transaction->get_timestamp_created() ) {
            
            $sql .= " AND date_created=%s";
            $sql_values[] = $Transaction->get_timestamp_created_as_string( 
                $this->get_dateformat_mysql_datetime() 
            );

        }
        
        // https://developer.wordpress.org/reference/classes/wpdb/prepare/
        $sanitised_sql = $this->get_wpdb()->prepare( $sql, $sql_values );

        // https://developer.wordpress.org/reference/classes/wpdb/get_row/
        $Resultset = $this->get_wpdb()->get_row( $sanitised_sql );

        if ( empty( $Resultset ) ) {

            throw new \RuntimeException( "Empty result encountered. Transaction does not exist." );

        }

        return $Resultset->id;

    }


}