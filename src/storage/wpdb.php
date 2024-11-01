<?php

/**
 * Class WPDB | models/storage/WPDB.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Storage;

abstract class WPDB {

    /**
     * The WPDB object.
     *
     * @var wpdb
     */
    private $WPDB = null;

    //
    //
    // Constructor
    //
    //

    public function __construct( $WPDB=null ) {

        if ( is_null( $WPDB ) ) {

            global $wpdb;
            $this->WPDB = $wpdb;

        } else {

            $this->WPDB = $WPDB;

        }

        $this->set_mysql_timezone_to_utc();

    }

    //
    //
    // Accessors
    //
    //

    /**
     * Gets the WPDB object.
     *
     * @return wpdb The Wordpress DB interface object.
     */
    public function get_wpdb() {

        return $this->WPDB;

    }

    //
    //
    // Helpers
    //
    //

    public function set_mysql_timezone_to_utc() {

        $rows_affected = $this->get_wpdb()->query( "SET time_zone='+00:00'" );

        if ( $rows_affected !== 0 ) {

            throw new \RuntimeException( "Fatal error setting timezone." );

        }

        return true;

    }

    public function get_prefixed_tablename( string $tn ) {

        return $this->get_wpdb()->prefix . $tn;

    }

}
