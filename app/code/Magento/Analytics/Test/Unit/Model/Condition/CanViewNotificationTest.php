<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Condition;

use Magento\Analytics\Model\Condition\CanViewNotification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Analytics\Model\NotificationFlag;

/**
 * Class CanViewNotificationTest
 */
class CanViewNotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NotificationFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationFlagMock;

    /**
     * @var CanViewNotification
     */
    private $canViewNotification;

    public function setUp()
    {
        $this->notificationFlagMock = $this->getMockBuilder(NotificationFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'notificationFlag' => $this->notificationFlagMock
            ]
        );
    }

    public function testUserShouldSeeNotification()
    {
        $this->notificationFlagMock->expects($this->once())
            ->method('hasNotificationValueForCurrentUser')
            ->willReturn(false);
        $this->notificationFlagMock->expects($this->once())
            ->method('storeNotificationValueForCurrentUser')
            ->willReturn(true);
        $this->assertTrue($this->canViewNotification->isVisible([]));
    }

    public function testUserShouldNotSeeNotification()
    {
        $this->notificationFlagMock->expects($this->once())
            ->method('hasNotificationValueForCurrentUser')
            ->willReturn(true);
        $this->assertFalse($this->canViewNotification->isVisible([]));
    }
}
