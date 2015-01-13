<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class Index extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customers list action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Magento_Customer::customer_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Customers'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Customers'), __('Customers'));
        $resultPage->addBreadcrumb(__('Manage Customers'), __('Manage Customers'));

        $this->_getSession()->unsCustomerData();

        return $resultPage;
    }
}
