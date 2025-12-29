<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Helper;

use Magento\CatalogInventory\Model\Stock\Item as StockItem;

/**
 * Test helper class for StockItem with custom methods
 *
 * This helper extends the StockItem class to provide custom methods
 * needed for testing that don't exist in the parent class.
 */
class ItemTestHelper extends StockItem
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Skip parent constructor to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Custom method for UpdateStockChangedAuto tests
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsInStock()
    {
        return $this->data['is_in_stock'] ?? true;
    }

    /**
     * Custom method for UpdateStockChangedAuto tests
     *
     * @param mixed $isInStock
     * @return self
     */
    public function setIsInStock($isInStock)
    {
        $this->data['is_in_stock'] = $isInStock;
        return $this;
    }

    /**
     * Custom method for UpdateStockChangedAuto tests
     *
     * @return bool
     */
    public function hasStockStatusChangedAutomaticallyFlag()
    {
        return $this->data['has_stock_status_changed_automatically_flag'] ?? false;
    }

    /**
     * Custom method for UpdateStockChangedAuto tests
     *
     * @param mixed $hasFlag
     * @return self
     */
    public function setHasStockStatusChangedAutomaticallyFlag($hasFlag)
    {
        $this->data['has_stock_status_changed_automatically_flag'] = $hasFlag;
        return $this;
    }
}
