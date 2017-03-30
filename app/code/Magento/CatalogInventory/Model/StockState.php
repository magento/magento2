<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;

/**
 * Interface StockState
 */
class StockState implements StockStateInterface
{
    /**
     * @var StockStateProviderInterface
     */
    protected $stockStateProvider;

    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param StockStateProviderInterface $stockStateProvider
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        StockStateProviderInterface $stockStateProvider,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockStateProvider = $stockStateProvider;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return bool
     */
    public function verifyStock($productId, $scopeId = null)
    {
        // if ($scopeId === null) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        // }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        return $this->stockStateProvider->verifyStock($stockItem);
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return bool
     */
    public function verifyNotification($productId, $scopeId = null)
    {
        // if ($scopeId === null) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        // }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        return $this->stockStateProvider->verifyNotification($stockItem);
    }

    /**
     * Check quantity
     *
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     * @exception \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function checkQty($productId, $qty, $scopeId = null)
    {
        // if ($scopeId === null) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        // }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        return $this->stockStateProvider->checkQty($stockItem, $qty);
    }

    /**
     * Returns suggested qty that satisfies qty increments and minQty/maxQty/minSaleQty/maxSaleQty conditions
     * or original qty if such value does not exist
     *
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     * @return float
     */
    public function suggestQty($productId, $qty, $scopeId = null)
    {
        // if ($scopeId === null) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        // }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        return $this->stockStateProvider->suggestQty($stockItem, $qty);
    }

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @param int $productId
     * @param int $scopeId
     * @return float
     */
    public function getStockQty($productId, $scopeId = null)
    {
        // if ($scopeId === null) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        // }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        return $this->stockStateProvider->getStockQty($stockItem);
    }

    /**
     * @param int $productId
     * @param float $qty
     * @param int $websiteId
     * @return \Magento\Framework\DataObject
     */
    public function checkQtyIncrements($productId, $qty, $websiteId = null)
    {
        // if ($websiteId === null) {
            $websiteId = $this->stockConfiguration->getDefaultScopeId();
        // }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->checkQtyIncrements($stockItem, $qty);
    }

    /**
     * @param int $productId
     * @param float $itemQty
     * @param float $qtyToCheck
     * @param float $origQty
     * @param int $scopeId
     * @return int
     */
    public function checkQuoteItemQty($productId, $itemQty, $qtyToCheck, $origQty, $scopeId = null)
    {
        // if ($scopeId === null) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        // }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        return $this->stockStateProvider->checkQuoteItemQty($stockItem, $itemQty, $qtyToCheck, $origQty);
    }
}
