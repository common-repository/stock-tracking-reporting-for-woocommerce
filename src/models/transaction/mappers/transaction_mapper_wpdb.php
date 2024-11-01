<?php

/**
 * Class Transaction_WPDB | models/mappers/transaction_wpdb.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Models\Transaction\Mappers;

use Innocow\Stock_Records\Models\Transaction\Transaction;
use Innocow\Stock_Records\Storage\WPDB;

/**
 * Convenience class for subsequent mapping classes.
 */
class Transaction_Mapper_WPDB extends WPDB {

    /**
     * The tablename for the transactions table.
     *
     * @var string
     */
    private $tablename = "icwcsr_transactions";

    /**
     * The format for MySQL's DATETIME type.
     *
     * @var string
     */
    private $dateformat_mysql_datetime = "Y-m-d H:i:s";

    /**
     * The format for MySQL's DATE type.
     *
     * @var string
     */
    private $dateformat_mysql_date = "Y-m-d";

    //
    //
    // Accessors
    //
    //

    /**
     * Gets the tablename for the transactions table.
     *
     * @param boolean $is_incl_prefix Whether to return the full tablename including WP prefix.
     *
     * @return string The tablename for the transactions table.
     */
    public function get_tablename( $is_incl_prefix=true ) {

        if ( $is_incl_prefix ) {
        
            return $this->get_wpdb()->prefix . $this->tablename;

        } else {

            return $this->tablename; 

        }

    }

    /**
     * Gets the format for MySQL's DATETIME type.
     *
     * @return string
     */
    public function get_dateformat_mysql_datetime() {

        return $this->dateformat_mysql_datetime;

    }

    /**
     * Gets the format for MySQL's DATE type.
     *
     * @return string
     */
    public function get_dateformat_mysql_date() {

        return $this->dateformat_mysql_date;

    }

    //
    //
    // Methods
    //
    //

    public function object_to_array( Transaction $Transaction ) {

        $values = array();
        $types = array();

        if ( $Transaction->get_product_id() ) {

            $values["product_id"] = $Transaction->get_product_id();
            $types[] = "%d";

        }

        if ( $Transaction->get_order_id() ) {

            $values["order_id"] = $Transaction->get_order_id();
            $types[] = "%d";

        }

        if ( $Transaction->get_user_id() ) {

            $values["user_id"] = $Transaction->get_user_id();
            $types[] = "%d";

        }

        if ( ! is_null( $Transaction->get_debit() ) ) {

            if ( $Transaction->get_debit() === 0 ) {
                $values["debit"] = null;
            } else {
                $values["debit"] = $Transaction->get_debit();
            }
            $types[] = "%d";

        }

        if ( ! is_null( $Transaction->get_credit() ) ) {

            if ( $Transaction->get_credit() === 0 ) {
                $values["credit"] = null;
            } else {
                $values["credit"] = $Transaction->get_credit();
            }
            $types[] = "%d";

        }

        if ( ! is_null( $Transaction->get_note() ) ) {

            if ( $Transaction->get_note() === "" ) {
                $values["note"] = null;
            } else {
                $values["note"] = $Transaction->get_note();
            }
            $types[] = "%s";

        }

        if ( $Transaction->is_manual() ) {

            $values["is_manual"] = $Transaction->is_manual();
            $types[] = "%d";

        }

        if ( $Transaction->is_hidden() ) {

            $values["is_hidden"] = $Transaction->is_hidden();
            $types[] = "%d";

        }

        if ( $Transaction->is_deleted() ) {

            $values["is_deleted"] = $Transaction->is_deleted();
            $types[] = "%d";

        }

        if ( $Transaction->get_timestamp_created() ) {

            $DT_Created = new \DateTime();
            $DT_Created->setTimezone( new \DateTimeZone( "UTC" ) );
            $DT_Created->setTimestamp( $Transaction->get_timestamp_created() );

            $values["date_created"] = $DT_Created->format(
                $this->get_dateformat_mysql_datetime()
            );
            $types[] = "%s";

        }

        if ( $Transaction->get_timestamp_updated() ) {

            $DT_Updated = new \DateTime();
            $DT_Updated->setTimezone( new \DateTimeZone( "UTC" ) );
            $DT_Updated->setTimestamp( $Transaction->get_timestamp_updated() );

            $values["date_updated"] = $DT_Updated->format( 
                $this->get_dateformat_mysql_datetime() 
            );
            $types[] = "%s";

        }

        return array( 
            "values" => $values, 
            "types" => $types
        );

    }

    /**
     * Assign a database result to the transaction object.
     *
     * @param Transaction $Transaction The transaction object.
     * @param object $Result The database result.
     */
    public function array_to_object( Transaction $Transaction, $Result ) {

        if ( isset( $Result->id ) ) {
            $Transaction->set_id( $Result->id );
        }

        if ( isset( $Result->product_id ) ) {
            $Transaction->set_product_id( $Result->product_id );
        }

        if ( isset( $Result->order_id ) ) {
            $Transaction->set_order_id( $Result->order_id );
        }

        if ( isset( $Result->user_id ) ) {
            $Transaction->set_user_id( $Result->user_id );
        }

        if ( isset( $Result->debit ) ) {
            $Transaction->set_debit( $Result->debit );
        }

        if ( isset( $Result->credit ) ) {
            $Transaction->set_credit( $Result->credit );
        }

        if ( isset( $Result->note ) ) {
            $Transaction->set_note( $Result->note );
        }

        if ( isset( $Result->is_manual ) ) {
            $Transaction->is_manual( $Result->is_manual );
        }

        if ( isset( $Result->is_hidden ) ) {
            $Transaction->is_hidden( $Result->is_hidden );
        }

        if ( isset( $Result->is_deleted ) ) {
            $Transaction->is_deleted( $Result->is_deleted );
        }

        if ( isset( $Result->timestamp_created ) ) {
            $Transaction->set_timestamp_created( $Result->timestamp_created );
        } elseif ( isset( $Result->date_created ) ) {
            $Transaction->set_timestamp_created_as_dt( new \DateTime( $Result->date_created ) );
        }

        if ( isset( $Result->timestamp_updated ) ) {
            $Transaction->set_timestamp_updated( $Result->timestamp_updated );
        } elseif ( isset( $Result->date_updated ) ) {
            $Transaction->set_timestamp_updated_as_dt( new \DateTime( $Result->date_updated ) );
        }

    }

}