<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Quote shipping method data.
 *
 * @codeCoverageIgnore
 */
class ShippingMethod extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Shipping carrier code.
     */
    const CARRIER_CODE = 'carrier_code';

    /**
     * Shipping method code.
     */
    const METHOD_CODE = 'method_code';

    /**
     * Shipping carrier title.
     */
    const CARRIER_TITLE = 'carrier_title';

    /**
     * Shipping method title.
     */
    const METHOD_TITLE = 'method_title';

    /**
     * Shipping amount in store currency.
     */
    const SHIPPING_AMOUNT = 'amount';

    /**
     * Shipping amount in base currency.
     */
    const BASE_SHIPPING_AMOUNT = 'base_amount';

    /**
     * Available.
     */
    const AVAILABLE = 'available';

    /**
     * Returns the shipping carrier code.
     *
     * @return string Shipping carrier code.
     */
    public function getCarrierCode()
    {
        return $this->_get(self::CARRIER_CODE);
    }

    /**
     * Returns the shipping method code.
     *
     * @return string Shipping method code.
     */
    public function getMethodCode()
    {
        return $this->_get(self::METHOD_CODE);
    }

    /**
     * Returns the shipping carrier title.
     *
     * @return string|null Shipping carrier title. Otherwise, null.
     */
    public function getCarrierTitle()
    {
        return $this->_get(self::CARRIER_TITLE);
    }

    /**
     * Returns the shipping method title.
     *
     * @return string|null Shipping method title. Otherwise, null.
     */
    public function getMethodTitle()
    {
        return $this->_get(self::METHOD_TITLE);
    }

    /**
     * Returns the shipping amount in store currency.
     *
     * @return float Shipping amount in store currency.
     */
    public function getAmount()
    {
        return $this->_get(self::SHIPPING_AMOUNT);
    }

    /**
     * Returns the shipping amount in base currency.
     *
     * @return float Shipping amount in base currency.
     */
    public function getBaseAmount()
    {
        return $this->_get(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Returns the value of the availability flag for the current shipping method.
     *
     * @return bool
     */
    public function getAvailable()
    {
        return $this->_get(self::AVAILABLE);
    }
}
