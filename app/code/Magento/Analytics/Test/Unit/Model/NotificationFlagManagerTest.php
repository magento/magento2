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
 * Class NotificationFlagTest
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
        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with(NotificationFlagManager::NOTIFICATION_SEEN . $this->userId, 1)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlagManager->setNotifiedUser($this->userId));
    }

    public function testIsUserNotified()
    {
        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with(NotificationFlagManager::NOTIFICATION_SEEN . $this->userId)
            ->willReturn(true);
        $this->assertTrue($this->notificationFlagManager->isUserNotified($this->userId));
    }
}
