<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Transactions;

use Magento\Backend\App\Action;

class Fetch extends \Magento\Sales\Controller\Adminhtml\Transactions
{
    /**
     * Fetch transaction details action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $txn = $this->_initTransaction();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$txn) {
            return $resultRedirect->setPath('sales/*/');
        }
        $txn->getOrderPaymentObject()->setOrder($txn->getOrder())->importTransactionInfo($txn);
        $txn->save();
        $this->messageManager->addSuccess(__('The transaction details have been updated.'));
        return $this->getDefaultResult();
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/transactions/view', ['_current' => true]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::transactions_fetch');
    }
}
