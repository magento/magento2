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
 * @category    Mage
 * @package     Mage_AdminNotification
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Notification service model
 *
 * @category    Mage
 * @package     Mage_AdminNotification
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_AdminNotification_Model_NotificationService
{
    /**
     * @var Mage_AdminNotification_Model_InboxFactory $notificationFactory
     */
    protected $_notificationFactory;

    /**
     * @param Mage_AdminNotification_Model_InboxFactory $notificationFactory
     */
    public function __construct(
        Mage_AdminNotification_Model_InboxFactory $notificationFactory
    ) {
        $this->_notificationFactory = $notificationFactory;
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @throws Mage_Core_Exception
     */
    public function markAsRead($notificationId)
    {
        $notification = $this->_notificationFactory->create();
        $notification->load($notificationId);
        if (!$notification->getId()) {
            throw new Mage_Core_Exception('Wrong notification ID specified.');
        }
        $notification->setIsRead(1);
        $notification->save();
    }
}
