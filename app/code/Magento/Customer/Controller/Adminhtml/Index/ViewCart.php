<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class ViewCart extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Get shopping cart to view only
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $this->_view->loadLayout();
        $this->prepareDefaultCustomerTitle();
        $this->_view->getLayout()->getBlock('admin.customer.view.cart')->setWebsiteId(
            (int)$this->getRequest()->getParam('website_id')
        );
        $this->_view->renderLayout();
    }
}
