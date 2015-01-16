<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block;

/**
 * Toolbar entry that shows latest notifications
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class ToolbarEntry extends \Magento\Backend\Block\Template
{
    /**
     * Number of notifications showed on expandable window
     */
    const NOTIFICATIONS_NUMBER = 4;

    /**
     * Collection of latest unread notifications
     *
     * @var \Magento\AdminNotification\Model\Resource\Inbox\Collection
     */
    protected $_notificationList;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\AdminNotification\Model\Resource\Inbox\Collection\Unread $notificationList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\AdminNotification\Model\Resource\Inbox\Collection\Unread $notificationList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_notificationList = $notificationList;
    }

    /**
     * Retrieve number of unread notifications
     *
     * @return int
     */
    public function getUnreadNotificationCount()
    {
        return $this->_notificationList->getSize();
    }

    /**
     * Retrieve the list of latest unread notifications
     *
     * @return \Magento\AdminNotification\Model\Resource\Inbox\Collection
     */
    public function getLatestUnreadNotifications()
    {
        return $this->_notificationList->setPageSize(self::NOTIFICATIONS_NUMBER);
    }

    /**
     * Format notification date (show only time if notification has been added today)
     *
     * @param string $dateString
     * @return string
     */
    public function formatNotificationDate($dateString)
    {
        if (date('Ymd') == date('Ymd', strtotime($dateString))) {
            return $this->formatTime(
                $dateString,
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT,
                false
            );
        }
        return $this->formatDate($dateString, \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM, true);
    }
}
