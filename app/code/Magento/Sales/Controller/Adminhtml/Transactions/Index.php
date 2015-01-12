<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
