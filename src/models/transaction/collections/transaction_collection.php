<?php

/**
 * Class Herd_Transaction_Collection | models/herd-transaction/collections/herd-transaction-collection.php
 * 
 * @author Innocow
 * @copyright 2017 Innocow
 */

namespace Innocow\Stock_Records\Models\Transaction\Collections;

use Innocow\Stock_Records\Models\Transaction\Transaction;

/**
 * A collection of transaction entity objects.
 */
class Transaction_Collection {

    /** Begin legacy abstract object **/

    //
    //
    // Properties
    //
    //

    /**
     * The total number of records in the query, not the number of records in the collection.
     *
     * @var integer
     */
    private $total = null;

    /**
     * An array of transaction entity objects.
     *
     * @var array
     */
    protected $collection = [];

    /**
     * Gets the total number of records in the query.
     *
     * @return integer The total number of records.
     */
    public function get_total() {

        return $this->total;

    }

    /**
     * Sets the total number of records in the query.
     *
     * @param integer $total The total number of records.
     */
    public function set_total( $total ) {

        $this->total = intval( $total );

    }

    /**
     * Gets the array of collection objects.
     *
     * @return array An array of objects.
     */
    public function get_collection( $return_with_key_ids=true ) {

        if ( $return_with_key_ids ) {
            return $this->collection;
        } else {

            $array_unmarked = [];
            foreach( $this->collection as $T ) {
                $array_unmarked[] = $T;
            }

            return $array_unmarked;

        }

    }

    /**
     * Gets the array of collection objects.
     *
     * @param array $collection An array of objects.
     */
    public function set_collection( $collection ) {

        $this->collection = $collection;

    }
    
    /** End legacy abstract object **/    

    //
    //
    // Properties
    //
    //

    /**
     * The total sum of the debit columns in the query.
     *
     * @var integer
     */
    private $total_sum_debits = null;

    /**
     * The total sum of the credit columns in the query.
     *
     * @var integer
     */
    private $total_sum_credits = null;

    /**
     * The relevant balances (starting/ending) for the products in the collection.
     */
    protected $balances = [];

    //
    //
    // Constructor
    //
    //

    /**
     * Initialise the object.
     */
    public function __construct() {

    }

    //
    //
    // Accessors
    //
    //

    /**
     * Gets the total sum of the debit columns in the query.
     *
     * @return integer The sum of debits in query.
     */
    public function get_total_sum_debits() {

        return $this->total_sum_debits;

    }

    /**
     * Sets the total sum of the debit columns in the query.
     *
     * @param integer $total_sum_debits The sum of debits in query.
     */
    public function set_total_sum_debits( $total_sum_debits ) {

        $this->total_sum_debits = intval( $total_sum_debits );

    }

    /**
     * Gets the total sum of the credit columns in the query.
     *
     * @return integer The sum of credits in query.
     */
    public function get_total_sum_credits() {

        return $this->total_sum_credits;

    }

    /**
     * Sets the total sum of the credit columns in the query.
     *
     * @param integer $total_sum_credits The sum of credits in query.
     */
    public function set_total_sum_credits( $total_sum_credits ) {

        $this->total_sum_credits = intval( $total_sum_credits );

    }


    public function get_balances() {

        return $this->balances;

    }

    public function set_balances( array $balances ) {

        $this->balances = $balances;

    }


    //
    //
    // Helpers
    //
    //

    public function add( Transaction $Transaction ) {

        $this->collection[$Transaction->get_id()] = $Transaction;
        
    }

    public function get_balance() {

        return $this->get_total_sum_debits() - $this->get_total_sum_credits();
        
    }

    public function is_initial_transaction() {

        if ( $this->get_balance() === 0 && $this->get_total() === 0 ) {

            return true;

        }

        return false;

    }

}
