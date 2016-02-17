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
 * @package    Zend_Queue
 * @subpackage Stomp
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * The Stomp client interacts with a Stomp server.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Stomp
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Queue_Stomp_Client_ConnectionInterface
{
    /**
     * @param  string  $scheme ['tcp', 'udp']
     * @param  string  host
     * @param  integer port
     * @param  string  class - create a connection with this class; class must support Zend_Queue_Stomp_Client_Connection_Interface
     * @return boolean
     */
    public function open($scheme, $host, $port);

    /**
     * @param  boolean $destructor
     * @return void
     */
    public function close($destructor = false);

    /**
     * Check whether we are connected to the server
     *
     * @return true
     * @throws Zend_Queue_Exception
     */
    public function ping();

    /**
     * write a frame to the stomp server
     *
     * example: $response = $client->write($frame)->read();
     *
     * @param  Zend_Queue_Stomp_FrameInterface $frame
     * @return $this
     */
    public function write(Zend_Queue_Stomp_FrameInterface $frame);

    /**
     * tests the socket to see if there is data for us
     */
    public function canRead();

    /**
     * reads in a frame from the socket or returns false.
     *
     * @return Zend_Queue_Stomp_Frame|false
     * @throws Zend_Queue_Exception
     */
    public function read();

    /**
     * Set the frame class to be used
     *
     * This must be a Zend_Queue_Stomp_FrameInterface.
     *
     * @param  string $class
     * @return Zend_Queue_Stomp_Client_ConnectionInterface;
     */
    public function setFrameClass($class);

    /**
     * Get the frameClass
     *
     * @return string
     */
    public function getFrameClass();

    /**
     * create an empty frame
     *
     * @return Zend_Queue_Stomp_FrameInterface class
     */
    public function createFrame();
}
