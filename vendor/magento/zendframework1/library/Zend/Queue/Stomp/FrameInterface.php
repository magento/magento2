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
 * This class represents a Stomp Frame Interface
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Stomp
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Queue_Stomp_FrameInterface
{
    /**
     * Get the status of the auto content length
     *
     * If AutoContentLength is true this code will automatically put the
     * content-length header in, even if it is already set by the user.
     *
     * This is done to make the message sending more reliable.
     *
     * @return boolean
     */
    public function getAutoContentLength();

    /**
     * setAutoContentLength()
     *
     * Set the value on or off.
     *
     * @param boolean $auto
     * @return $this;
     * @throws Zend_Queue_Exception
     */
    public function setAutoContentLength($auto);

    /**
     * Get the headers
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Set the headers
     *
     * Throws an exception if the array values are not strings.
     *
     * @param array $headers
     * @return $this
     * @throws Zend_Queue_Exception
     */
    public function setHeaders(array $headers);

    /**
     * Returns a value for a header
     * returns false if the header does not exist
     *
     * @param string $header
     * @return $string
     * @throws Zend_Queue_Exception
     */
    public function getHeader($header);

    /**
     * Returns a value for a header
     * returns false if the header does not exist
     *
     * @param string $header
     * @param string $value
     * @return $this
     * @throws Zend_Queue_Exception
     */
    public function setHeader($header, $value);

    /**
     * Return the body for this frame
     * returns false if the body does not exist
     *
     * @return $this
     */
    public function getBody();

    /**
     * Set the body for this frame
     * returns false if the body does not exist
     *
     * Set to null for no body.
     *
     * @param string|null $body
     * @return $this
     * @throws Zend_Queue_Exception
     */
    public function setBody($body);

    /**
     * Return the command for this frame
     * return false if the command does not exist
     *
     * @return $this
     */
    public function getCommand();

    /**
     * Set the body for this frame
     * returns false if the body does not exist
     *
     * @return $this
     * @throws Zend_Queue_Exception
     */
    public function setCommand($command);


    /**
     * Takes the current parameters and returns a Stomp Frame
     *
     * @throws Zend_Queue_Exception
     * @return string
     */
    public function toFrame();

    /**
     * @see toFrame()
     */
    public function __toString();

    /**
     * Accepts a frame and deconstructs the frame into its' component parts
     *
     * @param string $frame - a stomp frame
     * @return $this
     */
    public function fromFrame($frame);
}
