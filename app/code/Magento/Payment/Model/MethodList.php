<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class MethodList
{
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     */
    protected $methodSpecificationFactory;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param Checks\SpecificationFactory $specificationFactory
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $specificationFactory
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->methodSpecificationFactory = $specificationFactory;
    }

    /**
     * @param \Magento\Sales\Model\Quote $quote
     * @return \Magento\Payment\Model\MethodInterface[]
     */
    public function getAvailableMethods(\Magento\Sales\Model\Quote $quote = null)
    {
        $store = $quote ? $quote->getStoreId() : null;
        $methods = [];
        $specification = $this->methodSpecificationFactory->create([AbstractMethod::CHECK_ZERO_TOTAL]);
        foreach ($this->paymentHelper->getStoreMethods($store, $quote) as $method) {
            if ($this->_canUseMethod($method, $quote) && $specification->isApplicable($method, $quote)) {
                $method->setInfoInstance($quote->getPayment());
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\MethodInterface $method
     * @param \Magento\Sales\Model\Quote $quote
     * @return bool
     */
    protected function _canUseMethod($method, \Magento\Sales\Model\Quote $quote)
    {
        return $this->methodSpecificationFactory->create(
            [
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ]
        )->isApplicable(
            $method,
            $quote
        );
    }
}
