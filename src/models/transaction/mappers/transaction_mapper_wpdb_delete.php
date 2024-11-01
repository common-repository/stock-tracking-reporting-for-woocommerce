<?php

/**
 * Class Transaction_Mapper_WPDB_Delete | src/models/transaction/mappers/transaction_mapper_wpdb_delete.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Models\Transaction\Mappers;

use Innocow\Stock_Records\Models\Transaction\Transaction;

class Transaction_Mapper_WPDB_Delete extends Transaction_Mapper_WPDB {

    public function delete( Transaction $Transaction ) {
        return false;
    }

    public function purge( Transaction $Transaction ) {
        return false;
    }

}