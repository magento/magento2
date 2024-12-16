<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View;

class Capture extends View implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::invoice';

    /**
     * Capture invoice action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $invoice = $this->getInvoice();
        if (!$invoice) {
            /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
        try {
            $invoiceManagement = $this->_objectManager->get(\Magento\Sales\Api\InvoiceManagementInterface::class);
            $invoiceManagement->setCapture($invoice->getEntityId());
            $invoice->getOrder()->setIsInProcess(true);
            $this->_objectManager->create(
                \Magento\Framework\DB\Transaction::class
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            )->save();
            $this->messageManager->addSuccessMessage(__('The invoice has been captured.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Invoice capturing error'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/view', ['invoice_id' => $invoice->getId()]);
        return $resultRedirect;
    }
}
