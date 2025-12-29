<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Shipping\Model\Shipping;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Minimal rate collector for Address tests providing collectRates() and getResult().
 */
class RateCollectorForAddressTestHelper extends Shipping
{
    /** @var mixed */
    private $result;

    public function __construct()
    {
        // Skip parent constructor and dependencies
    }

    /**
     * No-op collect that returns self for chaining.
     *
     * @return $this
     */
    public function collectRates(RateRequest $request)
    {
        // Touch $request to satisfy PHPMD without altering behavior
        if ($request !== null) {
            // no-op
        }
        return $this;
    }

    /**
     * Set result object to be returned by getResult().
     *
     * @param mixed $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Return previously set result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}
