<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Payment\Model;

use \Magento\Payment\Model\Method\AbstractMethod;

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
        $methods = array();
        $specification = $this->methodSpecificationFactory->create(array(AbstractMethod::CHECK_ZERO_TOTAL));
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
            array(
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX
            )
        )->isApplicable(
            $method,
            $quote
        );
    }
}
