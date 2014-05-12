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
namespace Magento\AdminNotification\Model;

/**
 * Notification service model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class NotificationService
{
    /**
     * @var \Magento\AdminNotification\Model\InboxFactory $notificationFactory
     */
    protected $_notificationFactory;

    /**
     * @param \Magento\AdminNotification\Model\InboxFactory $notificationFactory
     */
    public function __construct(\Magento\AdminNotification\Model\InboxFactory $notificationFactory)
    {
        $this->_notificationFactory = $notificationFactory;
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function markAsRead($notificationId)
    {
        $notification = $this->_notificationFactory->create();
        $notification->load($notificationId);
        if (!$notification->getId()) {
            throw new \Magento\Framework\Model\Exception('Wrong notification ID specified.');
        }
        $notification->setIsRead(1);
        $notification->save();
    }
}
