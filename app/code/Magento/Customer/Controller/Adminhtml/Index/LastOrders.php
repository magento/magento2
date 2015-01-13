<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class LastOrders extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer last orders grid for ajax
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultCustomerTitle($resultPage);
        return $resultPage;
    }
}
