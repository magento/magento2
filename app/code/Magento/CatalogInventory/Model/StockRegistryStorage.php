<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class StockRegistryStorage implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    protected $stocks = [];

    /**
     * @var array
     */
    private $stockItems = [];

    /**
     * @var array
     */
    private $stockStatuses = [];

    /**
     * Get Stock Data
     *
     * @param int $scopeId
     * @return StockInterface
     */
    public function getStock($scopeId)
    {
        return isset($this->stocks[$scopeId]) ? $this->stocks[$scopeId] : null;
    }

    /**
     * Set Stock cache
     *
     * @param int $scopeId
     * @param StockInterface $value
     * @return void
     */
    public function setStock($scopeId, StockInterface $value)
    {
        $this->stocks[$scopeId] = $value;
    }

    /**
     * Delete cached Stock based on scopeId
     *
     * @param int|null $scopeId
     * @return void
     */
    public function removeStock($scopeId = null)
    {
        if (null === $scopeId) {
            $this->stocks = [];
        } else {
            unset($this->stocks[$scopeId]);
        }
    }

    /**
     * Retrieve Stock Item
     *
     * @param int $productId
     * @param int $scopeId
     * @return StockItemInterface
     */
    public function getStockItem($productId, $scopeId)
    {
        return $this->stockItems[$productId][$scopeId] ?? null;
    }

    /**
     * Update Stock Item
     *
     * @param int $productId
     * @param int $scopeId
     * @param StockItemInterface $value
     * @return void
     */
    public function setStockItem($productId, $scopeId, StockItemInterface $value)
    {
        $this->stockItems[$productId][$scopeId] = $value;
    }

    /**
     * Remove stock Item based on productId & scopeId
     *
     * @param int $productId
     * @param int|null $scopeId
     * @return void
     */
    public function removeStockItem($productId, $scopeId = null)
    {
        if (null === $scopeId) {
            unset($this->stockItems[$productId]);
        } else {
            unset($this->stockItems[$productId][$scopeId]);
        }
    }

    /**
     * Retrieve stock status
     *
     * @param int $productId
     * @param int $scopeId
     * @return StockStatusInterface
     */
    public function getStockStatus($productId, $scopeId)
    {
        return $this->stockStatuses[$productId][$scopeId] ?? null;
    }

    /**
     * Update stock Status
     *
     * @param int $productId
     * @param int $scopeId
     * @param StockStatusInterface $value
     * @return void
     */
    public function setStockStatus($productId, $scopeId, StockStatusInterface $value)
    {
        $this->stockStatuses[$productId][$scopeId] = $value;
    }

    /**
     * Clear stock status
     *
     * @param int $productId
     * @param int|null $scopeId
     * @return void
     */
    public function removeStockStatus($productId, $scopeId = null)
    {
        if (null === $scopeId) {
            unset($this->stockStatuses[$productId]);
        } else {
            unset($this->stockStatuses[$productId][$scopeId]);
        }
    }

    /**
     * Clear cached entities
     *
     * @return void
     */
    public function clean()
    {
        $this->stockItems = [];
        $this->stocks = [];
        $this->stockStatuses = [];
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->clean();
    }
}
