<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Address\Rate;

/**
 * Test helper for Rate model providing basic getters used in tests.
 */
class RateTestHelper extends Rate
{
    /** @var array */
    private $data = [];

    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Get test rate price.
     *
     * @return float|int|null
     */
    public function getPrice()
    {
        return $this->data['price'] ?? null;
    }

    /**
     * Set test rate price.
     *
     * @param float|int $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->data['price'] = $price;
        return $this;
    }

    /**
     * Get test rate carrier code.
     *
     * @return string|null
     */
    public function getCarrier()
    {
        return $this->data['carrier'] ?? null;
    }

    /**
     * Set test rate carrier code.
     *
     * @param string $carrier
     * @return $this
     */
    public function setCarrier($carrier)
    {
        $this->data['carrier'] = $carrier;
        return $this;
    }

    /**
     * Get test shipping method code.
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->data['method'] ?? null;
    }

    /**
     * Set test shipping method code.
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->data['method'] = $method;
        return $this;
    }

    /**
     * Get carrier title.
     *
     * @return string|null
     */
    public function getCarrierTitle()
    {
        return $this->data['carrier_title'] ?? null;
    }

    /**
     * Set carrier title.
     *
     * @param string $title
     * @return $this
     */
    public function setCarrierTitle($title)
    {
        $this->data['carrier_title'] = $title;
        return $this;
    }

    /**
     * Get method title.
     *
     * @return string|null
     */
    public function getMethodTitle()
    {
        return $this->data['method_title'] ?? null;
    }

    /**
     * Set method title.
     *
     * @param string $title
     * @return $this
     */
    public function setMethodTitle($title)
    {
        $this->data['method_title'] = $title;
        return $this;
    }

    /**
     * Get rate code.
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->data['code'] ?? null;
    }

    /**
     * Set rate code.
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->data['code'] = $code;
        return $this;
    }
}
