<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

use Magento\Payment\Api\Data\PaymentMethodInterface;

/**
 * Payment method instance factory.
 */
class InstanceFactory
{
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Payment\Helper\Data $helper
     */
    public function __construct(
        \Magento\Payment\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Create payment method instance.
     *
     * @param PaymentMethodInterface $paymentMethod
     * @return \Magento\Payment\Model\MethodInterface
     */
    public function create(PaymentMethodInterface $paymentMethod)
    {
        $methodInstance = $this->helper->getMethodInstance($paymentMethod->getCode());
        $methodInstance->setStore($paymentMethod->getStoreId());

        return $methodInstance;
    }
}
