<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class Carts extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Get shopping carts from all websites for specified client
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $this->_view->loadLayout();
        $this->prepareDefaultCustomerTitle();
        $this->_view->renderLayout();
    }
}
