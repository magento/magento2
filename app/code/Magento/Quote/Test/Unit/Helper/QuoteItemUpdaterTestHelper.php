<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Item;

/**
 * Test helper for Quote Item to expose magic methods as explicit ones for PHPUnit 12.
 */
class QuoteItemUpdaterTestHelper extends Item
{
    /**
     * Intentionally empty constructor to skip parent dependencies.
     */
    public function __construct()
    {
    }

    /**
     * Explicit setter used by tests to configure discount flag.
     *
     * @param bool $flag
     * @return $this
     */
    public function setNoDiscount($flag)
    {
        $this->setData('no_discount', (bool)$flag);
        return $this;
    }

    /**
     * Explicit setter used by tests to configure qty decimal flag.
     *
     * @param bool|int $flag
     * @return $this
     */
    public function setIsQtyDecimal($flag)
    {
        $this->setData('is_qty_decimal', (bool)$flag);
        return $this;
    }

    /**
     * Explicit setter used by tests to set original custom price.
     *
     * @param float|null $price
     * @return $this
     */
    public function setOriginalCustomPrice($price)
    {
        $this->setData('original_custom_price', $price);
        return $this;
    }

    /**
     * Get error flag for the item in tests.
     *
     * @return bool
     */
    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    // phpcs:ignore Magento2.NamingConvention.PublicMethodName
    public function getHasError()
    {
        return (bool)($this->getData('has_error') ?? false);
    }

    /**
     * Get parent item id for tests.
     *
     * @return int|false|null
     */
    public function getParentItemId()
    {
        return $this->getData('parent_item_id') ?? false;
    }

    /**
     * Get quantity to add for tests.
     *
     * @return float|int|null
     */
    public function getQtyToAdd()
    {
        return $this->getData('qty_to_add');
    }

    /**
     * Get previous quantity for tests.
     *
     * @return float|int|null
     */
    public function getPreviousQty()
    {
        return $this->getData('previous_qty');
    }
}
