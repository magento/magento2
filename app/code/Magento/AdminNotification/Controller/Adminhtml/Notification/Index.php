<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\Page;

class Index extends \Magento\AdminNotification\Controller\Adminhtml\Notification implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_AdminNotification::system_adminnotification');
        $resultPage->addBreadcrumb(
            __('Messages Inbox'),
            __('Messages Inbox')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Notifications'));
        return $resultPage;
    }
}
