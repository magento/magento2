<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\InputException;

/**
 * Adapt getStockStatus for multi stocks
 */
class AdaptGetStockStatusPlugin
{
    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @param IsProductSalableInterface $isProductSalable
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        IsProductSalableInterface $isProductSalable,
        GetProductSalableQtyInterface $getProductSalableQty,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver
    ) {
        $this->isProductSalable = $isProductSalable;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @param StockRegistryInterface $subject
     * @param StockStatusInterface $stockStatus
     * @param int $productId
     * @param int $scopeId
     * @return StockStatusInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetStockStatus(
        StockRegistryInterface $subject,
        StockStatusInterface $stockStatus,
        $productId,
        $scopeId = null
    ): StockStatusInterface {
        $websiteCode = null === $scopeId
            ? $this->storeManager->getWebsite()->getCode()
            : $this->storeManager->getWebsite($scopeId)->getCode();
        $stockId = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        $sku = $this->getSkusByProductIds->execute([$productId])[$productId];

        $status = (int)$this->isProductSalable->execute($sku, $stockId);
        try {
            $qty = $this->getProductSalableQty->execute($sku, $stockId);
        } catch (InputException $e) {
            $qty = 0;
        }

        $stockStatus->setStockStatus($status);
        $stockStatus->setQty($qty);
        return $stockStatus;
    }
}
