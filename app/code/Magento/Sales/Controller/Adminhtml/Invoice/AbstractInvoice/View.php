<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class View extends \Magento\Backend\App\Action
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(Context $context, Registry $registry)
    {
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }

    /**
     * Invoice information page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('invoice_id')) {
            $this->_forward('view', 'order_invoice', null, ['come_from' => 'invoice']);
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * @return \Magento\Sales\Model\Order\Invoice|bool
     */
    protected function getInvoice()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if (!$invoiceId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoiceId);
        if (!$invoice) {
            return false;
        }
        $this->registry->register('current_invoice', $invoice);
        return $invoice;
    }
}
