<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultCustomerTitle($resultPage);
        $resultPage->getLayout()->getBlock('admin.customer.view.cart')->setWebsiteId(
            (int)$this->getRequest()->getParam('website_id')
        );
        return $resultPage;
    }
}
