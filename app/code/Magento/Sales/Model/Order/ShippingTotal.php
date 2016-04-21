<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\TotalInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class ShippingTotal extends AbstractExtensibleModel implements TotalInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBaseShippingAmount()
    {
        return $this->_getData(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingCanceled()
    {
        return $this->_getData(self::BASE_SHIPPING_CANCELED);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingDiscountAmount()
    {
        return $this->_getData(self::BASE_SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingDiscountTaxCompensationAmnt()
    {
        return $this->_getData(self::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingInclTax()
    {
        return $this->_getData(self::BASE_SHIPPING_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingInvoiced()
    {
        return $this->_getData(self::BASE_SHIPPING_INVOICED);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingRefunded()
    {
        return $this->_getData(self::BASE_SHIPPING_REFUNDED);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->_getData(self::BASE_SHIPPING_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingTaxRefunded()
    {
        return $this->_getData(self::BASE_SHIPPING_TAX_REFUNDED);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAmount()
    {
        return $this->_getData(self::SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCanceled()
    {
        return $this->_getData(self::SHIPPING_CANCELED);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingDiscountAmount()
    {
        return $this->_getData(self::SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingDiscountTaxCompensationAmount()
    {
        return $this->_getData(self::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingInclTax()
    {
        return $this->_getData(self::SHIPPING_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingInvoiced()
    {
        return $this->_getData(self::SHIPPING_INVOICED);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingRefunded()
    {
        return $this->_getData(self::SHIPPING_REFUNDED);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingTaxAmount()
    {
        return $this->_getData(self::SHIPPING_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingTaxRefunded()
    {
        return $this->_getData(self::SHIPPING_TAX_REFUNDED);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingAmount($amount)
    {
        return $this->setData(self::BASE_SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingCanceled($baseShippingCanceled)
    {
        return $this->setData(self::BASE_SHIPPING_CANCELED, $baseShippingCanceled);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingDiscountAmount($amount)
    {
        return $this->setData(self::BASE_SHIPPING_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingDiscountTaxCompensationAmnt($amnt)
    {
        return $this->setData(self::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT, $amnt);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingInclTax($amount)
    {
        return $this->setData(self::BASE_SHIPPING_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingInvoiced($baseShippingInvoiced)
    {
        return $this->setData(self::BASE_SHIPPING_INVOICED, $baseShippingInvoiced);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingRefunded($baseShippingRefunded)
    {
        return $this->setData(self::BASE_SHIPPING_REFUNDED, $baseShippingRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingTaxAmount($amount)
    {
        return $this->setData(self::BASE_SHIPPING_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingTaxRefunded($baseShippingTaxRefunded)
    {
        return $this->setData(self::BASE_SHIPPING_TAX_REFUNDED, $baseShippingTaxRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAmount($amount)
    {
        return $this->setData(self::SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingCanceled($shippingCanceled)
    {
        return $this->setData(self::SHIPPING_CANCELED, $shippingCanceled);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingDiscountAmount($amount)
    {
        return $this->setData(self::SHIPPING_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(self::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingInclTax($amount)
    {
        return $this->setData(self::SHIPPING_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingInvoiced($shippingInvoiced)
    {
        return $this->setData(self::SHIPPING_INVOICED, $shippingInvoiced);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingRefunded($shippingRefunded)
    {
        return $this->setData(self::SHIPPING_REFUNDED, $shippingRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingTaxAmount($amount)
    {
        return $this->setData(self::SHIPPING_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingTaxRefunded($shippingTaxRefunded)
    {
        return $this->setData(self::SHIPPING_TAX_REFUNDED, $shippingTaxRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\TotalExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
