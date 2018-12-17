<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\AdminNotification\Model\NotificationService
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model;

use Magento\AdminNotification\Model\Inbox;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\AdminNotification\Model\NotificationService;
use PHPUnit\Framework\TestCase;

/**
 * Class NotificationServiceTest
 *
 * @package Magento\AdminNotification\Test\Unit\Model
 */
class NotificationServiceTest extends TestCase
{
    /**
     * Retrieve instance of notification service model
     *
     * @param int|null $notificationId
     * @return NotificationService
     */
    private function getServiceInstanceForMarkAsReadTest($notificationId)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\AdminNotification\Model\InboxFactory $notificationFactory */ //phpcs:ignore
        $notificationFactory = $this->createPartialMock(
            InboxFactory::class,
            ['create']
        );
        $notification = $this->createPartialMock(
            Inbox::class,
            ['load', 'getId', 'save', 'setIsRead', '__sleep', '__wakeup']
        );
        $notification->expects(static::once())->method('load')->with($notificationId)->will(static::returnSelf());
        $notification->expects(static::once())->method('getId')->will(static::returnValue($notificationId));

        // when notification Id is valid, add additional expectations
        if ($notificationId) {
            $notification->expects(static::once())->method('save')->will(static::returnSelf());
            $notification->expects(static::once())->method('setIsRead')->with(1)->will(static::returnSelf());
        }

        $notificationFactory->expects(static::once())->method('create')->will(static::returnValue($notification));
        return new NotificationService($notificationFactory);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testMarkAsRead()
    {
        $notificationId = 1;
        $service = $this->getServiceInstanceForMarkAsReadTest($notificationId);
        $service->markAsRead($notificationId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Wrong notification ID specified.
     */
    public function testMarkAsReadThrowsExceptionWhenNotificationIdIsInvalid()
    {
        $notificationId = null;
        $service = $this->getServiceInstanceForMarkAsReadTest($notificationId);
        $service->markAsRead($notificationId);
    }
}
