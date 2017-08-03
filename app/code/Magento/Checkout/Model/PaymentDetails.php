<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

/**
 * @codeCoverageIgnoreStart
 * @since 2.0.0
 */
class PaymentDetails extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Checkout\Api\Data\PaymentDetailsInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPaymentMethods()
    {
        return $this->getData(self::PAYMENT_METHODS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setPaymentMethods($paymentMethods)
    {
        return $this->setData(self::PAYMENT_METHODS, $paymentMethods);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTotals()
    {
        return $this->getData(self::TOTALS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTotals($totals)
    {
        return $this->setData(self::TOTALS, $totals);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
