<?php

/**
 * Class Transaction_Search | models/transaction/transaction_search.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 * 
 */

namespace Innocow\Stock_Records\Models\Transaction;

/**
 * An entity representing a transaction.
 *
 * Note that transactions in Herd follow accounting rules. Therefore, as inventory are assets
 * debits increase the quantity and credits decrease the quantity.
 */
class Transaction_Search {

    //
    //
    // Properties
    //
    //

    /**
     * The number of results to return per search.
     *
     * @var integer
     */
    private $search_limit = null;

    /**
     * The offset of the search results to display.
     *
     * @var integer
     */
    private $search_offset = null;

    /**
     * The field to sort the results by.
     *
     * @var string
     */
    private $search_order = null;

    /**
     * The direction for the sort.
     *
     * @var boolean
     */
    private $is_search_order_descending = null;

    /**
     * Product ID to search for.
     *
     * @var integer
     */
    private $product_id = null;

    /**
     * Product keywords to search for.
     * 
     * @var string
     */
    private $product_keywords = null;

    /**
     * Signed integer of minimum change to search for.
     * 
     * @var integer
     */
    private $changed_minimum = null;

    /**
     * Signed integer of minimum change to search for.
     * 
     * @var integer
     */
    private $changed_maximum = null;

    /**
     * Order ID to search for.
     *
     * @var integer
     */
    private $order_id = null;

    /**
     * User ID to search for. This field only records registered users.
     *
     * @var integer
     */
    private $user_id = null;

    /**
     * Notes to search for.
     *
     * @var string
     */
    private $note = null;

    /**
     * Whether transaction is a manual entry.
     *
     * @var boolean
     */
    private $is_manual = null;

    /**
     * Whether transaction is hidden.
     *
     * @var boolean
     */
    private $is_hidden = null;

    /**
     * Whether transaction is deleted.
     *
     * @var boolean
     */
    private $is_deleted = false;

    /**
     * Creation timestamp start boundary to search for. (UTC.)
     *
     * @var integer
     */
    private $timestamp_created_after = null;

    /**
     * Creation timestamp end boundary to search for. (UTC.)
     *
     * @var integer
     */
    private $timestamp_created_before = null;

    /**
     * Update timestamp start boundary to search for. (UTC.)
     *
     * @var integer
     */
    private $timestamp_updated_after = null;

    /**
     * Update timestamp end boundary to search for. (UTC.)
     *
     * @var integer
     */
    private $timestamp_updated_before = null;

    /**
     * Email to search for.
     * 
     * @var string
     */
    private $aux_email = null;

    /**
     * Enable calculating the balances of each product for the search.
     * 
     * @var boolean
     */
    private $do_product_balances = false;

    //
    //
    // Constructor
    //
    //

    /**
     * Initialise object.
     */
    public function __construct() {

        $this->set_search_limit( 10 );
        $this->set_search_offset( 0 );
        $this->is_search_order_descending( true );
        $this->is_deleted( false );
        $this->do_product_balances( false );

    }

    //
    //
    // Accessors
    //
    //

    /**
     * Gets the number of results to return per search.
     *
     * @return integer The search limit.
     */
    public function get_search_limit() {

        return $this->search_limit;

    }

    /**
     * Sets the number of results to return per search.
     *
     * @param integer $search_limit The search limit.
     */
    public function set_search_limit( int $search_limit ) {

        $this->search_limit = abs( intval( $search_limit ) );

    }

    /**
     * Gets the offset of the search results to display.
     *
     * @return integer The search offset.
     */
    public function get_search_offset() {

        return $this->search_offset;

    }

    /**
     * Sets the offset of the search results to display.
     *
     * @param integer $search_offset The search offset.
     */
    public function set_search_offset( int $search_offset ) {
        
        $this->search_offset = abs( intval( $search_offset ) );

    }

    /**
     * Gets the search order.
     *
     * @return string The search order.
     */
    public function get_search_order() {

        return $this->search_order;

    }


    /**
     * Sets the search order.
     *
     * @param string $search_order The search order
     */
    public function set_search_order( string $search_order ) {

        $safe_search_order = filter_var( 
            $search_order, 
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK
        );        

        $this->search_order = $safe_search_order;

    }

    /**
     * Determines if search order descending.
     *
     * @param boolean $search_order_descending True for descending, false for ascending.
     *
     * @return boolean True for descending, false for ascending.
     */
    public function is_search_order_descending( $search_order_descending=null ) {

        if ( ! is_null( $search_order_descending ) ) {

            $this->is_search_order_descending = (boolean) $search_order_descending;

        }

        return $this->is_search_order_descending;

    }

    /**
     * Gets the product ID to search for.
     *
     * @return integer The product ID.
     */
    public function get_product_id() {

        return $this->product_id;

    }

    /**
     * Sets the product ID to search for.
     *
     * @param integer $product_id The product ID.
     */
    public function set_product_id( int $product_id ) {

        $this->product_id = intval( $product_id );

    }

    /**
     * Gets the product keywords to search for.
     * 
     * @param boolean $as_array If true return the string as an array tokenised by $delimiter.
     * @param string $delimiter The character to separate the string by.
     *
     * @return integer The product ID.
     */
    public function get_product_keywords( $as_array=false, $delimiter=" " ) {

        if ( $as_array ) {

            return str_getcsv( $this->product_keywords, $delimiter );

        }

        return $this->product_keywords;

    }

    /**
     * Gets the product words as an array.
     *
     * @param string $delimiter The character to separate the string by.
     * 
     * @return array The product words as an array.
     */
    public function get_product_keywords_as_array( $delimiter=" " ) {

        return $this->get_product_keywords( true, $delimiter );

    }


    /**
     * Sets the product keywords to search for
     *
     * @param integer $product_keywords The product ID.
     */
    public function set_product_keywords( string $product_keywords ) {

        $safe_product_keywords = filter_var( 
            $product_keywords, 
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK
        );    

       $this->product_keywords = $safe_product_keywords;

    }

    /**
     * Gets the minimum amount of quantity change.
     *
     * @return integer Signed integer of quantity change.
     */
    public function get_changed_minimum() {

        return $this->changed_minimum;

    }

    /**
     * Sets the minimum change to search for.
     * 
     * @param integer @change Signed integer of quantity change.
     */
    public function set_changed_minimum( int $changed_minimum ) {

        $this->changed_minimum = intval( $changed_minimum );

    }

    /**
     * Gets the maximum amount of quantity change.
     *
     * @return integer Signed integer of quantity change.
     */
    public function get_changed_maximum() {

        return $this->changed_maximum;

    }

    /**
     * Sets the maximum change to search for.
     * 
     * @param integer @change Signed integer of quantity change.
     */
    public function set_changed_maximum( int $changed_maximum ) {

        $this->changed_maximum = intval( $changed_maximum );

    }


    /**
     * Gets the order ID to search for.
     *
     * @return integer The order ID.
     */
    public function get_order_id() {

        return $this->order_id;

    }

    /**
     * Sets the order ID to search for.
     *
     * @param integer $order_id The order ID.
     */
    public function set_order_id( int $order_id ) {

        $this->order_id = intval( $order_id );

    }

    /**
     * Gets the user ID to search for. Note this is only for registered users.
     *
     * @return integer The user ID.
     */
    public function get_user_id() {

        return $this->user_id;

    }

    /**
     * Sets the user ID to search for. Note this is only for registered users.
     *
     * @param integer $user_id The user ID.
     */
    public function set_user_id( int $user_id ) {

        $this->user_id = intval( $user_id );

    }

    /**
     * Gets the note to search for.
     * 
     * @param boolean If true return the string as an array tokenised by $delimiter.
     * @param string The character to separate the string by.
     *
     * @return mixed The note.
     */
    public function get_note( $as_array=false, $delimiter=" " ) {

        if ( $as_array ) {

            return str_getcsv( $this->note, $delimiter );

        }

        return $this->note;

    }

    /**
     * Gets the note as an array.
     *
     * @param string The character to separate the string by.
     * 
     * @return array The note as an array.
     */
    public function get_note_as_array( $delimiter=" " ) {

        return $this->get_note( true, $delimiter );

    }
    
    /**
     * Sets the note to search for.f
     *
     * @param string $note The note.
     */
    public function set_note( string $note ) {

        $safe_note = filter_var( 
            $note, 
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK
        );

        $this->note = $safe_note;

    }

    /**
     * Searches whether transaction is a manual entry or not.
     *
     * @param boolean $is_manual Set whether to search for manual transactions or not.
     *
     * @return boolean Whether to search for manual transactions or not.
     */
    public function is_manual( $is_manual=null ) {

        if ( ! is_null( $is_manual ) ) {

            $this->is_manual = (boolean) $is_manual;

        }

        return $this->is_manual;

    }

    /**
     * Searches whether transaction is hidden.
     *
     * @param boolean $is_hidden Set whether to search for hidden transactions or not.
     *
     * @return boolean Whether to search for hidden transactions or not.
     */
    public function is_hidden( $is_hidden=null ) {

        if ( ! is_null( $is_hidden ) ) {

            $this->is_hidden = (boolean) $is_hidden;

        }

        return $this->is_hidden;

    }

    /**t
     * Searches whether transaction is deleted.
     *
     * @param boolean $is_deleted Set whether to search for deleted transactions or not.
     *
     * @return boolean Whether to search for deleted transactions or not.
     */
    public function is_deleted( $is_deleted=null ) {

        if ( $is_deleted ) {

            $this->is_deleted = (boolean) $is_deleted;

        }

        return $this->is_deleted;

    }

    /**
     * Gets the creation timestamp start boundary to search for. (UTC.)
     *
     * @return integer A UTC timestamp.
     */
    public function get_timestamp_created_after() {

        return $this->timestamp_created_after;        

    }

    public function get_timestamp_created_after_as_dt() {

        if ( is_null( $this->timestamp_created_after ) ) {
            return null;
        }

        return ( new \DateTime() )->setTimestamp( $this->timestamp_created_after );

    }

    public function get_timestamp_created_after_as_string( $format="r" ) {

        if ( is_null ( $this->timestamp_created_after ) ) {
            return null;
        }

        $DateTime = ( new \DateTime() )->setTimestamp( $this->timestamp_created_after );
        return $DateTime->format( $format );

    }

    /**
     * sets the creation timestamp start boundary to search for. (UTC.)
     *
     * @param \DateTime $Timestamp_Created_After A UTC timestamp.
     */
    public function set_timestamp_created_after( $date ) {

        if ( gettype( $date ) === "object" && get_class( $date ) === "DateTime" ) {
    
            $date->setTimezone( new \DateTimeZone( "UTC" ) );
            $this->timestamp_created_after = $date->getTimestamp();

        }

        $this->timestamp_created_after = intval( $date );

    }

    /**
     * Gets the creation timestamp end boundary to search for. (UTC.)
     *
     * @return integer A UTC timestamp.
     */
    public function get_timestamp_created_before() {

        return $this->timestamp_created_before;

    }

    public function get_timestamp_created_before_as_dt() {

        if ( is_null( $this->timestamp_created_before ) ) {
            return null;            
        }
    
        return ( new \DateTime() )->setTimestamp( $this->timestamp_created_before );

    }

    public function get_timestamp_created_before_as_string( $format="r" ) {

        if ( is_null( $this->timestamp_created_before ) ) {
            return null;
            
        }
    
        $DateTime = ( new \DateTime() )->setTimestamp( $this->timestamp_created_before );
        return $DateTime->format( $format );

    }

    /**
     * Sets the creation timestamp end boundary to search for. (UTC.)
     *
     * @param \DateTime $Timestamp_Created_Before A UTC timestamp.
     */
    public function set_timestamp_created_before( $date ) {

        if ( gettype( $date ) === "object" && get_class( $date ) === "DateTime" ) {
    
            $date->setTimezone( new \DateTimeZone( "UTC" ) );
            $this->timestamp_created_before = $date->getTimestamp();

        }

        $this->timestamp_created_before = intval( $date );

    }

    /**
     * Gets the Update timestamp start boundary to search for. (UTC.)
     *
     * @return integer A UTC timestamp.
     */
    public function get_timestamp_updated_after() {

        return $this->timestamp_updated_after;

    }

    public function get_timestamp_updated_after_as_dt() {

        if ( is_null( $this->timestamp_updated_after ) ) {
            return null;
        }

        return ( new \DateTime() )->setTimestamp( $this->timestamp_updated_after );

    }

    public function get_timestamp_updated_after_as_string( $format="r" ) {

        if ( is_null( $this->timestamp_updated_after ) ) {
            return null;
        }

        $DateTime = ( new \DateTime() )->setTimestamp( $this->timestamp_updated_after );
        return $DateTime->format( $format );

    }    

    /**
     * Sets the Update timestamp start boundary to search for. (UTC.)
     *
     * @param \DateTime $Timestamp_Updated_After A UTC timestamp.
     */
    public function set_timestamp_updated_after( $date ) {

        if ( gettype( $date ) === "object" && get_class( $date ) === "DateTime" ) {
    
            $date->setTimezone( new \DateTimeZone( "UTC" ) );
            $this->timestamp_updated_after = $date->getTimestamp();

        }

        $this->timestamp_updated_after = intval( $date );

    }

    /**
     * Gets the Update timestamp end boundary to search for. (UTC.).
     *
     * @return integer A UTC timestamp.
     */
    public function get_timestamp_updated_before( $format="r" ) {

        if ( ! is_null( $this->timestamp_updated_before ) ) {

            $DateTime = ( new \DateTime() )->setTimestamp( $this->timestamp_updated_before );
            return $DateTime->format( $format );

        }

        return $this->timestamp_updated_before;

    }

    public function get_timestamp_updated_before_as_dt() {

        if ( is_null( $this->timestamp_updated_before ) ) {
            return null;
        }
            
        return ( new \DateTime() )->setTimestamp( $this->timestamp_updated_before );

    }

    public function get_timestamp_updated_before_as_string( $format="r" ) {

        if ( is_null( $this->timestamp_updated_before ) ) {
            return null;
        }
            
        $DateTime = ( new \DateTime() )->setTimestamp( $this->timestamp_updated_before );
        return $DateTime->format( $format );

    }

    /**
     * Sets the Update timestamp end boundary to search for. (UTC.).
     *
     * @param \DateTime $Timestamp_Updated_Before A UTC timestamp.
     */
    public function set_timestamp_updated_before( $date ) {

        if ( gettype( $date ) === "object" && get_class( $date ) === "DateTime" ) {
    
            $date->setTimezone( new \DateTimeZone( "UTC" ) );
            $this->timestamp_updated_after = $date->getTimestamp();

        }        

        $this->timestamp_updated_before = intval( $date );

    }

    /**
     * Email address to search auxillary tables by.
     * 
     * @return string An email address.
     */
    public function get_aux_email() {

        return $this->aux_email;

    }

    /**
     * Set the keywords to filter the user/customer email by.
     *
     * @param string $filter_note_keywords String of keywords.
     */
    public function set_aux_email( string $email ) {

        if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {

            throw new \InvalidArgumentException( "Not a valid email: $email" );

        }

        $this->aux_email = $email;

    }


    /**
     * Enable calculating the product balances when the search query is sent.
     * 
     * Using this will require multiple extra database queries, so only enable it if required.
     *
     * @param boolean $is_deleted Set whether to calculate product balances.
     *
     * @return boolean Whether to search for deleted transactions or not.
     */
    public function do_product_balances( $do_product_balances=null ) {

        if ( ! is_null( $do_product_balances ) ) {

            $this->do_product_balances = (boolean) $do_product_balances;

        }

        return $this->do_product_balances;

    }

    //
    //
    // Helpers
    //
    //

    public function reset_is_hidden() {
        $this->is_hidden = null;
    }

    public function reset_date_filters( $created_filter=true, $updated_filter=true) {

        if ( $created_filter ) {
            
            $this->timestamp_created_after = null;
            $this->timestamp_created_before = null;

        }

        if ( $updated_filter ) {
        
            $this->timestamp_updated_after = null;
            $this->timestamp_updated_before = null;

        }

    }

}
