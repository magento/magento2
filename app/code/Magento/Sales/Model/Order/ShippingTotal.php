<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\TotalInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class \Magento\Sales\Model\Order\ShippingTotal
 *
 * @since 2.0.3
 */
class ShippingTotal extends AbstractExtensibleModel implements TotalInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingAmount()
    {
        return $this->_getData(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingCanceled()
    {
        return $this->_getData(self::BASE_SHIPPING_CANCELED);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingDiscountAmount()
    {
        return $this->_getData(self::BASE_SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingDiscountTaxCompensationAmnt()
    {
        return $this->_getData(self::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingInclTax()
    {
        return $this->_getData(self::BASE_SHIPPING_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingInvoiced()
    {
        return $this->_getData(self::BASE_SHIPPING_INVOICED);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingRefunded()
    {
        return $this->_getData(self::BASE_SHIPPING_REFUNDED);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->_getData(self::BASE_SHIPPING_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getBaseShippingTaxRefunded()
    {
        return $this->_getData(self::BASE_SHIPPING_TAX_REFUNDED);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingAmount()
    {
        return $this->_getData(self::SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingCanceled()
    {
        return $this->_getData(self::SHIPPING_CANCELED);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingDiscountAmount()
    {
        return $this->_getData(self::SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingDiscountTaxCompensationAmount()
    {
        return $this->_getData(self::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingInclTax()
    {
        return $this->_getData(self::SHIPPING_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingInvoiced()
    {
        return $this->_getData(self::SHIPPING_INVOICED);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingRefunded()
    {
        return $this->_getData(self::SHIPPING_REFUNDED);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingTaxAmount()
    {
        return $this->_getData(self::SHIPPING_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getShippingTaxRefunded()
    {
        return $this->_getData(self::SHIPPING_TAX_REFUNDED);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingAmount($amount)
    {
        return $this->setData(self::BASE_SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingCanceled($baseShippingCanceled)
    {
        return $this->setData(self::BASE_SHIPPING_CANCELED, $baseShippingCanceled);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingDiscountAmount($amount)
    {
        return $this->setData(self::BASE_SHIPPING_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingDiscountTaxCompensationAmnt($amnt)
    {
        return $this->setData(self::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT, $amnt);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingInclTax($amount)
    {
        return $this->setData(self::BASE_SHIPPING_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingInvoiced($baseShippingInvoiced)
    {
        return $this->setData(self::BASE_SHIPPING_INVOICED, $baseShippingInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingRefunded($baseShippingRefunded)
    {
        return $this->setData(self::BASE_SHIPPING_REFUNDED, $baseShippingRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingTaxAmount($amount)
    {
        return $this->setData(self::BASE_SHIPPING_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setBaseShippingTaxRefunded($baseShippingTaxRefunded)
    {
        return $this->setData(self::BASE_SHIPPING_TAX_REFUNDED, $baseShippingTaxRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingAmount($amount)
    {
        return $this->setData(self::SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingCanceled($shippingCanceled)
    {
        return $this->setData(self::SHIPPING_CANCELED, $shippingCanceled);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingDiscountAmount($amount)
    {
        return $this->setData(self::SHIPPING_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(self::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingInclTax($amount)
    {
        return $this->setData(self::SHIPPING_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingInvoiced($shippingInvoiced)
    {
        return $this->setData(self::SHIPPING_INVOICED, $shippingInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingRefunded($shippingRefunded)
    {
        return $this->setData(self::SHIPPING_REFUNDED, $shippingRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingTaxAmount($amount)
    {
        return $this->setData(self::SHIPPING_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setShippingTaxRefunded($shippingTaxRefunded)
    {
        return $this->setData(self::SHIPPING_TAX_REFUNDED, $shippingTaxRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.3
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\TotalExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
