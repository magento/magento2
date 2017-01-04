<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Framework\FlagFactory;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Flag;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NotificationTimeTest
 */
class NotificationTimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FlagFactory
     */
    private $flagFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FlagResource
     */
    private $flagResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Flag
     */
    private $flagMock;

    /**
     * @var NotificationTime
     */
    private $notificationTime;

    public function setUp()
    {
        $this->flagFactoryMock = $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagResourceMock = $this->getMockBuilder(FlagResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->notificationTime = $objectManagerHelper->getObject(
            NotificationTime::class,
            [
                'flagFactory' => $this->flagFactoryMock,
                'flagResource' => $this->flagResourceMock
            ]
        );
    }

    public function testStoreLastTimeNotification()
    {
        $this->flagFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'data' => [
                        'flag_code' => NotificationTime::NOTIFICATION_TIME
                    ]
                ]
            )->willReturn($this->flagMock);
        $this->flagMock->expects($this->once())
            ->method('setFlagData')
            ->with(100500)
            ->willReturnSelf();
        $this->flagResourceMock->expects($this->once())
            ->method('save')
            ->with($this->flagMock)
            ->willReturnSelf();
        $this->assertTrue($this->notificationTime->storeLastTimeNotification(100500));
    }

    public function testGetLastTimeNotification()
    {
        $this->flagFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->flagMock);
        $this->flagResourceMock->expects($this->once())
            ->method('load')
            ->with($this->flagMock, NotificationTime::NOTIFICATION_TIME)
            ->willReturn($this->flagMock);
        $this->flagMock->expects($this->once())
            ->method('getFlagData')
            ->willReturn(100500);
        $this->assertEquals(100500, $this->notificationTime->getLastTimeNotification());
    }
}
