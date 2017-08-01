<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Messages collection
 *
 * @api
 * @since 2.0.0
 */
class Collection
{
    /**
     * All messages by type array
     *
     * @var MessageInterface[]
     * @since 2.0.0
     */
    protected $messages = [];

    /**
     * @var MessageInterface
     * @since 2.0.0
     */
    protected $lastAddedMessage;

    /**
     * Adding new message to collection
     *
     * @param MessageInterface $message
     * @return $this
     * @since 2.0.0
     */
    public function addMessage(MessageInterface $message)
    {
        if (!isset($this->messages[$message->getType()])) {
            $this->messages[$message->getType()] = [];
        }
        $this->messages[$message->getType()][] = $message;
        $this->lastAddedMessage = $message;
        return $this;
    }

    /**
     * Clear all messages except sticky
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @return MessageInterface[]
     * @since 2.0.0
     */
    public function getItems()
    {
        $result = [];
        foreach ($this->messages as $messages) {
            $result = array_merge($result, $messages);
        }

        return $result;
    }

    /**
     * Retrieve all messages by type
     *
     * @param string $type
     * @return MessageInterface[]
     * @since 2.0.0
     */
    public function getItemsByType($type)
    {
        return isset($this->messages[$type]) ? $this->messages[$type] : [];
    }

    /**
     * Retrieve all error messages
     *
     * @return MessageInterface[]
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
