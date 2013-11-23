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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Message;

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
     * @var string
     */
    protected $lastAddedMessage;

    /**
     * Adding new message to collection
     *
     * @param AbstractMessage $message
     * @return Collection
     */
    public function add(AbstractMessage $message)
    {
        return $this->addMessage($message);
    }

    /**
     * Adding new message to collection
     *
     * @param AbstractMessage $message
     * @return Collection
     */
    public function addMessage(AbstractMessage $message)
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
     * @return Collection
     */
    public function clear()
    {
        foreach ($this->messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                /** @var $message AbstractMessage */
                if (!$message->getIsSticky()) {
                    unset($this->messages[$type][$id]);
                }
            }
            if (empty($this->messages[$type])) {
                unset($this->messages[$type]);
            }
        }
        return $this;
    }

    /**
     * Get last added message if any
     *
     * @return AbstractMessage|null
     */
    public function getLastAddedMessage()
    {
        return $this->lastAddedMessage;
    }

    /**
     * Get first even message by identifier
     *
     * @param string $identifier
     * @return AbstractMessage|null
     */
    public function getMessageByIdentifier($identifier)
    {
        foreach ($this->messages as $messages) {
            foreach ($messages as $message) {
                /** @var $message AbstractMessage */
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
     */
    public function deleteMessageByIdentifier($identifier)
    {
        foreach ($this->messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                /** @var $message AbstractMessage */
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
     * @param string $type
     * @return array
     */
    public function getItems($type = null)
    {
        if ($type) {
            return isset($this->messages[$type]) ? $this->messages[$type] : array();
        }

        $arrRes = array();
        foreach ($this->messages as $messages) {
            $arrRes = array_merge($arrRes, $messages);
        }

        return $arrRes;
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
        return $this->getItemsByType(Factory::ERROR);
    }

    /**
     * @return string
     */
    public function toString()
    {
        $out = '';
        $arrItems = $this->getItems();
        foreach ($arrItems as $item) {
            $out .= $item->toString();
        }

        return $out;
    }

    /**
     * Retrieve messages count
     *
     * @param null|string $type
     * @return int
     */
    public function count($type = null)
    {
        if ($type) {
            if (isset($this->messages[$type])) {
                return count($this->messages[$type]);
            }
            return 0;
        }
        return count($this->messages);
    }
}
