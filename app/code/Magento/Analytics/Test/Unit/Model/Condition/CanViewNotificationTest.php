<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Condition;

use Magento\Analytics\Model\Condition\CanViewNotification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Analytics\Model\notificationFlagManager;
use Magento\Backend\Model\Auth\Session;

/**
 * Class CanViewNotificationTest
 */
class CanViewNotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CanViewNotification
     */
    private $canViewNotification;

    /**
     * @var NotificationFlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationFlagManagerMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    public function setUp()
    {
        $this->notificationFlagManagerMock = $this->getMockBuilder(NotificationFlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getId'])
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'notificationFlagManager' => $this->notificationFlagManagerMock,
                'session' => $this->sessionMock
            ]
        );
    }

    public function testUserShouldSeeNotification()
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->notificationFlagManagerMock->expects($this->once())
            ->method('isUserNotified')
            ->willReturn(false);
        $this->notificationFlagManagerMock->expects($this->once())
            ->method('setNotifiedUser')
            ->willReturn(true);
        $this->assertTrue($this->canViewNotification->isVisible([]));
    }

    public function testUserShouldNotSeeNotification()
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->notificationFlagManagerMock->expects($this->once())
            ->method('isUserNotified')
            ->willReturn(true);
        $this->assertFalse($this->canViewNotification->isVisible([]));
    }
}
