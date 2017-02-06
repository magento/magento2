<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Config\Source;

class Allmethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     */
    public function __construct(\Magento\Payment\Helper\Data $paymentData)
    {
        $this->_paymentData = $paymentData;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_paymentData->getPaymentMethodList(true, true, true);
    }
}
