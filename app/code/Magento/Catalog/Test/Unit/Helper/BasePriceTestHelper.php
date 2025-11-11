<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Pricing\Price\BasePrice;

/**
 * Test helper class for BasePrice with custom methods
 *
 * This helper extends BasePrice to add custom methods
 * that don't exist on the base class for testing purposes.
 */
class BasePriceTestHelper extends BasePrice
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
     * Custom getPriceWithoutOption method for Bundle testing
     *
     * @return mixed
     */
    public function getPriceWithoutOption()
    {
        return $this->data['price_without_option'] ?? null;
    }

    /**
     * Set price without option for testing
     *
     * @param mixed $price
     * @return self
     */
    public function setPriceWithoutOption($price): self
    {
        $this->data['price_without_option'] = $price;
        return $this;
    }

    /**
     * Override getAmount for testing
     *
     * @return mixed
     */
    public function getAmount()
    {
        return $this->data['amount'] ?? null;
    }

    /**
     * Set amount for testing
     *
     * @param mixed $amount
     * @return self
     */
    public function setAmount($amount): self
    {
        $this->data['amount'] = $amount;
        return $this;
    }
}
