<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

class Index extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Customer groups list.
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Customer::customer_group');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Customer Groups'));
        $this->_addBreadcrumb(__('Customers'), __('Customers'));
        $this->_addBreadcrumb(__('Customer Groups'), __('Customer Groups'));
        $this->_view->renderLayout();
    }
}
