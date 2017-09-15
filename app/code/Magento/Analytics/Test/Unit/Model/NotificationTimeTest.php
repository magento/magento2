<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\NotificationTime;
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
     * @var NotificationTime
     */
    private $notificationTime;

    public function setUp()
    {
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->notificationTime = $objectManagerHelper->getObject(
            NotificationTime::class,
            [
                'flagManager' => $this->flagManagerMock,
            ]
        );
    }

    public function testStoreLastTimeNotification()
    {
        $value = 100500;

        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with(NotificationTime::NOTIFICATION_TIME, $value)
            ->willReturn(true);
        $this->assertTrue($this->notificationTime->storeLastTimeNotification($value));
    }

    public function testGetLastTimeNotification()
    {
        $value = 100500;

        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with(NotificationTime::NOTIFICATION_TIME)
            ->willReturn(true);
        $this->assertEquals($value, $this->notificationTime->getLastTimeNotification());
    }

    public function testUnsetLastTimeNotificationValue()
    {
        $this->flagManagerMock
            ->expects($this->once())
            ->method('deleteFlag')
            ->with(NotificationTime::NOTIFICATION_TIME)
            ->willReturn(true);
        $this->assertTrue($this->notificationTime->unsetLastTimeNotificationValue());
    }
}
