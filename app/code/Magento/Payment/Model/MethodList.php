<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Methods List service class.
 *
 * @api
 * @since 2.0.0
 */
class MethodList
{
    /**
     * @var \Magento\Payment\Helper\Data
     * @deprecated 2.2.0 Do not use this property in case of inheritance.
     * @since 2.0.0
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     * @deprecated 2.2.0 Do not use this property in case of inheritance.
     * @since 2.0.0
     */
    protected $methodSpecificationFactory;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     * @since 2.2.0
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory
     * @since 2.2.0
     */
    private $paymentMethodInstanceFactory;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param Checks\SpecificationFactory $specificationFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $specificationFactory
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->methodSpecificationFactory = $specificationFactory;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Payment\Model\MethodInterface[]
     * @since 2.0.0
     */
    public function getAvailableMethods(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $store = $quote ? $quote->getStoreId() : null;
        $availableMethods = [];

        foreach ($this->getPaymentMethodList()->getActiveList($store) as $method) {
            $methodInstance = $this->getPaymentMethodInstanceFactory()->create($method);
            if ($methodInstance->isAvailable($quote) && $this->_canUseMethod($methodInstance, $quote)) {
                $methodInstance->setInfoInstance($quote->getPayment());
                $availableMethods[] = $methodInstance;
            }
        }
        return $availableMethods;
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\MethodInterface $method
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     * @since 2.0.0
     */
    protected function _canUseMethod($method, \Magento\Quote\Api\Data\CartInterface $quote)
    {
        return $this->methodSpecificationFactory->create(
            [
                AbstractMethod::CHECK_USE_CHECKOUT,
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ]
        )->isApplicable(
            $method,
            $quote
        );
    }

    /**
     * Get payment method list.
     *
     * @return \Magento\Payment\Api\PaymentMethodListInterface
     * @since 2.2.0
     */
    private function getPaymentMethodList()
    {
        if ($this->paymentMethodList === null) {
            $this->paymentMethodList = ObjectManager::getInstance()->get(
                \Magento\Payment\Api\PaymentMethodListInterface::class
            );
        }
        return $this->paymentMethodList;
    }

    /**
     * Get payment method instance factory.
     *
     * @return \Magento\Payment\Model\Method\InstanceFactory
     * @since 2.2.0
     */
    private function getPaymentMethodInstanceFactory()
    {
        if ($this->paymentMethodInstanceFactory === null) {
            $this->paymentMethodInstanceFactory = ObjectManager::getInstance()->get(
                \Magento\Payment\Model\Method\InstanceFactory::class
            );
        }
        return $this->paymentMethodInstanceFactory;
    }
}
