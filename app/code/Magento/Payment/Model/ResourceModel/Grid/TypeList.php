<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\ResourceModel\Grid;

/**
 * Sales transaction payment method types option array
 */
class TypeList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentData;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     */
    public function __construct(\Magento\Payment\Helper\Data $paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->paymentData->getPaymentMethodList();
    }
}
