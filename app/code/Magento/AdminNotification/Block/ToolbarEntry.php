<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        array $data = array()
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
