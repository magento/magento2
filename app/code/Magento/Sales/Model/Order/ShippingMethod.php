<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\DataObject;

/**
 * Value object for shipping_method order attribute
 * 
 * @method string getCarrierCode()
 * @method string getMethod()
 */
class ShippingMethod extends DataObject
{
    /**
     * Shipping method as in shipping_method order attribute
     *
     * @var string
     */
    private $fullShippingMethod;

    public function __construct(string $fullShippingMethod)
    {
        $this->fullShippingMethod = $fullShippingMethod;
        list($carrierCode, $method) = explode('_', $fullShippingMethod, 2);
        parent::__construct(['carrier_code' => $carrierCode, 'method' => $method]);
    }

    public function __toString() : string
    {
        return $this->fullShippingMethod;
    }
}