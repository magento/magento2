<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Helper;

use Magento\Sales\Model\Order\Item;

/**
 * Test helper class for Order Item with custom methods
 *
 */
class ItemTestHelper extends Item
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
     * Custom method for testing - returns self to simulate order item behavior
     *
     * @return self
     */
    public function getOrderItem(): self
    {
        return $this->data['order_item'] ?? $this;
    }

    /**
     * Set order item for testing
     *
     * @param mixed $item
     * @return self
     */
    public function setOrderItem($item): self
    {
        $this->data['order_item'] = $item;
        return $this;
    }

    /**
     * Override getParentItem for testing
     *
     * @return Item|null
     */
    public function getParentItem(): ?Item
    {
        $parentItem = $this->data['parent_item'] ?? null;

        // Handle boolean values from test data providers
        if ($parentItem === true) {
            // Return a mock Item when true is passed
            return $this;
        }
        if ($parentItem === false) {
            // Return null when false is passed
            return null;
        }

        return $parentItem;
    }

    /**
     * Set parent item for testing
     *
     * @param mixed $item
     * @return self
     */
    public function setParentItem($item): self
    {
        $this->data['parent_item'] = $item;
        return $this;
    }

    /**
     * Set has children for testing
     *
     * @param bool $hasChildren
     * @return self
     */
    public function setHasChildren(bool $hasChildren): self
    {
        $this->data['has_children'] = $hasChildren;
        return $this;
    }

    /**
     * Custom getProductType method for testing
     *
     * @return string|null
     */
    public function getProductType(): ?string
    {
        return $this->data['product_type'] ?? null;
    }

    /**
     * Set product type for testing
     *
     * @param mixed $productType
     * @return self
     */
    public function setProductType($productType): self
    {
        $this->data['product_type'] = $productType;
        return $this;
    }

    /**
     * Custom getProduct method for testing
     *
     * @return mixed
     */
    public function getProduct()
    {
        return $this->data['product'] ?? null;
    }

    /**
     * Set product for testing
     *
     * @param mixed $product
     * @return self
     */
    public function setProduct($product): self
    {
        $this->data['product'] = $product;
        return $this;
    }

    /**
     * Custom getSku method for testing
     *
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->data['sku'] ?? null;
    }

    /**
     * Set SKU for testing
     *
     * @param mixed $sku
     * @return self
     */
    public function setSku($sku): self
    {
        $this->data['sku'] = $sku;
        return $this;
    }
}
