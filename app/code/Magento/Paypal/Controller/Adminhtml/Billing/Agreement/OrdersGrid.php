<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

class OrdersGrid extends \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
{
    /**
     * Related orders ajax action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initBillingAgreement();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
