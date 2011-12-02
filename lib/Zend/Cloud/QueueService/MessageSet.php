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

/**
 * Collection of message objects
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage QueueService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_QueueService_MessageSet implements Countable, IteratorAggregate
{
    /** @var int */
    protected $_messageCount;

    /** @var ArrayAccess Messages */
    protected $_messages;

    /**
     * Constructor
     * 
     * @param  array $messages 
     * @return void
     */
    public function __construct(array $messages)
    {
        $this->_messageCount = count($messages);
        $this->_messages     = new ArrayIterator($messages);
    }

    /**
     * Countable: number of messages in collection
     * 
     * @return int
     */
    public function count()
    {
        return $this->_messageCount;
    }

    /**
     * IteratorAggregate: return iterable object
     * 
     * @return Traversable
     */
    public function getIterator()
    {
        return $this->_messages;
    }
}
