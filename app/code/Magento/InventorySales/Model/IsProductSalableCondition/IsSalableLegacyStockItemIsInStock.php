<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemManagementAllowedForSku;

/**
 * @inheritdoc
 */
class IsSalableLegacyStockItemIsInStock implements IsProductSalableInterface
{
    /**
     * @var IsSourceItemManagementAllowedForSku
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param IsSourceItemManagementAllowedForSku $isSourceItemManagementAllowedForSku
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockConfigurationInterface $stockConfiguration
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        IsSourceItemManagementAllowedForSku $isSourceItemManagementAllowedForSku,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockConfiguration = $stockConfiguration;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        if ($this->isSourceItemManagementAllowedForSku->execute($sku)) {
            return true;
        }

        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $productId = current($this->getProductIdsBySkus->execute([$sku]));
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $isInStock = (int)$stockItem->getIsInStock();

        if (!$isInStock) {
            return false;
        }
        return true;
    }
}
