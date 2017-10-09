<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\NotificationFlagManager;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NotificationFlagManagerTest
 */
class NotificationFlagManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var NotificationFlagManager
     */
    private $notificationFlagManager;

    public function setUp()
    {
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->notificationFlagManager = $objectManagerHelper->getObject(
            NotificationFlagManager::class,
            [
                'flagManager' => $this->flagManagerMock
            ]
        );
    }

    public function testSetNotifiedUser()
    {
        $userId = 1;
        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with(NotificationFlagManager::NOTIFICATION_SEEN . $userId, 1)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlagManager->setNotifiedUser($userId));
    }

    public function testIsUserNotified()
    {
        $userId = 1;
        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with(NotificationFlagManager::NOTIFICATION_SEEN . $userId)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlagManager->isUserNotified($userId));
    }
}
