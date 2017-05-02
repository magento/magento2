<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class ProductLimitation
 *
 * @api
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
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->productLimitationFilters);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->productLimitationFilters[$offset];
    }

    /**
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->productLimitationFilters[$offset] = $value;
    }

    /**
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->productLimitationFilters[$offset]);
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->offsetGet('store_id');
    }

    /**
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->offsetGet('category_id');
    }

    /**
     * @return int|null
     */
    public function getCategoryIsAnchor()
    {
        return $this->offsetGet('category_is_anchor');
    }

    /**
     * @return array|int|null
     */
    public function getVisibility()
    {
        return $this->offsetGet('visibility');
    }

    /**
     * @return array|int|null
     */
    public function getWebsiteIds()
    {
        return $this->offsetGet('website_ids');
    }

    /**
     * @return string|null
     */
    public function getStoreTable()
    {
        return $this->offsetGet('store_table');
    }

    /**
     * Join price index table flag
     *
     * @return bool
     */
    public function isUsingPriceIndex()
    {
        return $this->offsetExists('use_price_index') ? (bool)$this->offsetGet('use_price_index') : false;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setUsePriceIndex($value)
    {
        $this->offsetSet('use_price_index', (bool)$value);
    }

    /**
     * Required for price; customer group limitation for price
     *
     * @return int|null
     */
    public function getCustomerGroupId()
    {
        return $this->offsetGet('customer_group_id');
    }

    /**
     * Required for price; website limitation for price
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->offsetGet('website_id');
    }
}
