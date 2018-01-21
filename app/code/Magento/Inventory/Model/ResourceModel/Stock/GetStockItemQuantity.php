<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\Stock;

use Magento\Inventory\Model\GetStockItemQuantityInterface;

/**
 * @inheritdoc
 */
class GetStockItemQuantity implements GetStockItemQuantityInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): float
    {
        throw new \Exception('https://github.com/magento-engcom/msi/issues/420');
    }
}
