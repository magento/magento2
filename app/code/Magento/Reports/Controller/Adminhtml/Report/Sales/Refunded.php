<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Reports\Model\Flag;

class Refunded extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Refunds report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_REFUNDED_FLAG_CODE, 'refunded');

        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_salesroot_refunded'
        )->_addBreadcrumb(
            __('Total Refunded'),
            __('Total Refunded')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Refunds Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_refunded.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
