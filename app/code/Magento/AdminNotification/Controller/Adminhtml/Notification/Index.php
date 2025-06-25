<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\AdminNotification\Controller\Adminhtml\Notification implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_AdminNotification::system_adminnotification'
        )->_addBreadcrumb(
            __('Messages Inbox'),
            __('Messages Inbox')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Notifications'));
        return $this->_view->renderLayout();
    }
}
