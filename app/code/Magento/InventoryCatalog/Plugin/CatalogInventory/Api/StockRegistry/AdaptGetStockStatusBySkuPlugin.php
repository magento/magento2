<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\InputException;

/**
 * Adapt getStockStatusBySku for multi stocks.
 */
class AdaptGetStockStatusBySkuPlugin
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
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        IsProductSalableInterface $isProductSalable,
        GetProductSalableQtyInterface $getProductSalableQty,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver
    ) {
        $this->isProductSalable = $isProductSalable;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
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
        $websiteCode = null === $scopeId
            ? $this->storeManager->getWebsite()->getCode()
            : $this->storeManager->getWebsite($scopeId)->getCode();
        $stockId = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();

        $status = (int)$this->isProductSalable->execute($productSku, $stockId);
        try {
            $qty = $this->getProductSalableQty->execute($productSku, $stockId);
        } catch (InputException $e) {
            $qty = 0;
        }

        $stockStatus->setStockStatus($status);
        $stockStatus->setQty($qty);
        return $stockStatus;
    }
}
