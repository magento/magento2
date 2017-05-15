<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Condition;

use Magento\Analytics\Model\Condition\CanViewNotification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class CanViewNotificationTest
 */
class CanViewNotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NotificationTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationTimeMock;

    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactoryMock;

    /**
     * @var \DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var CanViewNotification
     */
    private $canViewNotification;

    public function setUp()
    {
        $this->dateTimeFactoryMock = $this->getMockBuilder(DateTimeFactory::class)
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(\DateTime::class)
            ->getMock();
        $this->notificationTimeMock = $this->getMockBuilder(NotificationTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'notificationTime' => $this->notificationTimeMock,
                'dateTimeFactory' => $this->dateTimeFactoryMock
            ]
        );
    }

    public function testValidate()
    {
        $this->notificationTimeMock->expects($this->once())
            ->method('getLastTimeNotification')
            ->willReturn(1);
        $this->dateTimeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dateTimeMock);
        $this->dateTimeMock->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(10005000);
        $this->assertTrue($this->canViewNotification->isVisible([]));
    }

    public function testValidateFlagRemoved()
    {
        $this->notificationTimeMock->expects($this->once())
            ->method('getLastTimeNotification')
            ->willReturn(null);
        $this->dateTimeFactoryMock->expects($this->never())
            ->method('create');
        $this->assertFalse($this->canViewNotification->isVisible([]));
    }
}
