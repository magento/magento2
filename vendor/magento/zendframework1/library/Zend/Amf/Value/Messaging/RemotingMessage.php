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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Amf_Value_Messaging_AbstractMessage */
#require_once 'Zend/Amf/Value/Messaging/AbstractMessage.php';

/**
 * This type of message contains information needed to perform
 * a Remoting invocation.
 *
 * Corresponds to flex.messaging.messages.RemotingMessage
 *
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Value_Messaging_RemotingMessage extends Zend_Amf_Value_Messaging_AbstractMessage
{

    /**
     * The name of the service to be called including package name
     * @var String
     */
    public $source;

    /**
     * The name of the method to be called
     * @var string
     */
    public $operation;

    /**
     * The arguments to call the mathod with
     * @var array
     */
    public $parameters;

    /**
     * Create a new Remoting Message
     *
     * @return void
     */
    public function __construct()
    {
        $this->clientId    = $this->generateId();
        $this->destination = null;
        $this->messageId   = $this->generateId();
        $this->timestamp   = time().'00';
        $this->timeToLive  = 0;
        $this->headers     = new stdClass();
        $this->body        = null;
    }
}
