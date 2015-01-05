<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice;

abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Sales::sales_invoice'
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('Invoices'),
            __('Invoices')
        );
        return $this;
    }

    /**
     * Invoices grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Invoices'));
        $this->_view->renderLayout();
    }
}
