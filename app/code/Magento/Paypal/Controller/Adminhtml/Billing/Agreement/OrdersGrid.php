<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

/**
 * Class \Magento\Paypal\Controller\Adminhtml\Billing\Agreement\OrdersGrid
 *
 */
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
