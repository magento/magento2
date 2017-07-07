<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model;

/**
 * Notification service model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 */
class NotificationService
{
    /**
     * @var \Magento\AdminNotification\Model\InboxFactory $notificationFactory
     */
    protected $_notificationFactory;

    /**
     * @param \Magento\AdminNotification\Model\InboxFactory $notificationFactory
     */
    public function __construct(\Magento\AdminNotification\Model\InboxFactory $notificationFactory)
    {
        $this->_notificationFactory = $notificationFactory;
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function markAsRead($notificationId)
    {
        $notification = $this->_notificationFactory->create();
        $notification->load($notificationId);
        if (!$notification->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Wrong notification ID specified.'));
        }
        $notification->setIsRead(1);
        $notification->save();
    }
}
