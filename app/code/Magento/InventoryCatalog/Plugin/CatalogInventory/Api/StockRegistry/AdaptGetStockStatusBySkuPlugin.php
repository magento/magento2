<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Adapt getStockStatusBySku for multi stocks.
 */
class AdaptGetStockStatusBySkuPlugin
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
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param IsProductSalableInterface $isProductSalable
     * @param GetProductSalableQtyInterface $getProductSalableQty
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        IsProductSalableInterface $isProductSalable,
        GetProductSalableQtyInterface $getProductSalableQty
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->isProductSalable = $isProductSalable;
        $this->getProductSalableQty = $getProductSalableQty;
    }

    /**
     * @param StockRegistryInterface $subject
     * @param StockStatusInterface $stockStatus
     * @param string $productSku
     * @param int $scopeId
     * @return StockStatusInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetStockStatusBySku(
        StockRegistryInterface $subject,
        StockStatusInterface $stockStatus,
        $productSku,
        $scopeId = null
    ): StockStatusInterface {
        if (null === $scopeId) {
            $scopeId = $this->getStockIdForCurrentWebsite->execute();
        }

        $status = (int)$this->isProductSalable->execute($productSku, $scopeId);
        $qty = $this->getProductSalableQty->execute($productSku, (int)$scopeId);

        $stockStatus->setStockStatus($status);
        $stockStatus->setQty($qty);
        return $stockStatus;
    }
}
