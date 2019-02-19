<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        return $this->_get(self::KEY_CARRIER_CODE);
    }

    /**
     * Sets the shipping carrier code.
     *
     * @param string $carrierCode
     * @return $this
     */
    public function setCarrierCode($carrierCode)
    {
        return $this->setData(self::KEY_CARRIER_CODE, $carrierCode);
    }

    /**
     * Returns the shipping method code.
     *
     * @return string Shipping method code.
     */
    public function getMethodCode()
    {
        return $this->_get(self::KEY_METHOD_CODE);
    }

    /**
     * Sets the shipping method code.
     *
     * @param string $methodCode
     * @return $this
     */
    public function setMethodCode($methodCode)
    {
        return $this->setData(self::KEY_METHOD_CODE, $methodCode);
    }

    /**
     * Returns the shipping carrier title.
     *
     * @return string|null Shipping carrier title. Otherwise, null.
     */
    public function getCarrierTitle()
    {
        return $this->_get(self::KEY_CARRIER_TITLE);
    }

    /**
     * Sets the shipping carrier title.
     *
     * @param string $carrierTitle
     * @return $this
     */
    public function setCarrierTitle($carrierTitle)
    {
        return $this->setData(self::KEY_CARRIER_TITLE, $carrierTitle);
    }

    /**
     * Returns the shipping method title.
     *
     * @return string|null Shipping method title. Otherwise, null.
     */
    public function getMethodTitle()
    {
        return $this->_get(self::KEY_METHOD_TITLE);
    }

    /**
     * Sets the shipping method title.
     *
     * @param string $methodTitle
     * @return $this
     */
    public function setMethodTitle($methodTitle)
    {
        return $this->setData(self::KEY_METHOD_TITLE, $methodTitle);
    }

    /**
     * Returns the shipping amount in store currency.
     *
     * @return float Shipping amount in store currency.
     */
    public function getAmount()
    {
        return $this->_get(self::KEY_SHIPPING_AMOUNT);
    }

    /**
     * Sets the shipping amount in store currency.
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->setData(self::KEY_SHIPPING_AMOUNT, $amount);
    }

    /**
     * Returns the shipping amount in base currency.
     *
     * @return float Shipping amount in base currency.
     */
    public function getBaseAmount()
    {
        return $this->_get(self::KEY_BASE_SHIPPING_AMOUNT);
    }

    /**
     * Sets the shipping amount in base currency.
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::KEY_BASE_SHIPPING_AMOUNT, $baseAmount);
    }

    /**
     * Returns the value of the availability flag for the current shipping method.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAvailable()
    {
        return $this->_get(self::KEY_AVAILABLE);
    }

    /**
     * Sets the value of the availability flag for the current shipping method.
     *
     * @param bool $available
     * @return $this
     */
    public function setAvailable($available)
    {
        return $this->setData(self::KEY_AVAILABLE, $available);
    }

    /**
     * Returns the error message.
     *
     * @return string|null Shipping Error message.
     */
    public function getErrorMessage()
    {
        return $this->_get(self::KEY_ERROR_MESSAGE);
    }

    /**
     * Set an error message.
     *
     * @param string|null $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage)
    {
        return $this->setData(self::KEY_ERROR_MESSAGE, $errorMessage);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Quote\Api\Data\ShippingMethodExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\ShippingMethodExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * {@inheritdoc}
     *
     * @return float
     */
    public function getPriceExclTax()
    {
        return $this->_get(self::KEY_PRICE_EXCL_TAX);
    }

    /**
     * {@inheritdoc}
     *
     * @param float $priceExclTax
     * @return $this
     */
    public function setPriceExclTax($priceExclTax)
    {
        return $this->setData(self::KEY_PRICE_EXCL_TAX, $priceExclTax);
    }

    /**
     * {@inheritdoc}
     *
     * @return float
     */
    public function getPriceInclTax()
    {
        return $this->_get(self::KEY_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     *
     * @param float $priceInclTax
     * @return $this
     */
    public function setPriceInclTax($priceInclTax)
    {
        return $this->setData(self::KEY_PRICE_INCL_TAX, $priceInclTax);
    }
}
