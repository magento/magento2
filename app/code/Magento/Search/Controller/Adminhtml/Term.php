<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

abstract class Term extends Action
{
    /**
     * Add search term breadcrumbs
     *
     * @return Page
     */
    protected function createPage()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Search::search_terms')
            ->addBreadcrumb(__('Search'), __('Search'));
        return $resultPage;
    }

    /**
     * Determine if action is allowed for reports
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return match ($this->getRequest()->getActionName()) {
            'exportSearchCsv', 'exportSearchExcel' =>
                $this->_authorization->isAllowed('Magento_Reports::report_search'),
            default =>
                $this->_authorization->isAllowed('Magento_Search::search'),
        };
    }
}
