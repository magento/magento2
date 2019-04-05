<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api;

interface BulkPartialInventoryTransferInterface
{
    /**
     * Run bulk partial inventory transfer for specified items.
     *
     * @param \Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferInterface[] $items
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface[]
     */
    public function execute(array $items): array;
}