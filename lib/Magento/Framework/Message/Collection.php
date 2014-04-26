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
namespace Magento\Framework\Message;

/**
 * Messages collection
 */
class Collection
{
    /**
     * All messages by type array
     *
     * @var array
     */
    protected $messages = array();

    /**
     * @var MessageInterface
     */
    protected $lastAddedMessage;

    /**
     * Adding new message to collection
     *
     * @param MessageInterface $message
     * @return $this
     */
    public function addMessage(MessageInterface $message)
    {
        if (!isset($this->messages[$message->getType()])) {
            $this->messages[$message->getType()] = array();
        }
        $this->messages[$message->getType()][] = $message;
        $this->lastAddedMessage = $message;
        return $this;
    }

    /**
     * Clear all messages except sticky
     *
     * @return $this
     */
    public function clear()
    {
        foreach ($this->messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                /** @var $message MessageInterface */
                if (!$message->getIsSticky()) {
                    unset($this->messages[$type][$id]);
                }
            }
            if (empty($this->messages[$type])) {
                unset($this->messages[$type]);
            }
        }
        if ($this->lastAddedMessage instanceof MessageInterface && !$this->lastAddedMessage->getIsSticky()) {
            $this->lastAddedMessage = null;
        }
        return $this;
    }

    /**
     * Get last added message if any
     *
     * @return MessageInterface|null
     */
    public function getLastAddedMessage()
    {
        return $this->lastAddedMessage;
    }

    /**
     * Get first even message by identifier
     *
     * @param string $identifier
     * @return MessageInterface|void
     */
    public function getMessageByIdentifier($identifier)
    {
        foreach ($this->messages as $messages) {
            foreach ($messages as $message) {
                /** @var $message MessageInterface */
                if ($identifier === $message->getIdentifier()) {
                    return $message;
                }
            }
        }
    }

    /**
     * Delete message by id
     *
     * @param string $identifier
     * @return void
     */
    public function deleteMessageByIdentifier($identifier)
    {
        foreach ($this->messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                /** @var $message MessageInterface */
                if ($identifier === $message->getIdentifier()) {
                    unset($this->messages[$type][$id]);
                }
                if (empty($this->messages[$type])) {
                    unset($this->messages[$type]);
                }
            }
        }
    }

    /**
     * Retrieve messages collection items
     *
     * @return array
     */
    public function getItems()
    {
        $result = array();
        foreach ($this->messages as $messages) {
            $result = array_merge($result, $messages);
        }

        return $result;
    }

    /**
     * Retrieve all messages by type
     *
     * @param string $type
     * @return array
     */
    public function getItemsByType($type)
    {
        return isset($this->messages[$type]) ? $this->messages[$type] : array();
    }

    /**
     * Retrieve all error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->getItemsByType(MessageInterface::TYPE_ERROR);
    }

    /**
     * Retrieve messages count by type
     *
     * @param string $type
     * @return int
     */
    public function getCountByType($type)
    {
        $result = 0;
        if (isset($this->messages[$type])) {
            $result = count($this->messages[$type]);
        }
        return $result;
    }

    /**
     * Retrieve messages count
     *
     * @return int
     */
    public function getCount()
    {
        $result = 0;
        foreach ($this->messages as $messages) {
            $result += count($messages);
        }
        return $result;
    }
}
