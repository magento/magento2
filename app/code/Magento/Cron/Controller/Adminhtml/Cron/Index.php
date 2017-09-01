<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Controller\Adminhtml\Cron;

class Index extends \Magento\Cron\Controller\Adminhtml\Cron
{
    /**
     * Display cron management grid
     * @return \Magento\Cron\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Cron\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Cron::cron_management');
        $resultPage->getConfig()->getTitle()->prepend(__('Cron Management'));
        return $resultPage;
    }
}
