<?php

/**
 * Class Http | src/services/http.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Services;

use Innocow\Stock_Records\Exceptions\HttpRequestValidationException;
use Innocow\Stock_Records\Services\Html;

class Http {

    private function is_in_array( $field, array $array_params ) {

        $txt_error_msg = "Request is missing the field: ";

        if ( ! isset( $array_params[$field] ) ) {
            throw new HttpRequestValidationException( 
                $txt_error_msg . $field
            );
        }

        return true;

    }

    public function validate_request_param( $field, array $array_params, $is_required=true ) {

        $Html = new Html();
        $fn_id = $Html->form_field_name( "transaction-id" );
        $fn_datetime_created = $Html->form_field_name( "transaction-datetime-created" );
        $fn_datetime_updated = $Html->form_field_name( "transaction-datetime-updated" );
        $fn_user_id = $Html->form_field_name( "transaction-user-id" );
        $fn_user_email = $Html->form_field_name( "transaction-user-email" );
        $fn_product_display = $Html->form_field_name( "transaction-product-display" );
        $fn_product_id = $Html->form_field_name( "transaction-product-id" );
        $fn_product_name = $Html->form_field_name( "transaction-product-name" );
        $fn_product_sku = $Html->form_field_name( "transaction-product-sku" );
        $fn_stock_amount = $Html->form_field_name( "transaction-stock-amount" );
        $fn_stock_change = $Html->form_field_name( "transaction-stock-change" );
        $fn_order_id = $Html->form_field_name( "transaction-order-id" );
        $fn_note = $Html->form_field_name( "transaction-note" );
        $fn_sync_levels = $Html->form_field_name( "do-sync-levels" );        

        if ( $field === "_id" ) {

            if ( $is_required ) {
                $this->is_in_array( $field, $array_params );
            }

            if ( isset( $array_params[$field] ) ) {

                if ( intval( $array_params[$field] ) < 0 ) {
                    throw new HttpRequestValidationException(
                        "The transaction id is invalid."
                    );
                }

            }

            return true;

        }

        if ( $field === $fn_id ) {

            if ( $is_required ) {
                $this->is_in_array( $field, $array_params );
            }

            if ( isset( $array_params[$field] ) ) {

                if ( intval( $array_params[$field] ) < 0 ) {
                    throw new HttpRequestValidationException(
                        "The transaction id is invalid."
                    );
                }

            }

            return true;

        }        

        if ( $field === $fn_datetime_created ) {

            if ( $is_required ) {
                $this->is_in_array( $field, $array_params );
            }

            if ( isset( $array_params[$field] ) ) {

                if ( intval( $array_params[$field] ) < 1 ) {
                    throw new HttpRequestValidationException(
                        "The date provided is not a valid date."
                    );
                }

            }

            return true;

        }

        if ( $field === $fn_datetime_updated ) {

            if ( $is_required ) {
                $this->is_in_array( $field, $array_params );
            }

            if ( isset( $array_params[$field] ) ) {

                if ( intval( $array_params[$field] ) < 1 ) {
                    throw new HttpRequestValidationException(
                        "The date provided is not a valid date."
                    );
                }

            }

            return true;

        }        

        if ( $field === "user" ) {

            if ( $is_required ) {

                $this->is_in_array( $fn_user_id, $array_params );
                $this->is_in_array( $fn_user_email, $array_params );

            }

            if ( isset( $array_params[$fn_user_id] ) 
            && isset( $array_params[$fn_user_email ] ) ) {

                $WC_User_Submitted = get_userdata( intval( $array_params[$fn_user_id] ) );

                if ( ! $WC_User_Submitted ) {
                    throw new HttpRequestValidationException(
                        "The user id is invalid. There is no user with that id."
                    );
                } else {
                    if ( $WC_User_Submitted->get( "user_email" ) 
                    !== $array_params[$fn_user_email] ) {
                        throw new HttpRequestValidationException(
                            "The submitted user email and id combination is invalid. Try reselecting."
                        );
                    }
                }

            }

            return true;

        }

        if ( $field === "product" ) {

            if ( $is_required ) {

                $this->is_in_array( $fn_product_id, $array_params );
                $this->is_in_array( $fn_product_name, $array_params );

            }

            if ( isset( $array_params[$fn_product_id] ) 
            && isset( $array_params[$fn_product_name ] ) ) {            

                $WC_Product_Submitted = wc_get_product( 
                    intval( $array_params[$fn_product_id] ) 
                );

                if ( ! $WC_Product_Submitted ) {
                    throw new HttpRequestValidationException(
                        "The product id is invalid. There is no product with that id."
                    );
                } else {
                    if ( $WC_Product_Submitted->get_title() 
                    !== $array_params[$fn_product_name] ) {
                        throw new HttpRequestValidationException(
                            "The submitted product name and id combination are invalid. Try reselecting."
                        );
                    }
                }

            }

            return true;

        }

        if ( $field === "stock" ) {

            $array_valid_stock_change = [
                "i",
                "d",
                "increase",
                "decrease"
            ];

            if ( $is_required ) {

                $this->is_in_array( $fn_stock_amount, $array_params );
                $this->is_in_array( $fn_stock_change, $array_params );

            }

            if ( isset( $array_params[$fn_stock_amount] )
            && isset( $array_params[$fn_stock_change] ) ) {

                if ( intval( $array_params[$fn_stock_amount] ) <= 0 ) { 
                    throw new HttpRequestValidationException( 
                        "The stock amount is invalid. It cannot be zero or negative." 
                    );
                }

                if ( ! in_array( $array_params[$fn_stock_change], $array_valid_stock_change ) ) {
                    throw new HttpRequestValidationException(
                        "The stock change value is invalid."
                    );
                }

            }

            return true;

        }

        if ( $field === $fn_order_id ) {

            if ( $is_required ) {
                $this->is_in_array( $field, $array_params );
            }

            if ( isset( $array_params[$field] ) && ! empty( $array_params[$field] ) ) {

                if ( intval( $array_params[$field] ) <= 0 ) {
                    throw new HttpRequestValidationException(
                        "The order id is invalid. It cannot be zero or negative."
                    );
                }

            }

            return true;

        }

        if ( $field === $fn_note 
        || $field === $fn_sync_levels ) {

            if ( $is_required ) {
                return $this->is_in_array( $field, $array_params );
            }

            return true;

        }

    }

}
