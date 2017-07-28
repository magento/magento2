<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Config\Source;

/**
 * Class \Magento\Payment\Model\Config\Source\Allmethods
 *
 * @since 2.0.0
 */
class Allmethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    protected $_paymentData;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @since 2.0.0
     */
    public function __construct(\Magento\Payment\Helper\Data $paymentData)
    {
        $this->_paymentData = $paymentData;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_paymentData->getPaymentMethodList(true, true, true);
    }
}
