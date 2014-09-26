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
namespace Magento\Multishipping\Block\Checkout;

/**
 * Multishipping billing information
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Billing extends \Magento\Payment\Block\Form\Container
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $_multishipping;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Payment\Model\Method\SpecificationInterface
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
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification,
        array $data = array()
    ) {
        $this->_multishipping = $multishipping;
        $this->_checkoutSession = $checkoutSession;
        $this->paymentSpecification = $paymentSpecification;
        parent::__construct($context, $paymentHelper, $methodSpecificationFactory, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Prepare children blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->setTitle(__('Billing Information - %1', $this->pageConfig->getDefaultTitle()));

        return parent::_prepareLayout();
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\Method\AbstractMethod|null $method
     * @return bool
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
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getAddress()
    {
        $address = $this->getData('address');
        if (is_null($address)) {
            $address = $this->_multishipping->getQuote()->getBillingAddress();
            $this->setData('address', $address);
        }
        return $address;
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Getter
     *
     * @return float
     */
    public function getQuoteBaseGrandTotal()
    {
        return (double)$this->getQuote()->getBaseGrandTotal();
    }

    /**
     * Retrieve url for select billing address
     *
     * @return string
     */
    public function getSelectAddressUrl()
    {
        return $this->getUrl('*/checkout_address/selectBilling');
    }

    /**
     * Retrieve data post destination url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/overview');
    }

    /**
     * Retrieve back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/backtoshipping');
    }
}
