<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\ShippingMethodInterface;

/**
 * Quote shipping method data.
 *
 * @codeCoverageIgnore
 */
class ShippingMethod extends \Magento\Framework\Api\AbstractExtensibleObject implements
    ShippingMethodInterface
{
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
     * Sets the shipping carrier code.
     *
     * @return $this
     */
    public function setCarrierCode($carrierCode)
    {
        return $this->setData(self::CARRIER_CODE, $carrierCode);
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
     * Sets the shipping method code.
     *
     * @return $this
     */
    public function setMethodCode($methodCode)
    {
        return $this->setData(self::METHOD_CODE, $methodCode);
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
     * Sets the shipping carrier title.
     *
     * @return $this
     */
    public function setCarrierTitle($carrierTitle)
    {
        return $this->setData(self::CARRIER_TITLE, $carrierTitle);
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
     * Sets the shipping method title.
     *
     * @return $this
     */
    public function setMethodTitle($methodTitle)
    {
        return $this->setData(self::METHOD_TITLE, $methodTitle);
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
     * Sets the shipping amount in store currency.
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->setData(self::SHIPPING_AMOUNT, $amount);
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
     * Sets the shipping amount in base currency.
     *
     * @return $this
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::BASE_SHIPPING_AMOUNT, $baseAmount);
    }

    /**
     * Returns the value of the availability flag for the current shipping method.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAvailable()
    {
        return $this->_get(self::AVAILABLE);
    }

    /**
     * Sets the value of the availability flag for the current shipping method.
     *
     * @return $this
     */
    public function setAvailable($available)
    {
        return $this->setData(self::AVAILABLE, $available);
    }
}
