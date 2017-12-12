<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Delete Legacy cataloginventory_stock_status database data
 *
 * @api
 */
interface DeleteCatalogInventoryStockStatusByDefaultSourceItemInterface
{
    /**
     * Delete cataloginventory_stock_status by executing plain SQL query
     *
     * @param SourceItemInterface $sourceItem
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(SourceItemInterface $sourceItem);
}
