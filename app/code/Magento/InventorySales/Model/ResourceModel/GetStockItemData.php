<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\InventorySalesApi\Model\GetStockItemDataInterface;

/**
 * @inheritdoc
 */
class GetStockItemData implements GetStockItemDataInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): ?array
    {
        return null;
    }
}
