<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Form;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Base container block for payment methods forms
 *
 * @method \Magento\Sales\Model\Quote getQuote()
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Container extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /** @var  \Magento\Payment\Model\Checks\SpecificationFactory */
    protected $methodSpecificationFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        array $data = []
    ) {
        $this->_paymentHelper = $paymentHelper;
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare children blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /**
         * Create child blocks for payment methods forms
         */
        foreach ($this->getMethods() as $method) {
            $this->setChild(
                'payment.method.' . $method->getCode(),
                $this->_paymentHelper->getMethodFormBlock($method, $this->_layout)
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\MethodInterface $method
     * @return bool
     */
    protected function _canUseMethod($method)
    {
        return $this->methodSpecificationFactory->create(
            [
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ]
        )->isApplicable(
            $method,
            $this->getQuote()
        );
    }

    /**
     * Check and prepare payment method model
     *
     * Redeclare this method in child classes for declaring method info instance
     *
     * @param \Magento\Payment\Model\MethodInterface $method
     * @return $this
     */
    protected function _assignMethod($method)
    {
        $method->setInfoInstance($this->getQuote()->getPayment());
        return $this;
    }

    /**
     * Declare template for payment method form block
     *
     * @param string $method
     * @param string $template
     * @return $this
     */
    public function setMethodFormTemplate($method = '', $template = '')
    {
        if (!empty($method) && !empty($template)) {
            if ($block = $this->getChildBlock('payment.method.' . $method)) {
                $block->setTemplate($template);
            }
        }
        return $this;
    }

    /**
     * Retrieve available payment methods
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            $methods = [];
            $specification = $this->methodSpecificationFactory->create([AbstractMethod::CHECK_ZERO_TOTAL]);
            foreach ($this->_paymentHelper->getStoreMethods($store, $quote) as $method) {
                if ($this->_canUseMethod($method) && $specification->isApplicable($method, $this->getQuote())) {
                    $this->_assignMethod($method);
                    $methods[] = $method;
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }

    /**
     * Retrieve code of current payment method
     *
     * @return string|false
     */
    public function getSelectedMethodCode()
    {
        $methods = $this->getMethods();
        if (!empty($methods)) {
            reset($methods);
            return current($methods)->getCode();
        }
        return false;
    }
}
