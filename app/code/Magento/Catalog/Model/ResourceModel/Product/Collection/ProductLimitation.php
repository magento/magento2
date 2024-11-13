<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * @api
 * @since 101.0.0
 */
class ProductLimitation implements \ArrayAccess
{
    /**
     * Product limitation filters
     * Allowed filters
     *  store_id                int;
     *  category_id             int;
     *  category_is_anchor      int;
     *  visibility              array|int;
     *  website_ids             array|int;
     *  store_table             string;
     *  use_price_index         bool;   join price index table flag
     *  customer_group_id       int;    required for price; customer group limitation for price
     *  website_id              int;    required for price; website limitation for price
     *
     * @var array
     */
    private $productLimitationFilters = [];

    /**
     * Check if the value is set for the given offset.
     *
     * @param string $offset
     * @return bool
     * @since 101.0.0
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->productLimitationFilters);
    }

    /**
     * Get the value by provided offset.
     *
     * @param string $offset
     * @return mixed
     * @since 101.0.0
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->productLimitationFilters[$offset];
    }

    /**
     * Set the given offset to filters.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     * @since 101.0.0
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->productLimitationFilters[$offset] = $value;
    }

    /**
     * Unset the given offset from filters.
     *
     * @param string $offset
     * @return void
     * @since 101.0.0
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->productLimitationFilters[$offset]);
    }

    /**
     * Returns store ID.
     *
     * @return int|null
     * @since 101.0.0
     */
    public function getStoreId()
    {
        return $this->offsetGet('store_id');
    }

    /**
     * Returns category ID.
     *
     * @return int|null
     * @since 101.0.0
     */
    public function getCategoryId()
    {
        return $this->offsetGet('category_id');
    }

    /**
     * Check if the category is an anchor.
     *
     * @return int|null
     * @since 101.0.0
     */
    public function getCategoryIsAnchor()
    {
        return $this->offsetGet('category_is_anchor');
    }

    /**
     * Returns visibility value.
     *
     * @return array|int|null
     * @since 101.0.0
     */
    public function getVisibility()
    {
        return $this->offsetGet('visibility');
    }

    /**
     * Returns website IDs.
     *
     * @return array|int|null
     * @since 101.0.0
     */
    public function getWebsiteIds()
    {
        return $this->offsetGet('website_ids');
    }

    /**
     * Returns Store table.
     *
     * @return string|null
     * @since 101.0.0
     */
    public function getStoreTable()
    {
        return $this->offsetGet('store_table');
    }

    /**
     * Join price index table flag
     *
     * @return bool
     * @since 101.0.0
     */
    public function isUsingPriceIndex()
    {
        return $this->offsetExists('use_price_index') ? (bool)$this->offsetGet('use_price_index') : false;
    }

    /**
     * Set 'use price index' offset.
     *
     * @param bool $value
     * @return void
     * @since 101.0.0
     */
    public function setUsePriceIndex($value)
    {
        $this->offsetSet('use_price_index', (bool) $value);
    }

    /**
     * Required for price; customer group limitation for price
     *
     * @return int|null
     * @since 101.0.0
     */
    public function getCustomerGroupId()
    {
        return $this->offsetGet('customer_group_id');
    }

    /**
     * Required for price; website limitation for price
     *
     * @return int|null
     * @since 101.0.0
     */
    public function getWebsiteId()
    {
        return $this->offsetGet('website_id');
    }
}
