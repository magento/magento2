<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\AdminNotification\Block\ToolbarEntry
 */
namespace Magento\AdminNotification\Block;

class ToolbarEntryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Retrieve toolbar entry block instance
     *
     * @param int $unreadNotifications number of unread notifications
     * @return \Magento\AdminNotification\Block\ToolbarEntry
     */
    protected function _getBlockInstance($unreadNotifications)
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        // mock collection of unread notifications
        $notificationList = $this->getMock(
            'Magento\AdminNotification\Model\Resource\Inbox\Collection\Unread',
            ['getSize', 'setCurPage', 'setPageSize'],
            [],
            '',
            false
        );
        $notificationList->expects($this->any())->method('getSize')->will($this->returnValue($unreadNotifications));

        $block = $objectManagerHelper->getObject(
            'Magento\AdminNotification\Block\ToolbarEntry',
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
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        // 1. Create mocks
        $notificationList = $this->getMockBuilder('Magento\AdminNotification\Model\Resource\Inbox\Collection\Unread')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\AdminNotification\Block\ToolbarEntry $model */
        $model = $helper->getObject('Magento\AdminNotification\Block\ToolbarEntry',
            ['notificationList' => $notificationList]
        );

        // 2. Set expectations
        $notificationList->expects($this->atLeastOnce())
            ->method('setPageSize')
            ->with(\Magento\AdminNotification\Block\ToolbarEntry::NOTIFICATIONS_NUMBER)
            ->will($this->returnSelf());

        // 3. Run tested method
        $result = $model->getLatestUnreadNotifications();

        // 4. Compare actual result with expected result
        $this->assertEquals($notificationList, $result);
    }
}
