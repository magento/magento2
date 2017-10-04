<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Condition;

use Magento\Analytics\Model\Condition\CanViewNotification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Analytics\Model\NotificationTime;

/**
 * Class CanViewNotificationTest
 */
class CanViewNotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NotificationTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationTimeMock;

    /**
     * @var CanViewNotification
     */
    private $canViewNotification;

    public function setUp()
    {
        $this->notificationTimeMock = $this->getMockBuilder(NotificationTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'notificationTime' => $this->notificationTimeMock
            ]
        );
    }

    public function testUserShouldSeeNotification()
    {
        $this->notificationTimeMock->expects($this->once())
            ->method('getLastTimeNotificationForCurrentUser')
            ->willReturn(false);
        $this->notificationTimeMock->expects($this->once())
            ->method('storeLastTimeNotificationForCurrentUser')
            ->willReturn(true);
        $this->assertTrue($this->canViewNotification->isVisible([]));
    }

    public function testUserShouldNotSeeNotification()
    {
        $this->notificationTimeMock->expects($this->once())
            ->method('getLastTimeNotificationForCurrentUser')
            ->willReturn(true);
        $this->assertFalse($this->canViewNotification->isVisible([]));
    }
}
