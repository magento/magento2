<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Reports\Model\Flag;

class Bestsellers extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Best sellers report action
     *
     * @return void
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
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Best Sellers Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_bestsellers.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
