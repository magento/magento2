<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Reports\Controller\Adminhtml\Index as ReportsIndexController;
use Magento\Framework\Controller\ResultFactory;

class Report extends ReportsIndexController
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::report_search';

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
}
