<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferInterface;

interface BulkPartialInventoryTransferInterface
{
    /**
     * Run bulk partial inventory transfer for specified items.
     *
     * @param PartialInventoryTransferInterface[] $items
     * @return SourceItemInterface[]
     */
    public function execute(array $items): array;
}