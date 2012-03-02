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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Messages collection
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Message_Collection
{
    /**
     * All messages by type array
     *
     * @var array
     */
    protected $_messages = array();
    protected $_lastAddedMessage;

    /**
     * Adding new message to collection
     *
     * @param   Mage_Core_Model_Message_Abstract $message
     * @return  Mage_Core_Model_Message_Collection
     */
    public function add(Mage_Core_Model_Message_Abstract $message)
    {
        return $this->addMessage($message);
    }

    /**
     * Adding new message to collection
     *
     * @param   Mage_Core_Model_Message_Abstract $message
     * @return  Mage_Core_Model_Message_Collection
     */
    public function addMessage(Mage_Core_Model_Message_Abstract $message)
    {
        if (!isset($this->_messages[$message->getType()])) {
            $this->_messages[$message->getType()] = array();
        }
        $this->_messages[$message->getType()][] = $message;
        $this->_lastAddedMessage = $message;
        return $this;
    }

    /**
     * Clear all messages except sticky
     *
     * @return Mage_Core_Model_Message_Collection
     */
    public function clear()
    {
        foreach ($this->_messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                if (!$message->getIsSticky()) {
                    unset($this->_messages[$type][$id]);
                }
            }
            if (empty($this->_messages[$type])) {
                unset($this->_messages[$type]);
            }
        }
        return $this;
    }

    /**
     * Get last added message if any
     *
     * @return Mage_Core_Model_Message_Abstract|null
     */
    public function getLastAddedMessage()
    {
        return $this->_lastAddedMessage;
    }

    /**
     * Get first even message by identifier
     *
     * @param string $identifier
     * @return Mage_Core_Model_Message_Abstract|null
     */
    public function getMessageByIdentifier($identifier)
    {
        foreach ($this->_messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                if ($identifier === $message->getIdentifier()) {
                    return $message;
                }
            }
        }
    }

    public function deleteMessageByIdentifier($identifier)
    {
        foreach ($this->_messages as $type => $messages) {
            foreach ($messages as $id => $message) {
                if ($identifier === $message->getIdentifier()) {
                    unset($this->_messages[$type][$id]);
                }
                if (empty($this->_messages[$type])) {
                    unset($this->_messages[$type]);
                }
            }
        }
    }

    /**
     * Retrieve messages collection items
     *
     * @param   string $type
     * @return  array
     */
    public function getItems($type=null)
    {
        if ($type) {
            return isset($this->_messages[$type]) ? $this->_messages[$type] : array();
        }

        $arrRes = array();
        foreach ($this->_messages as $messageType => $messages) {
            $arrRes = array_merge($arrRes, $messages);
        }

        return $arrRes;
    }

    /**
     * Retrieve all messages by type
     *
     * @param   string $type
     * @return  array
     */
    public function getItemsByType($type)
    {
        return isset($this->_messages[$type]) ? $this->_messages[$type] : array();
    }

    /**
     * Retrieve all error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->getItemsByType(Mage_Core_Model_Message::ERROR);
    }

    public function toString()
    {
        $out = '';
        $arrItems = $this->getItems();
        foreach ($arrItems as $item) {
            $out.= $item->toString();
        }

        return $out;
    }

    /**
     * Retrieve messages count
     *
     * @return int
     */
    public function count($type=null)
    {
        if ($type) {
            if (isset($this->_messages[$type])) {
                return count($this->_messages[$type]);
            }
            return 0;
        }
        return count($this->_messages);
    }
}
