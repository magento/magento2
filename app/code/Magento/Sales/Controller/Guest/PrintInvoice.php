<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action\Context;

class PrintInvoice extends \Magento\Sales\Controller\AbstractController\PrintInvoice
{
    /**
     * @var OrderLoader
     */
    protected $orderLoader;

    /**
     * @param Context $context
     * @param OrderViewAuthorization $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param OrderLoader $orderLoader
     */
    public function __construct(
        Context $context,
        OrderViewAuthorization $orderAuthorization,
        \Magento\Framework\Registry $registry,
        OrderLoader $orderLoader
    ) {
        $this->orderLoader = $orderLoader;
        parent::__construct($context, $orderAuthorization, $registry);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->orderLoader->load($this->_request, $this->_response)) {
            return;
        }

        $invoiceId = (int)$this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoiceId);
            $order = $invoice->getOrder();
        } else {
            $order = $this->_coreRegistry->registry('current_order');
        }

        if ($this->orderAuthorization->canView($order)) {
            if (isset($invoice)) {
                $this->_coreRegistry->register('current_invoice', $invoice);
            }
            $this->_view->loadLayout('print');
            $this->_view->renderLayout();
        } else {
            $this->_redirect('sales/guest/form');
        }
    }
}
