<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Statistics;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Reports\Controller\Adminhtml\Report\Statistics implements HttpGetActionInterface
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
