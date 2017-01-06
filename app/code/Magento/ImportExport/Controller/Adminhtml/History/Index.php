<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\History;

use Magento\ImportExport\Controller\Adminhtml\History as HistoryController;
use Magento\Framework\Controller\ResultFactory;

class Index extends HistoryController
{
    /**
     * Index action.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_ImportExport::system_convert_history');
        $resultPage->getConfig()->getTitle()->prepend(__('History'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import History'));
        $resultPage->addBreadcrumb(__('Import history'), __('Import history'));
        return $resultPage;
    }
}
