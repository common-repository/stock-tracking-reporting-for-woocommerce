<?php

/**
 * Class Transaction | models/transaction/entities/transaction.php
 * 
 * @author Innocow
 * @copyright 2017 Innocow
 */

namespace Innocow\Stock_Records\Models\Transaction;

/**
 * An entity representing a transaction.
 *
 * Note that transactions in Herd follow accounting rules. Therefore, as inventory are assets debits
 * increase the quantity and credits decrease the quantity.
 */
class Transaction {

    //
    //
    // Properties
    //
    //

    /**
     * The unique ID of the transaction.
     *
     * @var integer
     */
    private $id = null;

    /**
     * The unique ID of the product the transaction is for.
     * 
     * With WP-WC, the product ID maps to the WC product ID (which is an alias for the WP post ID.)
     *
     * @var integer
     */
    private $product_id = null;

    /**
     * The unique ID of the order if applicable.
     *
     * With WP-WC, the order ID maps to the WC order ID.
     *
     * @var integer
     */
    private $order_id = null;

    /**
     * The unique ID of the user that the transaction originated from.
     *
     * In WP-WC, the user ID maps to the users ID.
     *
     * @var integer
     */
    private $user_id = null;

    /**
     * The unique ID of non-registered users for the transaction.
     */
    private $customer_id = null;

    /**
     * The amount to debit or debited in the transaction.
     *
     * As inventory is an asset, in accounting terms a 'debit' to an asset implies an increase. 
     * This value increases the quantity of the product.
     *
     * @var integer
     */ 
    public $debit = null;

    /**
     * The amount to credit or credited in the transaction.
     *
     * As inventory is an asset, in accounting terms a 'credit' to an asset implies a decrease.  
     * This value decreases the quantity of the product.
     *
     * @var integer
     */     
    public $credit = null;
    
    /**
     * A descriptive note on the transaction.
     *
     * @var string
     */
    private $note = null;

    /**
     * Whether or not the transaction was manually entered (not automatic from a sale/refund.) 
     *
     * @var boolean
     */
    private $is_manual = false;

    /**
     * Whether or not to display the transaction.
     *
     * @var boolean
     */
    private $is_hidden = false;

    /**
     * Whether or not the transaction is deleted.
     * 
     * @var boolean
     */
    private $is_deleted = false;

    /**
     * The date of the transaction. Stored as the UTC epoch timestamp.
     *
     * @var integer
     */
    private $timestamp_created = null;

    /**
     * The date of the last update. Stored as the UTC epoch timestamp.
     *
     * @var integer
     */
    private $timestamp_updated = null;

    //
    //
    // Constructor
    //
    //
    
    /**
     * Initialises the object.
     *
     * @param integer $id The transaction ID.
     */
    public function __construct( $id=null ) { 

        if ( $id ) {
            $this->set_id( $id );
        }

    }

    //
    //
    // Accessors
    //
    //

    /**
     * Gets the unique ID of the transaction.
     *
     * @return integer The transaction ID.
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Sets the unique ID of the transaction.
     *
     * @param integer $id The transaction ID.
     *
     * @throws InvalidArgumentException if invalid ID.
     */
    public function set_id( int $id ) {

        if ( intval( $id ) < 1 ) {
            throw new \InvalidArgumentException( "Invalid transaction ID." );
        }

        $this->id = $id;

    }

    /**
     * Gets the unique ID of the product the transaction is for.
     *
     * @return integer The product ID.
     */
    public function get_product_id() {
        return $this->product_id;
    }

    /**
     * Sets the unique ID of the product the transaction is for.
     *
     * @param integer $product_id The product ID.
     *
     * @throws InvalidArgumentException if invalid product ID.
     */
    public function set_product_id( int $product_id ) {

        if ( intval( $product_id ) < 1 ) {
            throw new \InvalidArgumentException( "Invalid product ID." );
        }

        $this->product_id = $product_id;

    }

    /**
     * Gets the unique ID of the order.
     *
     * @return integer The order ID.
     */
    public function get_order_id() {
        return $this->order_id;
    }

    /**
     * Sets the unique ID of the order.
     *
     * @param integer $order_id The order ID.
     *
     * @throws InvalidArgumentException if invalid order ID.
     */
    public function set_order_id( int $order_id ) {

        if ( intval( $order_id ) < 1 ) {
            throw new \InvalidArgumentException( "Invalid order ID." );
        }

        $this->order_id = $order_id;

    }

    /**
     * Gets the unique ID of the user that the transaction originated from.
     * 
     * @return integer The user ID.
     */
    public function get_user_id() {
        return $this->user_id;
    }

    /**
     * Sets the unique ID of the user that the transaction originated from.
     *
     * @param integer $user_id The user ID.
     *
     * @throws InvalidArgumentException if invalid user ID.
     */
    public function set_user_id( int $user_id ) {

        if ( $user_id < 1 ) {
            throw new \InvalidArgumentException( "Invalid user ID." );
        }

        $this->user_id = $user_id;

    }


    /**
     * Gets the unique ID of the customer that the transaction originated from.
     * 
     * Currently unused. See $WC_Order and the alias of get_user_id() and get_customer_id().
     * 
     * @return integer The customer ID.
     */
    public function get_customer_id() {
        return $this->customer_id;
    }

    /**
     * Sets the unique ID of the customer that the transaction originated from. (Currently unused.)
     *
     * Currently unused. See $WC_Order and the alias of get_user_id() and get_customer_id().
     *
     * @param integer $customer_id The user ID.
     *
     * @throws InvalidArgumentException if invalid user ID.
     */
    public function set_customer_id( int $customer_id ) {

        if ( $customer_id < 1 ) {
            throw new \InvalidArgumentException( "Invalid customer ID ( $customer_id )" );
        }

        $this->customer_id = $customer_id;

    }

    /**
     * Gets the debit (increase) change in the transaction.
     *
     * @return integer The debit change.
     */
    public function get_debit() {
        return $this->debit;
    }

    /**
     * Sets the debit (increase) change in the transaction.
     *
     * @param integer $debit The debit change.
     *
     * @throws InvalidArgumentException if debit is less than 1.
     */
    public function set_debit( int $debit ) {

        if ( intval( $debit ) < 0 ) {
            throw new \InvalidArgumentException( "Debit value cannot be negative." );
        }

        $this->debit = $debit;

    }

    /**
     * Gets the credit (decrease) change in the transaction.
     *
     * @return integer The credit change.
     */
    public function get_credit() {

        return $this->credit;

    }

    /**
     * Sets the credit (decrease) change in the transaction.
     *
     * @param integer $credit The credit change.
     *
     * @throws InvalidArgumentException if credit is less than 1.
     */
    public function set_credit( int $credit ) {

        if ( intval( $credit ) < 0 ) {
            throw new \InvalidArgumentException( "Credit value cannot be negative." );
        }

        $this->credit = $credit;

    }

    /**
     * Gets a descriptive note on the transaction.
     *
     * @return string A note.
     */
    public function get_note() {
        return $this->note;
    }

    /**
     * Sets a descriptive note on the transaction.
     *
     * @param string $note A note.
     */
    public function set_note( $note ) {

        $tr_note = trim( $note );
        $limit = 256;

        if ( mb_strlen( $tr_note ) > $limit ) {
            throw new \InvalidArgumentException( "Note cannot be longer than $limit characters." );
        }

        $this->note = $tr_note;

    }

    /**
     * Gets whether transaction is manual or sets value if boolean is passed.
     *
     * @param boolean $is_manual True if transaction is manual, false if automatic.
     *
     * @return boolean True if manual, false otherwise.
     */
    public function is_manual( $is_manual=null ) {

        if ( $is_manual ) {
            $this->is_manual = (boolean) $is_manual;
        }

        return $this->is_manual;

    }

    /**
     * Gets whether transaction is hidden or sets value if boolean is passed.
     *
     * @param boolean $is_hidden True to hide transaction, false to unhide.
     *
     * @return boolean True if hidden, false if not.
     */
    public function is_hidden( $is_hidden=null ) {

        if ( $is_hidden ) {
            $this->is_hidden = (boolean) $is_hidden;
        }

        return $this->is_hidden;

    }

    /**
     * Gets whether transaction is deleted or sets value if boolean is passed.
     *
     * @param boolean $is_deleted True to delete, false to undelete.
     *
     * @return boolean True if deleted, false if not.
     */
    public function is_deleted( $is_deleted=null ) {

        if ( $is_deleted ) {
            $this->is_deleted = (boolean) $is_deleted;
        }

        return $this->is_deleted;

    }

    /**
     * Get the creation date as a timestamp.
     * 
     * @return int A timestamp.
     */
    public function get_timestamp_created() {
        return $this->timestamp_created;
    }

    /**
     * Get the creation date as a DateTime object.
     * 
     * @return DateTime A DateTime object.
     */
    public function get_timestamp_created_as_dt() {

        if ( is_null( $this->timestamp_created ) ) {
            return null;
        }

        return ( new \DateTime() )->setTimestamp( $this->timestamp_created );

    }

    /**
     * Get the creation date as a formatted string.
     * 
     * @param string $format A date() compatible string.
     * 
     * @return string A formatted date string.
     */
    public function get_timestamp_created_as_string( $format="r" ) {

        if ( is_null( $this->timestamp_created ) ) {
            return null;
        }

        $DT = ( new \DateTime() )->setTimestamp( $this->timestamp_created );
        return $DT->format( $format );

    }

    /**
     * Set the creation date with a timestamp.
     *
     * @param int $timestamp A timestamp.
     */
    public function set_timestamp_created( int $timestamp ) {
        $this->timestamp_created = $timestamp;
    }

    /**
     * Set the creation date with a DateTime object..
     *
     * @param DateTime $DT A DateTime object.
     */
    public function set_timestamp_created_as_dt( \DateTime $DT ) {
        $this->timestamp_created = $DT->getTimestamp();
    }

    /**
     * Get the updated date as a timestamp.
     * 
     * @return int A timestamp.
     */
    public function get_timestamp_updated() {
        return $this->timestamp_updated;
    }

    /**
     * Get the updated date as a DateTime object.
     * 
     * @return DateTime A DateTime object.
     */
    public function get_timestamp_updated_as_dt() {

        if ( is_null( $this->timestamp_updated ) ) {
            return null;
        }

        return ( new \DateTime() )->setTimestamp( $this->timestamp_updated );

    }

    /**
     * Get the updated date as a formatted string.
     * 
     * @param string $format A date() compatible string.
     * 
     * @return string A date string.
     */
    public function get_timestamp_updated_as_string( $format="r" ) {

        if ( is_null( $this->timestamp_updated ) ) {
            return null;
        }

        $DT = ( new \DateTime() )->setTimestamp( $this->timestamp_updated );
        return $DT->format( $format );

    }

    /**
     * Set the updated date with a timestamp.
     * 
     * @param int $timestamp A timestamp.
     */
    public function set_timestamp_updated( int $timestamp ) {
        $this->timestamp_updated = $timestamp;
    }

    /**
     * Set the updated date with a DateTime object.
     * 
     * @param DateTime A DateTime object.
     */
    public function set_timestamp_updated_as_dt( \DateTime $DT ) {
        $this->timestamp_updated = $DT->getTimestamp();
    }    

    //
    //
    // Helpers
    //
    //

    /**
     * Gets the change in inventory from transaction.
     *
     * @return integer A signed integer of the change in inventory.
     */
    public function get_change() {

        if ( ! $this->get_debit() && ! $this->get_credit() ) {
            return null;
        }

        return $this->get_debit() - $this->get_credit();

    }

    /**
     * Accepts a signed integer and categorises it as a debit or credit.
     * 
     * @param integer A signed integer.
     */
    public function set_change( int $change ) {

        if ( $change < 0 ) {

            $this->set_credit( abs( $change ) );
            $this->set_debit( 0 );

        } else if ( $change > 0 ) {

            $this->set_credit( 0 );
            $this->set_debit( abs( $change ) );

        }

    }

    /**
     * Whether transaction increased the quantity.
     *
     * @return boolean True if increase, false otherwise.
     */
    public function is_increase() {

        if ( $this->get_change() > 0 ) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Whether transaction decreased the quantity.
     *
     * @return boolean True if decrease, false otherwise.
     */
    public function is_decrease() {
        return ! $this->is_increase();
    }

    //
    //
    // Wordpress/Woocommerce specific methods.
    //
    //

    /**
     * Gets the full url to the edit page of a Wordpress post item.
     *
     * @param integer $post_id The post ID.
     *
     * @return string A Url.
     */
    private function full_url_edit_post( int $post_id ) {

        if ( ! function_exists( "admin_url" ) ) {
            throw new \RuntimeException( "Wordpress function missing: admin_url()" );
        }

        $post_path = "post.php?post=$post_id&action=edit";
        $url = admin_url( $post_path );

        return $url;

    }

    /**
     * Get the full url to the order edit page.
     * 
     * @return string|null A Url, or NULL if order id is null.
     */
    public function get_order_url() {

        if ( is_null( $this->get_order_id() ) ) {
            return null;
        }

        return $this->full_url_edit_post( $this->get_order_id() );

    }

    /**
     * Get the full url to the product edit page.
     * 
     * @return string|null A Url, or NULL if product id is unavailable.
     */
    public function get_product_url() {

        if ( is_null( $this->get_product_id() ) ) {
            return null;
        }

        return $this->full_url_edit_post( $this->get_product_id() );

    }

    /**
     * Get the product name.
     * 
     * @return string|null The product name, or NULL if product id or name is unavailable.
     */
    public function get_product_name() {

        if ( ! function_exists( "wc_get_product" ) ) {
            throw new \RuntimeException( "Woocommerce function missing: wc_get_product()" );
        }

        if ( is_null( $this->get_product_id() ) ) {
            return null;
        }

        $WC_Product = wc_get_product( $this->get_product_id() );
        $name = $WC_Product->get_title();

        if ( empty( $name ) ) {
            return null;
        }

        return $name;

    }


    /**
     * Get the product SKU.
     * 
     * @return string|null The product SKU, or NULL if product id or SKU is unavailable.
     */
    public function get_product_sku() {

        if ( ! function_exists( "wc_get_product" ) ) {
            throw new \RuntimeException( "Woocommerce function missing: wc_get_product()" );
        }

        if ( is_null( $this->get_product_id() ) ) {
            return null;
        }

        $WC_Product = wc_get_product( $this->get_product_id() );
        $sku = $WC_Product->get_sku();

        if ( empty( $sku ) ) {
            return null;
        }

        return $sku;

    }

    /**
     * Get a string to display for the product. It will try in descending order the name, sku 
     * then id and will return null if neither is available to use.
     * 
     * @return string|null A string or NULL if product details are unavailable.
     */
    public function get_product_display_safe() {

        if ( ! is_null( $this->get_product_name() ) ) {
            return $this->get_product_name();
        }

        if ( ! is_null( $this->get_product_sku() ) ) {
            return $this->get_product_sku();
        }

        if ( ! is_null( $this->get_product_id() ) ) {
            return $this->get_product_id();
        }

        return null;

    }

    /**
     * Get the email of the WP admin user of the transaction.
     * 
     * @return string|null An email, or NULL if order id and user id and/or email is unavailable.
     */
    public function get_admin_email() {

        if ( ! class_exists( "\WP_User" ) ) {
            throw new \RuntimeException( "Wordpress class missing: WP_User" );
        }

        if ( ! $this->get_user_id() ) {
            return null;
        }

        $WP_User = new \WP_User( $this->get_user_id() );
        $email = $WP_User->get( "user_email" );

        return $email;

    }

    /**
     * Get the email of the WC customer of the transaction.
     * 
     * @return string|null An email, or NULL if order id and/or email is unavailable.
     */
    public function get_customer_email( ) {

        if ( ! class_exists( "\WC_Order" ) ) {
            throw new \RuntimeException( "Woocommerce class missing: WC_Order" );
        }

        if ( ! function_exists( "wc_get_order" ) ) {
            throw new \RuntimeException( "Woocommerce function missing: wc_get_order()" );
        }

        if ( is_null( $this->get_order_id() ) ) {
            return null;
        }

        if ( ! wc_get_order( $this->get_order_id() ) ) {
            return null;
        }

        $WC_Order = new \WC_Order( $this->get_order_id() ); 
        $email = $WC_Order->get_billing_email();

        if ( empty( $email ) ) {
            return null;
        }

        return $email;

    }

    //
    //
    // Magic Methods
    //
    //

    /**
     * Get the object as an array.
     * 
     * @param boolean $as_json Return as JSON string.
     * 
     * @return array|string An array, or JSON string.
     */
    public function to_array( array $arr_exclude_keys=[], $as_json=false ) {

        $arr_return = [
            "id" => $this->get_id(),
            "order_id" => $this->get_order_id(),
            "product_id" => $this->get_product_id(),
            "user_id" => $this->get_user_id(),
            "note" => $this->get_note(),
            "is_manual" => $this->is_manual(),
            "is_hidden" => $this->is_hidden(),
            "is_deleted" => $this->is_deleted(),
            "timestamp_created" => $this->get_timestamp_created(),
            "timestamp_updated" => $this->get_timestamp_updated(),
            "debit" => $this->get_debit(),
            "credit" => $this->get_credit(),
            "change" => $this->get_change(),
            "is_decrease" => $this->is_decrease(),
        ];

        foreach ( $arr_exclude_keys as $key ) {
            unset( $arr_return[$key] );
        }

        if ( $as_json ) {
            return json_encode( $arr_return );
        }

        return $arr_return;

    }

    /**
     * Get the object as an array including WP/WC details such as Urls, SKUs, etc.
     * 
     * @param boolean $as_json Return as JSON string.
     * 
     * @return array|string An array, or JSON string.
     */
    public function to_array_extra( array $arr_exclude_keys=[], $as_json=false ) {

        $arr_return = $this->to_array();
        $arr_return["order_url"] = $this->get_order_url();
        $arr_return["user_email_admin"] = $this->get_admin_email();
        $arr_return["user_email_customer"] = $this->get_customer_email();
        $arr_return["product_name"] = $this->get_product_name();
        $arr_return["product_url"] = $this->get_product_url();
        $arr_return["product_sku"] = $this->get_product_sku();

        foreach ( $arr_exclude_keys as $key ) {
            unset( $arr_return[$key] );
        }

        if ( $as_json ) {
            return json_encode( $arr_return );
        }

        return $arr_return;

    }

}
