<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice;

/**
 * Class Email
 *
 * @package Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice
 */
abstract class Email extends \Magento\Backend\App\Action
{
    /**
     * Check if email sending is allowed for the current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }

    /**
     * Notify user
     *
     * @return void
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if (!$invoiceId) {
            return;
        }
        $invoice = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoiceId);
        if (!$invoice) {
            return;
        }

        $this->_objectManager->create('Magento\Sales\Model\Order\InvoiceNotifier')
            ->notify($invoice);

        $this->messageManager->addSuccess(__('We sent the message.'));
        $this->_redirect(
            'sales/invoice/view',
            ['order_id' => $invoice->getOrder()->getId(), 'invoice_id' => $invoiceId]
        );
    }
}
