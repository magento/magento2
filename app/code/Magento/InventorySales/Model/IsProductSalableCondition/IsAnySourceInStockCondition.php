<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * @inheritdoc
 */
class IsAnySourceInStockCondition implements IsProductSalableInterface
{
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * IsAnySourceInStockCondition constructor.
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     */
    public function __construct(GetSourceItemsBySkuInterface $getSourceItemsBySku)
    {
        $this->getSourceItemsBySku = $getSourceItemsBySku;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId): bool
    {
        $sourceItems = $this->getSourceItemsBySku->execute($sku);
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getStatus() === SourceItemInterface::STATUS_IN_STOCK) {
                return true;
            }
        }

        return false;
    }
}
