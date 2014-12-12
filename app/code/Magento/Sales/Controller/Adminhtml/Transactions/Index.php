<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Transactions;


class Index extends \Magento\Sales\Controller\Adminhtml\Transactions
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::sales_transactions');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Transactions'));
        $this->_view->renderLayout();
    }
}
