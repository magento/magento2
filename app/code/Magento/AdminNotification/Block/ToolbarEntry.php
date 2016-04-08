<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

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
    const NOTIFICATIONS_NUMBER = 3;

    /**
     * Number of notifications showed on icon
     */
    const NOTIFICATIONS_COUNTER_MAX = 99;

    /**
     * Length of notification description showed by default
     */
    const NOTIFICATION_DESCRIPTION_LENGTH = 150;

    /**
     * Collection of latest unread notifications
     *
     * @var \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
     */
    protected $_notificationList;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Unread $notificationList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Unread $notificationList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_notificationList = $notificationList;
    }

    /**
     * Retrieve notification description start length
     *
     * @return int
     */
    public function getNotificationDescriptionLength()
    {
        return self::NOTIFICATION_DESCRIPTION_LENGTH;
    }

    /**
     * Retrieve notification counter max value
     *
     * @return int
     */
    public function getNotificationCounterMax()
    {
        return self::NOTIFICATIONS_COUNTER_MAX;
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
     * @return \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
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
        $date = new \DateTime($dateString);
        if ($date == new \DateTime('today')) {
            return $this->_localeDate->formatDateTime(
                $date,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::SHORT
            );
        }
        return $this->_localeDate->formatDateTime(
            $date,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
    }
}
