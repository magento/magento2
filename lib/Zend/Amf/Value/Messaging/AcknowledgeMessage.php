<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: AcknowledgeMessage.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Amf_Value_Messaging_AsyncMessage */
#require_once 'Zend/Amf/Value/Messaging/AsyncMessage.php';

/**
 * This is the type of message returned by the MessageBroker
 * to endpoints after the broker has routed an endpoint's message
 * to a service.
 *
 * flex.messaging.messages.AcknowledgeMessage
 *
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Value_Messaging_AcknowledgeMessage extends Zend_Amf_Value_Messaging_AsyncMessage
{
    /**
     * Create a new Acknowledge Message
     *
     * @param unknown_type $message
     */
    public function __construct($message)
    {
        $this->clientId    = $this->generateId();
        $this->destination = null;
        $this->messageId   = $this->generateId();
        $this->timestamp   = time().'00';
        $this->timeToLive  = 0;
        $this->headers     = new STDClass();
        $this->body        = null;

        // correleate the two messages
        if ($message && isset($message->messageId)) {
            $this->correlationId = $message->messageId;
        }
    }
}
