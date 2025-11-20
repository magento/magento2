<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Item;

class CartItemForShippingTestHelper extends Item
{
    /**
     * @var bool
     */
    private $freeShipping = false;
    /**
     * @var Item|null
     */
    private $parentItem = null;
    /**
     * @var bool
     */
    private $hasChildren = false;
    /**
     * @var bool
     */
    private $isVirtual = false;
    /**
     * @var float|int
     */
    private $weight = 0;
    /**
     * @var int|float
     */
    private $qty = 0;
    /**
     * @var mixed
     */
    private $product = null;

    public function __construct()
    {
        // Skip parent constructor
    }

    public function getFreeShipping()
    {
        return (bool)$this->freeShipping;
    }

    public function setFreeShipping($flag)
    {
        $this->freeShipping = (bool)$flag;
        return $this;
    }

    public function getParentItem()
    {
        return $this->parentItem;
    }

    public function setParentItem($item)
    {
        $this->parentItem = $item;
        return $this;
    }

    public function getHasChildren()
    {
        return (bool)$this->hasChildren;
    }

    public function setHasChildren($flag)
    {
        $this->hasChildren = (bool)$flag;
        return $this;
    }

    public function isVirtual()
    {
        return (bool)$this->isVirtual;
    }

    public function setIsVirtual($flag)
    {
        $this->isVirtual = (bool)$flag;
        return $this;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    public function getQty()
    {
        return $this->qty;
    }

    public function setQty($qty)
    {
        $this->qty = $qty;
        return $this;
    }

    public function setRowWeight($rowWeight)
    {
        // Touch parameter to avoid PHPMD warning; no-op for test behavior
        if ($rowWeight !== null) {
            // no-op
        }
        return $this;
    }

    /**
     * Override product setter to avoid event dispatching.
     *
     * @param mixed $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Return the previously set product instance.
     *
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }
}
