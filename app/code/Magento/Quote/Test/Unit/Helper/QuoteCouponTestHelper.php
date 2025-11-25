<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote;

/**
 * Test helper for Quote coupon code operations.
 *
 * Provides lightweight implementations of coupon code setters/getters
 * while bypassing the parent constructor for unit tests.
 */
class QuoteCouponTestHelper extends Quote
{
    /**
     * Construct helper without invoking the parent constructor.
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Set coupon code on the quote for testing.
     *
     * @param mixed $code
     * @return $this
     */
    public function setCouponCode($code)
    {
        $this->setData('coupon_code', $code);
        return $this;
    }

    /**
     * Get coupon code from the quote for testing.
     *
     * @return mixed
     */
    public function getCouponCode()
    {
        return $this->getData('coupon_code');
    }
}
