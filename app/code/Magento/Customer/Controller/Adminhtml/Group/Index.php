<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

class Index extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Customer groups list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Customer::customer_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Groups'));
        $resultPage->addBreadcrumb(__('Customers'), __('Customers'));
        $resultPage->addBreadcrumb(__('Customer Groups'), __('Customer Groups'));
        return $resultPage;
    }
}
