<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote;

/**
 * Test helper for a \Magento\Quote\Model\Quote with controllable flags
 * like hasItems, hasError, and validateMinimumAmount.
 *
 * This allows tests in other modules (e.g., Checkout) to simulate
 * quote states without mocking internals.
 */
class QuoteMutableFlagsTestHelper extends Quote
{
    /** @var bool */
    private $hasItemsVal = false;

    /** @var bool */
    private $hasErrorVal = false;

    /** @var bool */
    private $validateMinimumVal = false;

    /**
     * Override parent constructor; not needed for tests.
     */
    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function hasItems()
    {
        return $this->hasItemsVal;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setHasItemsVal($value)
    {
        $this->hasItemsVal = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHasError()
    {
        return $this->hasErrorVal;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->hasErrorVal;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setHasErrorVal($value)
    {
        $this->hasErrorVal = (bool)$value;
        return $this;
    }

    /**
     * @param bool $multishipping
     * @return bool
     */
    public function validateMinimumAmount($multishipping = false)
    {
        // Touch parameter to satisfy PHPMD; behavior uses internal flag
        if ($multishipping === true) {
            // no-op
        }
        return $this->validateMinimumVal;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setValidateMinimumAmountVal($value)
    {
        $this->validateMinimumVal = (bool)$value;
        return $this;
    }
}
