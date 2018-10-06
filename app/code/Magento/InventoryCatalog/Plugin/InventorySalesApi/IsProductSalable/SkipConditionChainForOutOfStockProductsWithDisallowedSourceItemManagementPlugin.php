<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventorySalesApi\IsProductSalable;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemManagementAllowedForSku;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Skip condition chain for products with negative legacy stock item "is in stock" status and disallowed
 * source item management
 */
class SkipConditionChainForOutOfStockProductsWithDisallowedSourceItemManagementPlugin
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
     * @param IsProductSalableInterface $subject
     * @param callable $proceed
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function aroundExecute(IsProductSalableInterface $subject, callable $proceed, string $sku, int $stockId)
    {
        if ($this->isSourceItemManagementAllowedForSku->execute($sku)) {
            return $proceed($sku, $stockId);
        }

        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $productId = current($this->getProductIdsBySkus->execute([$sku]));
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $isInStock = (int)$stockItem->getIsInStock();

        if (!$isInStock) {
            return false;
        }
        return $proceed($sku, $stockId);
    }
}
