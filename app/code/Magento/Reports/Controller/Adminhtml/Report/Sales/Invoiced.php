<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Reports\Model\Flag;

class Invoiced extends \Magento\Reports\Controller\Adminhtml\Report\Sales implements HttpGetActionInterface
{
    /**
     * Invoice report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_INVOICE_FLAG_CODE, 'invoiced');

        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_salesroot_invoiced'
        )->_addBreadcrumb(
            __('Total Invoiced'),
            __('Total Invoiced')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Invoice Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_invoiced.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
