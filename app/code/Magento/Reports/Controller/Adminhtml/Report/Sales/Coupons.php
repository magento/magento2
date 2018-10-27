<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Reports\Model\Flag;

class Coupons extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Coupons report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_COUPONS_FLAG_CODE, 'coupons');

        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_salesroot_coupons'
        )->_addBreadcrumb(
            __('Coupons'),
            __('Coupons')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Coupons Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_coupons.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
