<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

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
        $invoice = $this->_objectManager->create('Magento\Sales\Api\InvoiceRepositoryInterface')->get($invoiceId);
        if (!$invoice) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $this->_objectManager->create('Magento\Sales\Api\InvoiceManagementInterface')->notify($invoice->getEntityId());

        $this->messageManager->addSuccess(__('You sent the message.'));
        return $this->resultRedirectFactory->create()->setPath(
            'sales/invoice/view',
            ['order_id' => $invoice->getOrder()->getId(), 'invoice_id' => $invoiceId]
        );
    }
}
