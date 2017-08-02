<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\ResourceModel\Grid;

/**
 * Sales transaction payment method types option array
 * @since 2.0.0
 */
class TypeList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    protected $paymentData;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @since 2.0.0
     */
    public function __construct(\Magento\Payment\Helper\Data $paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * Return option array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->paymentData->getPaymentMethodList();
    }
}
