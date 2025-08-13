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
 * @since 100.0.2
 */
class MethodList
{
    /**
     * @var \Magento\Payment\Helper\Data
     * @see MAGETWO-71174
     * @deprecated 100.1.0 Do not use this property in case of inheritance.
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     * @see MAGETWO-71174
     * @deprecated 100.2.0 Do not use this property in case of inheritance.
     */
    protected $methodSpecificationFactory;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var array
     */
    private $additionalChecks;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param Checks\SpecificationFactory  $specificationFactory
     * @param array                        $additionalChecks
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $specificationFactory,
        array $additionalChecks = []
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->methodSpecificationFactory = $specificationFactory;
        $this->additionalChecks = $additionalChecks;
    }

    /**
     * Returns all available payment methods for the given quote.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Payment\Model\MethodInterface[]
     */
    public function getAvailableMethods(?\Magento\Quote\Api\Data\CartInterface $quote = null)
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
     */
    protected function _canUseMethod($method, \Magento\Quote\Api\Data\CartInterface $quote)
    {
        return $this->methodSpecificationFactory->create(
            array_merge(
                [
                    AbstractMethod::CHECK_USE_CHECKOUT,
                    AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                ],
                $this->additionalChecks
            )
        )->isApplicable(
            $method,
            $quote
        );
    }

    /**
     * Get payment method list.
     *
     * @return \Magento\Payment\Api\PaymentMethodListInterface
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
