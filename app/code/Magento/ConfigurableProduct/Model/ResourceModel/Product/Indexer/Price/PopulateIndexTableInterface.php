<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\DB\Select;

/**
 * Populate index table with data from select statement
 */
interface PopulateIndexTableInterface
{
    /**
     * Insert data from select statement into index table
     *
     * @param Select $select
     * @param string $indexTableName
     */
    public function execute(Select $select, string $indexTableName): void;
}
