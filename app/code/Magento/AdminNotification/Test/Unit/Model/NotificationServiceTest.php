<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\AdminNotification\Model\NotificationService
 */
namespace Magento\AdminNotification\Test\Unit\Model;

class NotificationServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Retrieve instance of notification service model
     *
     * @param $notificationId
     * @return \Magento\AdminNotification\Model\NotificationService
     */
    protected function _getServiceInstanceForMarkAsReadTest($notificationId)
    {
        /**
         * @var
         *  $notificationFactory \PHPUnit_Framework_MockObject_MockObject|\Magento\AdminNotification\Model\InboxFactory
         */
        $notificationFactory = $this->createPartialMock(
            \Magento\AdminNotification\Model\InboxFactory::class,
            ['create']
        );
        $notification = $this->createPartialMock(
            \Magento\AdminNotification\Model\Inbox::class,
            ['load', 'getId', 'save', 'setIsRead', '__sleep', '__wakeup']
        );
        $notification->expects($this->once())->method('load')->with($notificationId)->will($this->returnSelf());
        $notification->expects($this->once())->method('getId')->will($this->returnValue($notificationId));

        // when notification Id is valid, add additional expectations
        if ($notificationId) {
            $notification->expects($this->once())->method('save')->will($this->returnSelf());
            $notification->expects($this->once())->method('setIsRead')->with(1)->will($this->returnSelf());
        }

        $notificationFactory->expects($this->once())->method('create')->will($this->returnValue($notification));
        return new \Magento\AdminNotification\Model\NotificationService($notificationFactory);
    }

    public function testMarkAsRead()
    {
        $notificationId = 1;
        $service = $this->_getServiceInstanceForMarkAsReadTest($notificationId);
        $service->markAsRead($notificationId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Wrong notification ID specified.
     */
    public function testMarkAsReadThrowsExceptionWhenNotificationIdIsInvalid()
    {
        $notificationId = null;
        $service = $this->_getServiceInstanceForMarkAsReadTest($notificationId);
        $service->markAsRead($notificationId);
    }
}
