<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\ResourceModel\System\Message\Collection;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection;

/**
 * Class Synchronized
 *
 * @package Magento\AdminNotification\Model\ResourceModel\System\Message\Collection
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Synchronized extends Collection
{
    /**
     * Unread message list
     *
     * @var \Magento\Framework\Notification\MessageInterface[]
     */
    protected $_unreadMessages = []; //phpcs:ignore

    /**
     * Store new messages in database and remove outdated messages
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function _afterLoad() //phpcs:ignore
    {
        $messages = $this->_messageList->asArray();
        $persisted = [];
        $unread = [];
        foreach ($messages as $message) {
            if ($message->isDisplayed()) {
                foreach ($this->_items as $persistedKey => $persistedMessage) {
                    if ($message->getIdentity() == $persistedMessage->getIdentity()) {
                        $persisted[$persistedKey] = $persistedMessage;
                        continue 2;
                    }
                }
                $unread[] = $message;
            }
        }
        $removed = array_diff_key($this->_items, $persisted);
        foreach ($removed as $removedItem) {
            $removedItem->delete();
        }
        foreach ($unread as $unreadItem) {
            $item = $this->getNewEmptyItem();
            $item->setIdentity($unreadItem->getIdentity())->setSeverity($unreadItem->getSeverity())->save();
        }
        if (!empty($removed) || !empty($unread)) {
            $this->_unreadMessages = $unread;
            $this->clear();
            $this->load();
        } else {
            parent::_afterLoad();
        }
        return $this;
    }

    /**
     * Retrieve list of unread messages
     *
     * @return \Magento\Framework\Notification\MessageInterface[]
     */
    public function getUnread()
    {
        return $this->_unreadMessages;
    }
}
