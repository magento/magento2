<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

use Magento\Payment\Api\Data\PaymentMethodInterface;

/**
 * Payment method instance factory.
 * @since 2.2.0
 */
class InstanceFactory
{
    /**
     * @var \Magento\Payment\Helper\Data
     * @since 2.2.0
     */
    private $helper;

    /**
     * @param \Magento\Payment\Helper\Data $helper
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function create(PaymentMethodInterface $paymentMethod)
    {
        $methodInstance = $this->helper->getMethodInstance($paymentMethod->getCode());
        $methodInstance->setStore($paymentMethod->getStoreId());

        return $methodInstance;
    }
}
