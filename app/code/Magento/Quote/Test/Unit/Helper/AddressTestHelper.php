<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Address;

/**
 * Test helper for Address
 *
 * This helper extends the concrete Address class to provide custom test-specific functionality.
 * Most methods (setTaxAmount, setShippingAmount, setCountryId, etc.) are inherited from the parent
 * Address class via magic methods or concrete implementations.
 *
 * This helper ONLY implements custom methods that don't exist in the parent class:
 * - setDefaultShippingRate() - Sets a default shipping rate for testing
 * - getShippingRateByCode() - Overrides parent to support default rate fallback
 * - setShippingRateByCode() - Sets shipping rate for a specific code
 *
 * All other methods are inherited from Magento\Quote\Model\Quote\Address.
 */
class AddressTestHelper extends Address
{
    /**
     * @var array<string, mixed>
     */
    private $shippingRates = [];

    /**
     * @var mixed
     */
    private $defaultShippingRate = null;

    /**
     * Constructor that skips parent initialization
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Get shipping rate by code with default fallback
     *
     * Overrides parent method to support defaultShippingRate fallback for testing.
     * Parent implementation searches through collection; this returns default if code not found.
     *
     * @param string $code
     * @return mixed
     */
    public function getShippingRateByCode($code)
    {
        // If a specific code is set, return it; otherwise return the default rate
        if (isset($this->shippingRates[$code])) {
            return $this->shippingRates[$code];
        }
        // Return the default rate for any code
        return $this->defaultShippingRate;
    }

    /**
     * Set shipping rate for specific code
     *
     * @param string $code
     * @param mixed $rate
     * @return $this
     */
    public function setShippingRateByCode($code, $rate)
    {
        $this->shippingRates[$code] = $rate;
        return $this;
    }

    /**
     * Set default shipping rate
     *
     * This is a custom method for testing that doesn't exist in the parent class.
     * Used to set a fallback rate when getShippingRateByCode() doesn't find a specific code.
     *
     * @param mixed $rate
     * @return $this
     */
    public function setDefaultShippingRate($rate)
    {
        $this->defaultShippingRate = $rate;
        return $this;
    }

    /**
     * Collect shipping rates
     *
     * Overrides parent method to avoid dependency injection issues in tests.
     * Parent implementation requires _rateCollectionFactory which is not initialized
     * when constructor is bypassed.
     *
     * @return $this
     */
    public function collectShippingRates()
    {
        // Do nothing in tests - just return $this
        return $this;
    }
}
