<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\AdminNotification\Model\NotificationService
 */
namespace Magento\AdminNotification\Model;

class NotificationServiceTest extends \PHPUnit_Framework_TestCase
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
        $notificationFactory = $this->getMock(
            'Magento\AdminNotification\Model\InboxFactory',
            array('create'),
            array(),
            '',
            false
        );
        $notification = $this->getMock(
            'Magento\AdminNotification\Model\Inbox',
            array('load', 'getId', 'save', 'setIsRead', '__sleep', '__wakeup'),
            array(),
            '',
            false
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
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Wrong notification ID specified.
     */
    public function testMarkAsReadThrowsExceptionWhenNotificationIdIsInvalid()
    {
        $notificationId = null;
        $service = $this->_getServiceInstanceForMarkAsReadTest($notificationId);
        $service->markAsRead($notificationId);
    }
}
