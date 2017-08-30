<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cron;

class Index extends \Magento\Backend\Controller\Adminhtml\Cron
{
    /**
     * Display cron management grid
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::cron_management');
        $resultPage->getConfig()->getTitle()->prepend(__('Cron Management'));
        return $resultPage;
    }
}
