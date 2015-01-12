<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Centinel Index Controller
 *
 * @author   Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Centinel\Controller\Adminhtml\Centinel;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
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
        return $this->_objectManager->get('Magento\Sales\Model\AdminOrder\Create')->getQuote()->getPayment();
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
