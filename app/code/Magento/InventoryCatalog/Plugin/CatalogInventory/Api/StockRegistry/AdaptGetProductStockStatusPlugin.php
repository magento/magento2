<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Adapt getProductStockStatus for multi stocks.
 */
class AdaptGetProductStockStatusPlugin
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
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param IsProductSalableInterface $isProductSalable
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        IsProductSalableInterface $isProductSalable,
        GetSkusByProductIdsInterface $getSkusByProductIds
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->isProductSalable = $isProductSalable;
        $this->getSkusByProductIds = $getSkusByProductIds;
    }

    /**
     * @param StockRegistryInterface $subject
     * @param int $status
     * @param int $productId
     * @param int $scopeId
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductStockStatus(
        StockRegistryInterface $subject,
        $status,
        $productId,
        $scopeId = null
    ): int {
        if (null === $scopeId) {
            $scopeId = $this->getStockIdForCurrentWebsite->execute();
        }

        $sku = $this->getSkusByProductIds->execute([$productId])[$productId];
        return (int)$this->isProductSalable->execute($sku, (int)$scopeId);
    }
}
