<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Advertisement\Test\Unit\Model;

use Magento\Advertisement\Model\AdvertisementFlagManager;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class AdvertisementFlagManagerTest
 */
class AdvertisementFlagManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var AdvertisementFlagManager
     */
    private $advertisementFlagManager;

    public function setUp()
    {
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->advertisementFlagManager = $objectManager->getObject(
            AdvertisementFlagManager::class,
            [
                'flagManager' => $this->flagManagerMock
            ]
        );
    }

    public function testSetNotifiedUser()
    {
        $userId = 1;
        $this->flagManagerMock->expects($this->once())
            ->method('saveFlag')
            ->with('advertisement_notification_seen_admin_' . $userId, 1)
            ->willReturn(true);
        $this->assertTrue($this->advertisementFlagManager->setNotifiedUser($userId));
    }

    public function testIsUserNotified()
    {
        $userId = 1;
        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with('advertisement_notification_seen_admin_' . $userId)
            ->willReturn(true);
        $this->assertTrue($this->advertisementFlagManager->isUserNotified($userId));
    }
}
