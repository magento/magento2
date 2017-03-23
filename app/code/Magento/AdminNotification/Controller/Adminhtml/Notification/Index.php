<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class Index extends \Magento\AdminNotification\Controller\Adminhtml\Notification
{
    /**
     * @return void
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
        $this->_view->renderLayout();
    }
}
