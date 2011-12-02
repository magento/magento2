<?php
/**
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
 * @package    Zend_Cloud
 * @subpackage QueueService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/QueueService/Adapter.php';
#require_once 'Zend/Cloud/QueueService/Message.php';
#require_once 'Zend/Cloud/QueueService/MessageSet.php';

/**
 * Abstract queue adapter
 *
 * Provides functionality around setting message and message set classes.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage QueueService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Cloud_QueueService_Adapter_AbstractAdapter
    implements Zend_Cloud_QueueService_Adapter
{
    /**@+ option keys */
    const MESSAGE_CLASS    = 'message_class';
    const MESSAGESET_CLASS = 'messageset_class';
    /**@-*/

    /** @var string Class to use for queue messages */
    protected $_messageClass    = 'Zend_Cloud_QueueService_Message';

    /** @var string Class to use for collections of queue messages */
    protected $_messageSetClass = 'Zend_Cloud_QueueService_MessageSet';

    /**
     * Set class to use for message objects
     * 
     * @param  string $class 
     * @return Zend_Cloud_QueueService_Adapter_AbstractAdapter
     */
    public function setMessageClass($class)
    {
        $this->_messageClass = (string) $class;
        return $this;
    }

    /**
     * Get class to use for message objects
     * 
     * @return string
     */
    public function getMessageClass()
    {
        return $this->_messageClass;
    }

    /**
     * Set class to use for message collection objects
     * 
     * @param  string $class 
     * @return Zend_Cloud_QueueService_Adapter_AbstractAdapter
     */
    public function setMessageSetClass($class)
    {
        $this->_messageSetClass = (string) $class;
        return $this;
    }

    /**
     * Get class to use for message collection objects
     * 
     * @return string
     */
    public function getMessageSetClass()
    {
        return $this->_messageSetClass;
    }
}
