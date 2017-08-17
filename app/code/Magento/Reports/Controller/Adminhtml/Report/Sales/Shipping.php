<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Reports\Model\Flag;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Sales\Shipping
 *
 */
class Shipping extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Shipping report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_SHIPPING_FLAG_CODE, 'shipping');

        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_salesroot_shipping'
        )->_addBreadcrumb(
            __('Shipping'),
            __('Shipping')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_shipping.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
