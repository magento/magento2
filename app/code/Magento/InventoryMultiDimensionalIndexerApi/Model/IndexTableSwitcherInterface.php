<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMultiDimensionalIndexerApi\Model;

/**
 * Logic for switching active and replica index tables by IndexName object
 *
 * @api
 */
interface IndexTableSwitcherInterface
{
    /**
     * @param IndexName $indexName
     * @param string $connectionName
     * @return void
     */
    public function switch(IndexName $indexName, string $connectionName): void;
}
