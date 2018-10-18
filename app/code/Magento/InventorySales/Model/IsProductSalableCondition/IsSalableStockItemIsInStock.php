<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemManagementAllowedForSku;

/**
 * @inheritdoc
 */
class IsSalableStockItemIsInStock implements IsProductSalableInterface
{
    /**
     * @var IsSourceItemManagementAllowedForSku
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param IsSourceItemManagementAllowedForSku $isSourceItemManagementAllowedForSku
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        IsSourceItemManagementAllowedForSku $isSourceItemManagementAllowedForSku,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        if ($this->isSourceItemManagementAllowedForSku->execute($sku)) {
            return true;
        }

        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $isInStock = $stockItemConfiguration->getExtensionAttributes()->getIsInStock();

        return $isInStock;
    }
}
