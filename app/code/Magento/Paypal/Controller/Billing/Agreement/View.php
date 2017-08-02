<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

/**
 * Class \Magento\Paypal\Controller\Billing\Agreement\View
 *
 * @since 2.0.0
 */
class View extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * View billing agreement
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        if (!($agreement = $this->_initAgreement())) {
            return;
        }
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Billing Agreements'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            __('Billing Agreement # %1', $agreement->getReferenceId())
        );
        $navigationBlock = $this->_view->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('paypal/billing_agreement/');
        }
        $this->_view->renderLayout();
    }
}
