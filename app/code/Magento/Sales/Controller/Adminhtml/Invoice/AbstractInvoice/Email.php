<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;

/**
 * Class Email
 *
 * @package Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice
 */
abstract class Email extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if (!$invoiceId) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $invoice = $this->_objectManager->create(\Magento\Sales\Api\InvoiceRepositoryInterface::class)->get($invoiceId);
        if (!$invoice) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $isEnabled = (bool)$invoice->getStore()->getConfig('sales_email/invoice/enabled');
        if (!$isEnabled) {
            $this->messageManager->addWarningMessage(
                __('Invoice emails are disabled for this store. No email was sent.')
            );
            return $this->resultRedirectFactory->create()->setPath(
                'sales/invoice/view',
                ['order_id' => $invoice->getOrder()->getId(), 'invoice_id' => $invoiceId]
            );
        }

        $this->_objectManager->create(
            \Magento\Sales\Api\InvoiceManagementInterface::class
        )->notify($invoice->getEntityId());

        $this->messageManager->addSuccessMessage(__('You sent the message.'));
        return $this->resultRedirectFactory->create()->setPath(
            'sales/invoice/view',
            ['order_id' => $invoice->getOrder()->getId(), 'invoice_id' => $invoiceId]
        );
    }
}
