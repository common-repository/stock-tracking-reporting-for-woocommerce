<?php

/**
 * Class Storage | src/services/storage.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Services;

use Innocow\Stock_Records\Models\Transaction\Transaction;
use Innocow\Stock_Records\Models\Transaction\Transaction_Search;
use Innocow\Stock_Records\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Create;
use Innocow\Stock_Records\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Read;
use Innocow\Stock_Records\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Update;
use Innocow\Stock_Records\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Delete;
use Innocow\Stock_Records\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Search;

/**
 * Storage gateway for transaction mappers.
 */
class Storage {

    public function create( $Entity ) {

        if ( $Entity instanceof Transaction ) {
        
            $Create_Mapper = new Transaction_Mapper_WPDB_Create();
            return $Create_Mapper->create( $Entity );

        }

        throw new \InvalidArgumentException( "Invalid object (" . get_class( $Entity ) . ")." );

    }

    public function read( $Entity ) {

        if ( $Entity instanceof Transaction ) {

            $Read_Mapper = new Transaction_Mapper_WPDB_Read();
            return $Read_Mapper->read( $Entity );

        }

        throw new \InvalidArgumentException( "Invalid object (" . get_class( $Entity ) . ")." );

    }

    public function update( $Entity ) {

        if ( $Entity instanceof Transaction ) {

            if ( icwcsr_is_premium_loaded() 
            && class_exists( 
                "\Innocow\Stock_Records_Premium\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Update" 
            ) ) {
                $Update_Mapper = new \Innocow\Stock_Records_Premium\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Update();
            } else {
                $Update_Mapper = new Transaction_Mapper_WPDB_Update();
            }

            return $Update_Mapper->update( $Entity );

        }

        throw new \InvalidArgumentException( "Invalid object (" . get_class( $Entity ) . ")." );

    }

    public function delete( $Entity ) {

        if ( $Entity instanceof Transaction ) {

            if ( icwcsr_is_premium_loaded() 
            && class_exists( 
                "\Innocow\Stock_Records_Premium\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Delete" 
            ) ) {
                $Delete_Mapper = new \Innocow\Stock_Records_Premium\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Delete();
            } else {
                $Delete_Mapper = new Transaction_Mapper_WPDB_Delete();
            }

            return $Delete_Mapper->delete( $Entity );

        }

        throw new \InvalidArgumentException( "Invalid object (" . get_class( $Entity ) . ")." );

    }

    public function purge( $Entity) {

        if ( $Entity instanceof Transaction ) {

            if ( icwcsr_is_premium_loaded() 
            && class_exists( 
                "\Innocow\Stock_Records_Premium\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Delete" 
            ) ) {            
                $Delete_Mapper = new \Innocow\Stock_Records_Premium\Models\Transaction\Mappers\Transaction_Mapper_WPDB_Delete();
            } else {
                $Delete_Mapper = new Transaction_Mapper_WPDB_Delete();
            }

            return $Delete_Mapper->purge( $Entity );

        }

        throw new \InvalidArgumentException( "Invalid object (" . get_class( $Entity ) . ")." );

    }

    public function search ( $Entity ) {

        if ( $Entity instanceof Transaction_Search ) {

            $Search_Mapper = new Transaction_Mapper_WPDB_Search();
            return $Search_Mapper->search( $Entity );

        }

        throw new \InvalidArgumentException( "Invalid object (" . get_class( $Entity ) . ")." );

    }
    
}