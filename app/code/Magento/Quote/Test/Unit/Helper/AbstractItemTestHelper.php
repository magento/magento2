<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Item;

/**
 * Test helper for AbstractItem mocking
 */
class AbstractItemTestHelper extends Item
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @param $product
     * @param $children
     */
    public function __construct($product = null, $children = null)
    {
        // Skip parent constructor to avoid dependencies
        if ($product) {
            $this->setProduct($product);
        }
        if ($children) {
            $this->setChildren($children);
        }
    }

    /**
     * @return \Magento\Catalog\Model\Product|mixed|null
     */
    public function getProduct()
    {
        return $this->data['product'] ?? null;
    }

    /**
     * @param $product
     * @return $this
     */
    public function setProduct($product): self
    {
        $this->data['product'] = $product;
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote|mixed|null
     */
    public function getQuote()
    {
        return $this->data['quote'] ?? null;
    }

    /**
     * @param $parentItem
     * @return $this
     */
    public function setParentItem($parentItem): self
    {
        $this->data['parent_item'] = $parentItem;
        return $this;
    }

    /**
     * @return array|AbstractItem[]
     */
    public function getChildren(): array
    {
        return $this->data['children'] ?? [];
    }

    /**
     * @param array $children
     * @return $this
     */
    public function setChildren(array $children): self
    {
        $this->data['children'] = $children;
        return $this;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHasChildren(): bool
    {
        return !empty($this->data['children']);
    }
}
