<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Reports\Model\Flag;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Sales\Tax
 *
 * @since 2.0.0
 */
class Tax extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Tax report action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_TAX_FLAG_CODE, 'tax');

        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_salesroot_tax'
        )->_addBreadcrumb(
            __('Tax'),
            __('Tax')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Tax Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_tax.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
