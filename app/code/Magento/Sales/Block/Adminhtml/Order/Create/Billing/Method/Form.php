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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Billing\Method;

/**
 * Adminhtml sales order create payment method form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Payment\Block\Form\Container
{
    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = array()
    ) {
        $this->_sessionQuote = $sessionQuote;
        parent::__construct($context, $paymentHelper, $methodSpecificationFactory, $data);
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\MethodInterface|null $method
     * @return bool
     */
    protected function _canUseMethod($method)
    {
        return $method && $method->canUseInternal() && parent::_canUseMethod($method);
    }

    /**
     * Check existing of payment methods
     *
     * @return bool
     */
    public function hasMethods()
    {
        $methods = $this->getMethods();
        if (is_array($methods) && count($methods)) {
            return true;
        }
        return false;
    }

    /**
     * Get current payment method code or the only available, if there is only one method
     *
     * @return string|false
     */
    public function getSelectedMethodCode()
    {
        // One available method. Return this method as selected, because no other variant is possible.
        $methods = $this->getMethods();
        if (count($methods) == 1) {
            foreach ($methods as $method) {
                return $method->getCode();
            }
        }

        // Several methods. If user has selected some method - then return it.
        $currentMethodCode = $this->getQuote()->getPayment()->getMethod();
        if ($currentMethodCode) {
            return $currentMethodCode;
        }

        // Several methods, but no preference for one of them.
        return false;
    }

    /**
     * Enter description here...
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->_sessionQuote->getQuote();
    }

    /**
     * Whether switch/solo card type available
     *
     * @return true
     */
    public function hasSsCardType()
    {
        $availableTypes = explode(',', $this->getQuote()->getPayment()->getMethod()->getConfigData('cctypes'));
        $ssPresenations = array_intersect(array('SS', 'SM', 'SO'), $availableTypes);
        if ($availableTypes && count($ssPresenations) > 0) {
            return true;
        }
        return false;
    }
}
