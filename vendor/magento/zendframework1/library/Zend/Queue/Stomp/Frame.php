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
 * @see Zend_Queue_Stomp_FrameInterface
 */
#require_once 'Zend/Queue/Stomp/FrameInterface.php';

/**
 * This class represents a Stomp Frame
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Stomp
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Stomp_Frame
    implements Zend_Queue_Stomp_FrameInterface
{
    const END_OF_FRAME   = "\x00\n";
    const CONTENT_LENGTH = 'content-length';
    const EOL            = "\n";

    /**
     * Headers for the frame
     *
     * @var array
     */
    protected $_headers = array();

    /**
     * The command for the frame
     *
     * @var string
     */
    protected $_command = null;

    /**
     * The body of the frame
     *
     * @var string
     */
    protected $_body = null;

    /**
     * Do the content-length automatically?
     */
    protected $_autoContentLength = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setHeaders(array());
        $this->setBody(null);
        $this->setCommand(null);
        $this->setAutoContentLength(true);
    }

    /**
     * get the status of the auto content length
     *
     * If AutoContentLength is true this code will automatically put the
     * content-length header in, even if it is already set by the user.
     *
     * This is done to make the message sending more reliable.
     *
     * @return boolean
     */
    public function getAutoContentLength()
    {
        return $this->_autoContentLength;
    }

    /**
     * setAutoContentLength()
     *
     * Set the value on or off.
     *
     * @param boolean $auto
     * @return $this;
     * @throws Zend_Queue_Exception
     */
    public function setAutoContentLength($auto)
    {
        if (!is_bool($auto)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$auto is not a boolean');
        }

        $this->_autoContentLength = $auto;
        return $this;
    }

    /**
     * Get the headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Set the headers
     *
     * Throws an exception if the array values are not strings.
     *
     * @param array $headers
     * @return $this
     * @throws Zend_Queue_Exception
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }

        return $this;
    }

    /**
     * Sets a value for a header
     *
     * @param  string $header
     * @param  string $value
     * @return Zend_Queue_Stomp_Frame
     * @throws Zend_Queue_Exception
     */
    public function setHeader($header, $value) {
        if (!is_string($header)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$header is not a string: ' . print_r($header, true));
        }

        if (!is_scalar($value)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$value is not a string: ' . print_r($value, true));
        }

        $this->_headers[$header] = $value;
        return $this;
    }


    /**
     * Returns a value for a header
     *
     * Returns false if the header does not exist.
     *
     * @param  string $header
     * @return string|false
     * @throws Zend_Queue_Exception
     */
    public function getHeader($header)
    {
        if (!is_string($header)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$header is not a string');
        }

        return isset($this->_headers[$header])
            ? $this->_headers[$header]
            : false;
    }

    /**
     * Return the body for this frame
     *
     * Returns false if the body does not exist
     *
     * @return false|string
     */
    public function getBody()
    {
        return $this->_body === null
            ? false
            : $this->_body;
    }

    /**
     * Set the body for this frame
     *
     * Set to null for no body.
     *
     * @param  string|null $body
     * @return Zend_Queue_Stomp_Frame
     * @throws Zend_Queue_Exception
     */
    public function setBody($body)
    {
        if (!is_string($body) && $body !== null) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$body is not a string or null');
        }

        $this->_body = $body;
        return $this;
    }

    /**
     * Return the command for this frame
     *
     * Return false if the command does not exist
     *
     * @return string|false
     */
    public function getCommand()
    {
        return $this->_command === null
            ? false
            : $this->_command;
    }

    /**
     * Set the body for this frame
     *
     * @param  string|null
     * @return Zend_Queue_Stomp_Frame
     * @throws Zend_Queue_Exception
     */
    public function setCommand($command)
    {
        if (!is_string($command) && $command !== null) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$command is not a string or null');
        }

        $this->_command = $command;
        return $this;
    }

    /**
     * Takes the current parameters and returns a Stomp Frame
     *
     * @return string
     * @throws Zend_Queue_Exception
     */
    public function toFrame()
    {
        if ($this->getCommand() === false) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('You must set the command');
        }

        $command = $this->getCommand();
        $headers = $this->getHeaders();
        $body    = $this->getBody();
        $frame   = '';

        // add a content-length to the SEND command.
        // @see http://stomp.codehaus.org/Protocol
        if ($this->getAutoContentLength()) {
            $headers[self::CONTENT_LENGTH] = strlen($this->getBody());
        }

        // Command
        $frame = $command . self::EOL;

        // Headers
        foreach ($headers as $key=>$value) {
            $frame .= $key . ': ' . $value . self::EOL;
        }

        // Seperator
        $frame .= self::EOL; // blank line required by protocol

        // add the body if any
        if ($body !== false) {
            $frame .= $body;
        }
        $frame .= self::END_OF_FRAME;

        return $frame;
    }

    /**
     * @see toFrame()
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->toFrame();
        } catch (Zend_Queue_Exception $e) {
            $return = '';
        }
        return $return;
    }

    /**
     * Accepts a frame and deconstructs the frame into its component parts
     *
     * @param  string $frame - a stomp frame
     * @return $this
     */
    public function fromFrame($frame)
    {
        if (!is_string($frame)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$frame is not a string');
        }

        $headers = array();
        $body    = null;
        $command = false;
        $header  = '';

        // separate the headers and the body
        $match = self::EOL . self::EOL;
        if (preg_match('/' . $match . '/', $frame)) {
            list ($header, $body) = explode($match, $frame, 2);
        } else {
            $header = $frame;
        }

        // blow up headers
        $headers = explode(self::EOL, $header);
        unset($header);

        // get the command (first line)
        $this->setCommand(array_shift($headers));

        // set each of the headers.
        foreach ($headers as $header) {
            if (strpos($header, ':') > 0) {
                list($name, $value) = explode(':', $header, 2);
                $this->setHeader($name, $value);
            }
        }

        // crop the body if content-length is present
        if ($this->getHeader(self::CONTENT_LENGTH) !== false ) {
            $length = (int) $this->getHeader(self::CONTENT_LENGTH);
            $body   = substr($body, 0, $length);
        }

        $this->setBody($body);
        return $this;
    }
}
