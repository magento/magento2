<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Transactions;

use Magento\Backend\App\Action;

class View extends \Magento\Sales\Controller\Adminhtml\Transactions
{
    /**
     * View Transaction Details action
     *
     * @return void
     */
    public function execute()
    {
        $txn = $this->_initTransaction();
        if (!$txn) {
            return;
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::sales_transactions');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Transactions'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(sprintf("#%s", $txn->getTxnId()));
        $this->_view->renderLayout();
    }
}
