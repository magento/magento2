<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Advertisement\Test\Unit\Model\Condition;

use Magento\Advertisement\Model\Condition\CanViewNotification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Advertisement\Model\AdvertisementFlagManager;
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
     * @var AdvertisementFlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $advertisementFlagManagerMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    public function setUp()
    {
        $this->advertisementFlagManagerMock = $this->getMockBuilder(AdvertisementFlagManager::class)
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
                'advertisementFlagManager' => $this->advertisementFlagManagerMock,
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
        $this->advertisementFlagManagerMock->expects($this->once())
            ->method('isUserNotified')
            ->willReturn($isUserNotified);
        $this->advertisementFlagManagerMock->expects($this->any())
            ->method('setNotifiedUser')
            ->willReturn(true);

        $this->assertEquals($expected, $this->canViewNotification->isVisible([]));
    }
}
