<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\NotificationTime;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NotificationTimeTest
 */
class NotificationTimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextInterfaceMock;

    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactoryMock;

    /**
     * @var \DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var NotificationTime
     */
    private $notificationTime;

    /**
     * @var int
     */
    private $value;

    /**
     * @var int
     */
    private $userId;

    public function setUp()
    {
        $this->value = 10005000;
        $this->userId = 1;

        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContextInterfaceMock = $this->getMockBuilder(UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeFactoryMock = $this->getMockBuilder(DateTimeFactory::class)
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(\DateTime::class)
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->notificationTime = $objectManagerHelper->getObject(
            NotificationTime::class,
            [
                'flagManager' => $this->flagManagerMock,
                'userContext' => $this->userContextInterfaceMock,
                'dateTimeFactory' => $this->dateTimeFactoryMock
            ]
        );
    }

    public function testStoreLastTimeNotificationForCurrentUser()
    {
        $this->userContextInterfaceMock->expects($this->once())
            ->method("getUserId")
            ->willReturn(1);
        $this->dateTimeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dateTimeMock);
        $this->dateTimeMock->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(10005000);

        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with(NotificationTime::NOTIFICATION_TIME . $this->userId, $this->value)
            ->willReturn(true);
        $this->assertTrue($this->notificationTime->storeLastTimeNotificationForCurrentUser());
    }

    public function testGetLastTimeNotificationForCurrentUser()
    {
        $this->userContextInterfaceMock->expects($this->once())
            ->method("getUserId")
            ->willReturn(1);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with(NotificationTime::NOTIFICATION_TIME . $this->userId)
            ->willReturn(true);
        $this->assertEquals($this->value, $this->notificationTime->getLastTimeNotificationForCurrentUser());
    }

    public function testUnsetLastTimeNotificationValueForCurrentUser()
    {
        $this->userContextInterfaceMock->expects($this->once())
            ->method("getUserId")
            ->willReturn(1);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('deleteFlag')
            ->with(NotificationTime::NOTIFICATION_TIME . $this->userId)
            ->willReturn(true);
        $this->assertTrue($this->notificationTime->unsetLastTimeNotificationValueForCurrentUser());
    }
}
