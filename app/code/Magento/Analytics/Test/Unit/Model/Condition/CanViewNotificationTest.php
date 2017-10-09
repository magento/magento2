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

    public function isVisibleProvider()
    {
        return [
            [1, false, true],
            [1, true, false]
        ];
    }

    /**
     * @dataProvider isVisibleProvider
     * @param int $userId
     * @param bool $isUserNotified
     * @param bool $expected
     */
    public function testIsVisible($userId, $isUserNotified, $expected)
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn($userId);
        $this->notificationFlagManagerMock->expects($this->once())
            ->method('isUserNotified')
            ->willReturn($isUserNotified);
        $this->notificationFlagManagerMock->expects($this->any())
            ->method('setNotifiedUser')
            ->willReturn(true);

        $this->assertEquals($expected, $this->canViewNotification->isVisible([]));
    }
}
