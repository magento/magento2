<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Reports\Model\Flag;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Sales\Bestsellers
 *
 * @since 2.0.0
 */
class Bestsellers extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Bestsellers report action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_BESTSELLERS_FLAG_CODE, 'bestsellers');

        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_products_bestsellers'
        )->_addBreadcrumb(
            __('Products Bestsellers Report'),
            __('Products Bestsellers Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Bestsellers Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_bestsellers.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
