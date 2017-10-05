<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\NotificationFlag;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NotificationFlagTest
 */
class NotificationFlagTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var NotificationFlag
     */
    private $notificationFlag;

    /**
     * @var int
     */
    private $userId;

    public function setUp()
    {
        $this->userId = 1;

        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getId'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->notificationFlag = $objectManagerHelper->getObject(
            NotificationFlag::class,
            [
                'flagManager' => $this->flagManagerMock,
                'session' => $this->sessionMock
            ]
        );
    }

    public function testStoreNotificationValueForCurrentUser()
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with(NotificationFlag::NOTIFICATION_SEEN . $this->userId, 1)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlag->storeNotificationValueForCurrentUser());
    }

    public function testHasNotificationValueForCurrentUser()
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with(NotificationFlag::NOTIFICATION_SEEN . $this->userId)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlag->hasNotificationValueForCurrentUser());
    }

    public function testUnsetNotificationValueForCurrentUser()
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->flagManagerMock
            ->expects($this->once())
            ->method('deleteFlag')
            ->with(NotificationFlag::NOTIFICATION_SEEN . $this->userId)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlag->unsetNotificationValueForCurrentUser());
    }
}
