<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Value object for shipping_method order attribute
 */
class ShippingMethod
{
    /**
     * @var
     */
    private $carrierCode;
    /**
     * @var
     */
    private $method;

    public function __construct(string $carrierCode, string $method)
    {
        $this->carrierCode = $carrierCode;
        $this->method = $method;
    }

    /**
     * Shipping method as in shipping_method order attribute
     *
     * @param string $fullShippingMethod
     * @return ShippingMethod
     */
    public static function fromFullShippingMethodCode(string $fullShippingMethod) : ShippingMethod
    {
        list($carrierCode, $method) = explode('_', $fullShippingMethod, 2);
        return new self($carrierCode, $method);
    }

    /**
     * Shipping method as in shipping_method order attribute
     *
     * @return string
     */
    public function __toString() : string
    {
        return "{$this->carrierCode}_{$this->method}";
    }

    /**
     * Returns carrier code
     *
     * @return string
     */
    public function getCarrierCode() : string
    {
        return $this->carrierCode;
    }

    /**
     * Returns shipping method code without carrier
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }
}
