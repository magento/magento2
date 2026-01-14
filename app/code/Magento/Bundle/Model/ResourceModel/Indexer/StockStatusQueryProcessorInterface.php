<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Framework\DB\Select;

interface StockStatusQueryProcessorInterface
{
    /**
     * Process stock status select query for bundle products
     *
     * @param Select $select
     * @return Select
     */
    public function execute(Select $select): Select;
}
