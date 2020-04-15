<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\AdminNotification\Block\ToolbarEntry
 */
namespace Magento\AdminNotification\Test\Unit\Block;

use Magento\AdminNotification\Block\ToolbarEntry;
use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Unread;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ToolbarEntryTest extends TestCase
{
    /**
     * Retrieve toolbar entry block instance
     *
     * @param int $unreadNotifications number of unread notifications
     * @return ToolbarEntry
     */
    protected function _getBlockInstance($unreadNotifications)
    {
        $objectManagerHelper = new ObjectManager($this);
        // mock collection of unread notifications
        $notificationList = $this->createPartialMock(
            Unread::class,
            ['getSize', 'setCurPage', 'setPageSize']
        );
        $notificationList->expects($this->any())->method('getSize')->will($this->returnValue($unreadNotifications));

        $block = $objectManagerHelper->getObject(
            ToolbarEntry::class,
            ['notificationList' => $notificationList]
        );

        return $block;
    }

    public function testGetUnreadNotificationCount()
    {
        $notificationsCount = 100;
        $block = $this->_getBlockInstance($notificationsCount);
        $this->assertEquals($notificationsCount, $block->getUnreadNotificationCount());
    }

    public function testGetLatestUnreadNotifications()
    {
        $helper = new ObjectManager($this);

        // 1. Create mocks
        $notificationList = $this->getMockBuilder(Unread::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ToolbarEntry $model */
        $model = $helper->getObject(
            ToolbarEntry::class,
            ['notificationList' => $notificationList]
        );

        // 2. Set expectations
        $notificationList->expects($this->atLeastOnce())
            ->method('setPageSize')
            ->with(ToolbarEntry::NOTIFICATIONS_NUMBER)
            ->will($this->returnSelf());

        // 3. Run tested method
        $result = $model->getLatestUnreadNotifications();

        // 4. Compare actual result with expected result
        $this->assertEquals($notificationList, $result);
    }
}
