<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Minimal concrete implementation of AbstractItem for unit tests.
 */
class AbstractItemForTestHelper extends AbstractItem
{
    /** @var array */
    private $children = [];

    /** @var bool */
    private $isChildrenCalculated = false;

    /** @var float|int */
    private $discountAmount = 0;

    /** @var string|null */
    private $lastOptionCode = null;

    /**
     * Create a test item without calling parent constructor.
     */
    public function __construct()
    {
        // Intentionally do not call parent constructor
    }

    /**
     * Set children items to be returned by getChildren().
     *
     * @param array $children
     * @return $this
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Control whether children discounts are calculated on parent.
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsChildrenCalculated(bool $flag)
    {
        $this->isChildrenCalculated = $flag;
        return $this;
    }

    /**
     * Set this item's discount amount.
     *
     * @param float|int $amount
     * @return $this
     */
    public function setDiscountAmount($amount)
    {
        $this->discountAmount = $amount;
        return $this;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function isChildrenCalculated()
    {
        return $this->isChildrenCalculated;
    }

    /**
     * @return float|int
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @return null
     */
    public function getQuote()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getAddress()
    {
        return null;
    }

    /**
     * @param string $code
     * @return null
     */
    public function getOptionByCode($code)
    {
        // Record the last requested option code for PHPMD and potential assertions
        $this->lastOptionCode = (string)$code;
        return null;
    }
}
