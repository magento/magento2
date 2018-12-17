<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Block;

use DateTime;
use IntlDateFormatter;
use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;
use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Unread;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Toolbar entry that shows latest notifications
 *
 * @package Magento\AdminNotification\Block
 * @api
 * @author Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class ToolbarEntry extends Template
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
     * @var Collection
     */
    protected $_notificationList; //phpcs:ignore

    /**
     * @param Context $context
     * @param Unread $notificationList
     * @param array $data
     */
    public function __construct(
        Context $context,
        Unread $notificationList,
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
    public function getNotificationDescriptionLength(): int
    {
        return self::NOTIFICATION_DESCRIPTION_LENGTH;
    }

    /**
     * Retrieve notification counter max value
     *
     * @return int
     */
    public function getNotificationCounterMax(): int
    {
        return self::NOTIFICATIONS_COUNTER_MAX;
    }

    /**
     * Retrieve number of unread notifications
     *
     * @return int
     */
    public function getUnreadNotificationCount(): int
    {
        return $this->_notificationList->getSize();
    }

    /**
     * Retrieve the list of latest unread notifications
     *
     * @return Collection
     */
    public function getLatestUnreadNotifications(): Collection
    {
        return $this->_notificationList->setPageSize(self::NOTIFICATIONS_NUMBER);
    }

    /**
     * Format notification date (show only time if notification has been added today)
     *
     * @param string $dateString
     * @return string
     * @throws \Exception
     * @todo use \Magento\Framework\Stdlib\DateTime\TimezoneInterface instead of DateTime
     */
    public function formatNotificationDate($dateString): string
    {
        $date = new DateTime($dateString);
        if ($date == new DateTime('today')) {
            return $this->_localeDate->formatDateTime(
                $date,
                IntlDateFormatter::NONE,
                IntlDateFormatter::SHORT
            );
        }
        return $this->_localeDate->formatDateTime(
            $date,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::MEDIUM
        );
    }
}
