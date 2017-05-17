<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\ResourceModel\System\Message\Collection;

class Synchronized extends \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection
{
    /**
     * Unread message list
     *
     * @var \Magento\Framework\Notification\MessageInterface[]
     */
    protected $_unreadMessages = [];

    /**
     * Store new messages in database and remove outdated messages
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function _afterLoad()
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
        if (count($removed) || count($unread)) {
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
