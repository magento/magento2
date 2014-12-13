<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
