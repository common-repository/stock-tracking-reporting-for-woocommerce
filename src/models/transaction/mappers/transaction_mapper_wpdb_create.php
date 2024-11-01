<?php

/**
 * Class Transaction_Mapper_WPDB_Create | src/models/transaction/mappers/transaction_mapper_wpdb_create.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Models\Transaction\Mappers;

use Innocow\Stock_Records\Models\Transaction\Transaction;

/**
 * Mapper for create related funcionality.
 */
class Transaction_Mapper_WPDB_Create extends Transaction_Mapper_WPDB {

    /**
     * Creates a transaction entry in the transaction table.
     *
     * @param Transaction_Interface $Transaction The transaction object.
     *
     * @throws RuntimeException if incomplete transaction object or query failure.
     *
     * @return integer Number of rows affected.
     */
    public function create( Transaction $Transaction ) {

        if ( ! $Transaction->get_product_id() ) {
            throw new \RuntimeException( "Cannot create transaction without a product ID." );
        }

        // We're not using MySQL"s NOW() when creating the record for precision. There could be a
        // difference between the order date and the transaction entry.
        if ( is_null( $Transaction->get_timestamp_created() ) 
        && $Transaction->get_timestamp_created() < 1 ) {
            throw new \RuntimeException( "Cannot create transaction without a date set." );
        }

        $values_and_types_array = $this->object_to_array( $Transaction );

        $insert_values = $values_and_types_array["values"];
        $insert_types = $values_and_types_array["types"];

        // https://developer.wordpress.org/reference/classes/wpdb/insert/
        $rows_affected = $this->get_wpdb()->insert( 
            $this->get_tablename(),
            $insert_values, 
            $insert_types 
        );

        if ( $rows_affected !== 1 ) {

            throw new \RuntimeException( 
                "Fatal error creating transaction. Expected 1 affected row, recieved $rows_affected."
            );

        }

        // Assign the newly created transaction back to the object as its ID.
        $Read_Mapper = new Transaction_Mapper_WPDB_Read( $this->get_wpdb() );
        $Transaction->set_id( $Read_Mapper->read_id( $Transaction ) );

        return $rows_affected;

    }

}
