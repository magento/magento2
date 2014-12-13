<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Transactions;

use Magento\Backend\App\Action;

class Fetch extends \Magento\Sales\Controller\Adminhtml\Transactions
{
    /**
     * Fetch transaction details action
     *
     * @return void
     */
    public function execute()
    {
        $txn = $this->_initTransaction();
        if (!$txn) {
            return;
        }
        try {
            $txn->getOrderPaymentObject()->setOrder($txn->getOrder())->importTransactionInfo($txn);
            $txn->save();
            $this->messageManager->addSuccess(__('The transaction details have been updated.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t update the transaction details.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->_redirect('sales/transactions/view', ['_current' => true]);
    }
}
