<?php

/**
 * Class Transaction_Mapper_WPDB_Update | src/models/transaction/mappers/transaction_mapper_wpdb_update.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Stock_Records\Models\Transaction\Mappers;

use Innocow\Stock_Records\Models\Transaction\Transaction;

class Transaction_Mapper_WPDB_Update extends Transaction_Mapper_WPDB {

    public function update( Transaction $Transaction ) {
        return false;
    }

}