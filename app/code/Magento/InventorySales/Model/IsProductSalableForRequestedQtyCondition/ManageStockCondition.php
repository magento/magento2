<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

/**
 * @inheritdoc
 */
class ManageStockCondition implements IsProductSalableForRequestedQtyInterface
{
    /** @var \Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition */
    private $manageStockCondition;

    /**
     * ManageStockCondition constructor.
     * @param \Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition $manageStockCondition
     */
    public function __construct(
        \Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition $manageStockCondition
    ) {
        $this->manageStockCondition = $manageStockCondition;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): bool
    {
        return $this->manageStockCondition->execute($sku, $stockId);
    }
}
