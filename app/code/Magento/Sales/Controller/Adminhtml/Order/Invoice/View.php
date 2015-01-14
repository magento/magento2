<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

class View extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View
{
    /**
     * Invoice information page
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

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::sales_order');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Invoices'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(sprintf("#%s", $invoice->getIncrementId()));
        $this->_view->getLayout()->getBlock(
            'sales_invoice_view'
        )->updateBackButtonUrl(
            $this->getRequest()->getParam('come_from')
        );
        $this->_view->renderLayout();
    }
}
