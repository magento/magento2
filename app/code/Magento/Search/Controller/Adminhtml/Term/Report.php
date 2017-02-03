<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Reports\Controller\Adminhtml\Index as ReportsIndexController;
use Magento\Framework\Controller\ResultFactory;

class Report extends ReportsIndexController
{
    /**
     * Search terms report action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_eventManager->dispatch('on_view_report', ['report' => 'search']);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Reports::report_search')
            ->addBreadcrumb(__('Reports'), __('Reports'))
            ->addBreadcrumb(__('Search Terms'), __('Search Terms'));
        $resultPage->getConfig()->getTitle()->set(__('Search Terms Report'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Reports::report_search');
    }
}
