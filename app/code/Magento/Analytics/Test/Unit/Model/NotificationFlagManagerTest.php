<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\NotificationFlagManager;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
        $objectManager = new ObjectManager($this);
        $this->notificationFlagManager = $objectManager->getObject(
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
            ->with('analytics_notification_seen_admin_' . $userId, 1)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlagManager->setNotifiedUser($userId));
    }

    public function testIsUserNotified()
    {
        $userId = 1;
        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with('analytics_notification_seen_admin_' . $userId)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlagManager->isUserNotified($userId));
    }
}
