<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Statistics;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Statistics\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Reports\Controller\Adminhtml\Report\Statistics
{
    /**
     * Refresh statistics action
     *
     * @return void
     * @since 2.0.0
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
