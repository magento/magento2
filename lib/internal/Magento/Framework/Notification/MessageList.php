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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Notification;

/**
 * Class for processing the list of system messages
 *
 * Class MessageList
 */
class MessageList
{
    /**
     * List of configured message classes
     *
     * @var array
     */
    protected $_messageClasses;

    /**
     * List of messages
     *
     * @var array
     */
    protected $_messages;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param array $messages
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager, $messages = array())
    {
        $this->_objectManager = $objectManager;
        $this->_messageClasses = $messages;
    }

    /**
     * Load messages to display
     *
     * @return void
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function _loadMessages()
    {
        if (!empty($this->_messages)) {
            return;
        }
        foreach ($this->_messageClasses as $key => $messageClass) {
            if (!$messageClass) {
                throw new \InvalidArgumentException('Message class for message "' . $key . '" is not set');
            }
            $message = $this->_objectManager->get($messageClass);
            if ($message instanceof \Magento\Framework\Notification\MessageInterface) {
                $this->_messages[$message->getIdentity()] = $message;
            } else {
                throw new \UnexpectedValueException("Message class has to implement the message interface.");
            }

        }
    }

    /**
     * Retrieve message by
     *
     * @param string $identity
     * @return null|\Magento\Framework\Notification\MessageInterface
     */
    public function getMessageByIdentity($identity)
    {
        $this->_loadMessages();
        return isset($this->_messages[$identity]) ? $this->_messages[$identity] : null;
    }

    /**
     * Retrieve list of all messages
     *
     * @return \Magento\Framework\Notification\MessageInterface[]
     */
    public function asArray()
    {
        $this->_loadMessages();
        return $this->_messages;
    }
}
