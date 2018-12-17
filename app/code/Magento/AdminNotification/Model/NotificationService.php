<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Notification service model
 *
 * @package Magento\AdminNotification\Model
 * @author Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class NotificationService
{
    /**
     * @var InboxFactory $notificationFactory
     */
    protected $_notificationFactory; //phpcs:ignore

    /**
     * @param InboxFactory $notificationFactory
     */
    public function __construct(InboxFactory $notificationFactory)
    {
        $this->_notificationFactory = $notificationFactory;
    }

    /**
     * Mark notification as read
     *
     * @param int|null $notificationId
     * @return void
     * @throws LocalizedException
     * @throws \Exception
     */
    public function markAsRead($notificationId): void
    {
        $notification = $this->_notificationFactory->create();
        $notification->load($notificationId);
        if (!$notification->getId()) {
            throw new LocalizedException(__('Wrong notification ID specified.'));
        }
        $notification->setIsRead(1);
        $notification->save();
    }
}
