<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesFrontendUi\Plugin\Block\Stockqty;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

class AbstractStockqtyPlugin
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteId;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteId
     * @param GetStockItemConfigurationInterface $getStockItemConfig
     * @param GetProductSalableQtyInterface $getProductSalableQty
     */
    public function __construct(
        StockByWebsiteIdResolverInterface $stockByWebsiteId,
        GetStockItemConfigurationInterface $getStockItemConfig,
        GetProductSalableQtyInterface $getProductSalableQty,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->getStockItemConfiguration = $getStockItemConfig;
        $this->stockByWebsiteId = $stockByWebsiteId;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    /**
     * @param \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject
     * @param callable $proceed
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsMsgVisible(
        \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject,
        callable $proceed
    ): bool {
        $productType = $subject->getProduct()->getTypeId();
        if (!$this->isSourceItemManagementAllowedForProductType->execute($productType)) {
            return false;
        }
        $sku = $subject->getProduct()->getSku();
        $websiteId = (int)$subject->getProduct()->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteId->execute($websiteId)->getStockId();
        $stockItemConfig = $this->getStockItemConfiguration->execute($sku, $stockId);
        if (null === $stockItemConfig) {
            return false;
        }
        return $stockItemConfig->getBackorders() === StockItemConfigurationInterface::BACKORDERS_NO
            && $this->getProductSalableQty->execute($sku, $stockId) <= $stockItemConfig->getStockThresholdQty();
    }

    /**
     * @param \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject
     * @param callable $proceed
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetStockQtyLeft(
        \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject,
        callable $proceed
    ): float {
        $sku = $subject->getProduct()->getSku();
        $websiteId = (int)$subject->getProduct()->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteId->execute($websiteId)->getStockId();
        return $this->getProductSalableQty->execute($sku, $stockId);
    }
}
