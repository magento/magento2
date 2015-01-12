<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

class Report extends \Magento\Reports\Controller\Adminhtml\Index
{
    /**
     * Add reports to breadcrumb
     *
     * @return $this
     */
    public function _initAction()
    {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        return $this;
    }

    /**
     * Search terms report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_eventManager->dispatch('on_view_report', ['report' => 'search']);

        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_search'
        )->_addBreadcrumb(
            __('Search Terms'),
            __('Search Terms')
        );
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Search Terms Report'));
        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Reports::report_search');
    }
}
