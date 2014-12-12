<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Statistics;

class Index extends \Magento\Reports\Controller\Adminhtml\Report\Statistics
{
    /**
     * Refresh statistics action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_statistics_refresh'
        )->_addBreadcrumb(
            __('Refresh Statistics'),
            __('Refresh Statistics')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Refresh Statistics'));
        $this->_view->renderLayout();
    }
}
