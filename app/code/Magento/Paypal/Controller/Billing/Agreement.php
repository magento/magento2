<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing;

use Magento\Framework\App\RequestInterface;

/**
 * Billing agreements controller
 */
abstract class Agreement extends \Magento\Framework\App\Action\Action
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
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * Init billing agreement model from request
     *
     * @return \Magento\Paypal\Model\Billing\Agreement|false
     */
    protected function _initAgreement()
    {
        $agreementId = $this->getRequest()->getParam('agreement');
        if ($agreementId) {
            /** @var \Magento\Paypal\Model\Billing\Agreement $billingAgreement */
            $billingAgreement = $this->_objectManager->create(\Magento\Paypal\Model\Billing\Agreement::class)
                ->load($agreementId);
            $currentCustomerId = $this->_getSession()->getCustomerId();
            $agreementCustomerId = $billingAgreement->getCustomerId();
            if ($billingAgreement->getId() && $agreementCustomerId == $currentCustomerId) {
                $this->_coreRegistry->register('current_billing_agreement', $billingAgreement);
                return $billingAgreement;
            }
        }
        $this->messageManager->addErrorMessage(
            __('Please specify the correct billing agreement ID and try again.')
        );
        $this->_redirect('*/*/');
        return false;
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_objectManager->get(\Magento\Customer\Model\Session::class);
    }
}
