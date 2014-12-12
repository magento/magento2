<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

class Index extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * View billing agreements
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Billing Agreements'));
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
