<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Magento\AdminNotification\Controller\Adminhtml\Notification;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class Index
 *
 * @package Magento\AdminNotification\Controller\Adminhtml\Notification
 */
class Index extends Notification implements HttpGetActionInterface
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
