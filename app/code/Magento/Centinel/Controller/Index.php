<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Centinel Authenticate Controller
 *
 */
namespace Magento\Centinel\Controller;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Return payment model
     *
     * @return \Magento\Sales\Model\Quote\Payment
     */
    protected function _getPayment()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session')->getQuote()->getPayment();
    }

    /**
     * Return Centinel validation model
     *
     * @return \Magento\Centinel\Model\Service
     */
    protected function _getValidator()
    {
        if ($this->_getPayment()->getMethodInstance()->getIsCentinelValidationEnabled()) {
            return $this->_getPayment()->getMethodInstance()->getCentinelValidator();
        }
        return false;
    }
}
