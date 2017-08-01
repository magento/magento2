<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Block\Checkout;

/**
 * Multishipping billing information
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Billing extends \Magento\Payment\Block\Form\Container
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @since 2.0.0
     */
    protected $_multishipping;

    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Payment\Model\Method\SpecificationInterface
     * @since 2.0.0
     */
    protected $paymentSpecification;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification
     * @param array $data
     * @param array $additionalChecks
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification,
        array $data = [],
        array $additionalChecks = []
    ) {
        $this->_multishipping = $multishipping;
        $this->_checkoutSession = $checkoutSession;
        $this->paymentSpecification = $paymentSpecification;
        parent::__construct($context, $paymentHelper, $methodSpecificationFactory, $data, $additionalChecks);
        $this->_isScopePrivate = true;
    }

    /**
     * Prepare children blocks
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Billing Information - %1', $this->pageConfig->getTitle()->getDefault())
        );

        return parent::_prepareLayout();
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\Method\AbstractMethod|null $method
     * @return bool
     * @since 2.0.0
     */
    protected function _canUseMethod($method)
    {
        return $method && $this->paymentSpecification->isSatisfiedBy(
            $method->getCode()
        ) && parent::_canUseMethod(
            $method
        );
    }

    /**
     * Retrieve code of current payment method
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getSelectedMethodCode()
    {
        $method = $this->getQuote()->getPayment()->getMethod();
        if ($method) {
            return $method;
        }
        return false;
    }

    /**
     * Retrieve billing address
     *
     * @return \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    public function getAddress()
    {
        $address = $this->getData('address');
        if ($address === null) {
            $address = $this->_multishipping->getQuote()->getBillingAddress();
            $this->setData('address', $address);
        }
        return $address;
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Getter
     *
     * @return float
     * @since 2.0.0
     */
    public function getQuoteBaseGrandTotal()
    {
        return (double)$this->getQuote()->getBaseGrandTotal();
    }

    /**
     * Retrieve url for select billing address
     *
     * @return string
     * @since 2.0.0
     */
    public function getSelectAddressUrl()
    {
        return $this->getUrl('*/checkout_address/selectBilling');
    }

    /**
     * Retrieve data post destination url
     *
     * @return string
     * @since 2.0.0
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/overview');
    }

    /**
     * Retrieve back url
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/backtoshipping');
    }
}
