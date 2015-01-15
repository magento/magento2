<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\Model\Exception;

class Void extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View
{
    /**
     * Void invoice action
     *
     * @return void
     */
    public function execute()
    {
        $invoice = $this->getInvoice();
        if (!$invoice) {
            $this->_forward('noroute');
            return;
        }
        try {
            $invoice->void();
            $invoice->getOrder()->setIsInProcess(true);
            $this->_objectManager->create(
                'Magento\Framework\DB\Transaction'
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            )->save();
            $this->messageManager->addSuccess(__('The invoice has been voided.'));
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Invoice voiding error'));
        }
        $this->_redirect('sales/*/view', ['invoice_id' => $invoice->getId()]);
    }
}
