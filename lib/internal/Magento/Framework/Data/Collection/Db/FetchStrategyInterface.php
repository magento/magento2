<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface of collection data retrieval
 */
namespace Magento\Framework\Data\Collection\Db;

use Magento\Framework\DB\Select;

interface FetchStrategyInterface
{
    /**
     * Retrieve all records
     *
     * @param Select $select
     * @param array $bindParams
     * @return array
     */
    public function fetchAll(Select $select, array $bindParams = []);
}
