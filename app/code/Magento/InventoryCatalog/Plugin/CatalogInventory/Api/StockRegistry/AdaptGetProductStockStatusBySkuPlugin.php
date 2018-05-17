<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Adapt getProductStockStatusBySku for multi stocks.
 */
class AdaptGetProductStockStatusBySkuPlugin
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param IsProductSalableInterface $isProductSalable
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        IsProductSalableInterface $isProductSalable
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->isProductSalable = $isProductSalable;
    }

    /**
     * @param StockRegistryInterface $subject
     * @param int $status
     * @param string $productSku
     * @param int $scopeId
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductStockStatusBySku(
        StockRegistryInterface $subject,
        $status,
        $productSku,
        $scopeId = null
    ): int {
        if (null === $scopeId) {
            $scopeId = $this->getStockIdForCurrentWebsite->execute();
        }

        return (int)$this->isProductSalable->execute($productSku, $scopeId);
    }
}
