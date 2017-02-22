<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

class View extends \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
{
    /**
     * View billing agreement action
     *
     * @return void
     */
    public function execute()
    {
        $agreementModel = $this->_initBillingAgreement();

        if ($agreementModel) {
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Paypal::paypal_billing_agreement');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                sprintf("#%s", $agreementModel->getReferenceId())
            );
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Billing Agreements'));
            $this->_view->renderLayout();
            return;
        }

        $this->_redirect('paypal/*/');
        return;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Paypal::billing_agreement_actions_view');
    }
}
