<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Test helper for RateRequest used to provide chainable setters and
 * accessors required by legacy tests while skipping parent constructor.
 */
class RateRequestTestHelper extends RateRequest
{
    /**
     * Constructor intentionally left empty to skip parent dependencies.
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Set store id for shipping rate request in tests.
     *
     * @param int|string|null $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        return $this;
    }

    /**
     * Set website id for shipping rate request in tests.
     *
     * @param int|string|null $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->setData('website_id', $websiteId);
        return $this;
    }

    /**
     * Set base currency for shipping rate request in tests.
     *
     * @param mixed $currency
     * @return $this
     */
    public function setBaseCurrency($currency)
    {
        $this->setData('base_currency', $currency);
        return $this;
    }

    /**
     * Set package currency for shipping rate request in tests.
     *
     * @param mixed $currency
     * @return $this
     */
    public function setPackageCurrency($currency)
    {
        $this->setData('package_currency', $currency);
        return $this;
    }

    /**
     * Get base subtotal including tax for shipping rate request in tests.
     *
     * @return float|int|null
     */
    public function getBaseSubtotalTotalInclTax()
    {
        return null;
    }

    /**
     * Get base subtotal for shipping rate request in tests.
     *
     * @return float|int|null
     */
    public function getBaseSubtotal()
    {
        return null;
    }
}
