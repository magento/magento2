<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem\Service;

interface GetAssignedStocksInterface
{
    /**
     * Returns alle assigned stocks by given sources ids.
     *
     * @param  array  $sourceIds
     * @return int[] List of stock ids
     */
    public function execute(array $sourceIds);

}