<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Condition;

use Magento\Analytics\Model\Condition\CanViewNotification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Analytics\Model\NotificationFlagManager;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Module\ModuleListInterface;

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

    /**
     * @var ModuleListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleListMock;

    public function setUp()
    {
        $this->notificationFlagManagerMock = $this->getMockBuilder(NotificationFlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getId'])
            ->getMock();
        $this->moduleListMock = $this->getMockBuilder(ModuleListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'notificationFlagManager' => $this->notificationFlagManagerMock,
                'session' => $this->sessionMock,
                'moduleList' => $this->moduleListMock
            ]
        );
    }

    public function isVisibleProvider()
    {
        return [
            [1, false, false, true],
            [1, true, false, false],
            [1, false, true, false],
            [1, true, true, false]
        ];
    }

    /**
     * @dataProvider isVisibleProvider
     * @param int $userId
     * @param bool $hasNotificationModule
     * @param bool $isUserNotified
     * @param bool $expected
     */
    public function testIsVisible($userId, $hasNotificationModule, $isUserNotified, $expected)
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn($userId);
        $this->moduleListMock->expects($this->once())
            ->method('has')
            ->willReturn($hasNotificationModule);
        $this->notificationFlagManagerMock->expects($this->any())
            ->method('isUserNotified')
            ->willReturn($isUserNotified);
        $this->notificationFlagManagerMock->expects($this->any())
            ->method('setNotifiedUser')
            ->willReturn(true);

        $this->assertEquals($expected, $this->canViewNotification->isVisible([]));
    }
}
