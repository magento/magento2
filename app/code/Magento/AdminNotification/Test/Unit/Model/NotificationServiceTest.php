<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\AdminNotification\Model\NotificationService
 */
namespace Magento\AdminNotification\Test\Unit\Model;

use Magento\AdminNotification\Model\Inbox;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\AdminNotification\Model\NotificationService;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    /**
     * Retrieve instance of notification service model
     *
     * @param $notificationId
     * @return NotificationService
     */
    protected function _getServiceInstanceForMarkAsReadTest($notificationId)
    {
        /**
         * @var $notificationFactory MockObject|InboxFactory
         */
        $notificationFactory = $this->createPartialMock(
            InboxFactory::class,
            ['create']
        );
        $notification = $this->createPartialMock(
            Inbox::class,
            ['load', 'getId', 'save', 'setData', '__sleep', '__wakeup']
        );
        $notification->expects($this->once())->method('load')->with($notificationId)->will($this->returnSelf());
        $notification->expects($this->once())->method('getId')->will($this->returnValue($notificationId));

        // when notification Id is valid, add additional expectations
        if ($notificationId) {
            $notification->expects($this->once())->method('save')->will($this->returnSelf());
            $notification->expects($this->once())->method('setData')
                ->with('is_read', 1)->will($this->returnSelf());
        }

        $notificationFactory->expects($this->once())->method('create')->will($this->returnValue($notification));
        return new NotificationService($notificationFactory);
    }

    public function testMarkAsRead()
    {
        $notificationId = 1;
        $service = $this->_getServiceInstanceForMarkAsReadTest($notificationId);
        $service->markAsRead($notificationId);
    }

    public function testMarkAsReadThrowsExceptionWhenNotificationIdIsInvalid()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Wrong notification ID specified.');

        $notificationId = null;
        $service = $this->_getServiceInstanceForMarkAsReadTest($notificationId);
        $service->markAsRead($notificationId);
    }
}
