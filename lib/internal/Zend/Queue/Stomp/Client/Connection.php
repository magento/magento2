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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Connection.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Queue_Stomp_Client_ConnectionInterface
 */
#require_once 'Zend/Queue/Stomp/Client/ConnectionInterface.php';

/**
 * The Stomp client interacts with a Stomp server.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Stomp
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Stomp_Client_Connection
    implements Zend_Queue_Stomp_Client_ConnectionInterface
{
    const READ_TIMEOUT_DEFAULT_USEC = 0; // 0 microseconds
    const READ_TIMEOUT_DEFAULT_SEC = 5; // 5 seconds

    /**
     * Connection options
     * @var array
     */
    protected $_options;

    /**
     * tcp/udp socket
     *
     * @var resource
     */
    protected $_socket = false;

    /**
     * open() opens a socket to the Stomp server
     *
     * @param  array $options ('scheme', 'host', 'port')
     * @param  string $scheme
     * @param  string $host
     * @param  int $port
     * @param  array $options Accepts "timeout_sec" and "timeout_usec" keys
     * @return true;
     * @throws Zend_Queue_Exception
     */
    public function open($scheme, $host, $port, array $options = array())
    {
        $str = $scheme . '://' . $host;
        $this->_socket = fsockopen($str, $port, $errno, $errstr);

        if ($this->_socket === false) {
            // aparently there is some reason that fsockopen will return false
            // but it normally throws an error.
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Unable to connect to $str; error = $errstr ( errno = $errno )");
        }

        stream_set_blocking($this->_socket, 0); // non blocking

        if (!isset($options['timeout_sec'])) {
            $options['timeout_sec'] = self::READ_TIMEOUT_DEFAULT_SEC;
        }
        if (! isset($options['timeout_usec'])) {
            $options['timeout_usec'] = self::READ_TIMEOUT_DEFAULT_USEC;
        }

        $this->_options = $options;

        return true;
    }

    /**
     * Close the socket explicitly when destructed
     *
     * @return void
     */
    public function __destruct()
    {
    }

    /**
     * Close connection
     *
     * @param  boolean $destructor
     * @return void
     */
    public function close($destructor = false)
    {
        // Gracefully disconnect
        if (!$destructor) {
            $frame = $this->createFrame();
            $frame->setCommand('DISCONNECT');
            $this->write($frame);
        }

        // @todo: Should be fixed.
        // When the socket is "closed", it will trigger the below error when php exits
        // Fatal error: Exception thrown without a stack frame in Unknown on line 0

        // Danlo: I suspect this is because this has already been claimed by the interpeter
        // thus trying to shutdown this resources, which is already shutdown is a problem.
        if (is_resource($this->_socket)) {
            // fclose($this->_socket);
        }

        // $this->_socket = null;
    }

    /**
     * Check whether we are connected to the server
     *
     * @return true
     * @throws Zend_Queue_Exception
     */
    public function ping()
    {
        if (!is_resource($this->_socket)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Not connected to Stomp server');
        }
        return true;
    }

    /**
     * Write a frame to the stomp server
     *
     * example: $response = $client->write($frame)->read();
     *
     * @param Zend_Queue_Stom_FrameInterface $frame
     * @return $this
     */
    public function write(Zend_Queue_Stomp_FrameInterface $frame)
    {
        $this->ping();
        $output = $frame->toFrame();

        $bytes = fwrite($this->_socket, $output, strlen($output));
        if ($bytes === false || $bytes == 0) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('No bytes written');
        }

        return $this;
    }

    /**
     * Tests the socket to see if there is data for us
     *
     * @return boolean
     */
    public function canRead()
    {
        $read   = array($this->_socket);
        $write  = null;
        $except = null;

        return stream_select(
            $read,
            $write,
            $except,
            $this->_options['timeout_sec'],
            $this->_options['timeout_usec']
        ) == 1;
        // see http://us.php.net/manual/en/function.stream-select.php
    }

    /**
     * Reads in a frame from the socket or returns false.
     *
     * @return Zend_Queue_Stomp_FrameInterface|false
     * @throws Zend_Queue_Exception
     */
    public function read()
    {
        $this->ping();

        $response = '';
        $prev     = '';

        // while not end of file.
        while (!feof($this->_socket)) {
            // read in one character until "\0\n" is found
            $data = fread($this->_socket, 1);

            // check to make sure that the connection is not lost.
            if ($data === false) {
                #require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception('Connection lost');
            }

            // append last character read to $response
            $response .= $data;

            // is this \0 (prev) \n (data)? END_OF_FRAME
            if (ord($data) == 10 && ord($prev) == 0) {
                break;
            }
            $prev = $data;
        }

        if ($response === '') {
            return false;
        }

        $frame = $this->createFrame();
        $frame->fromFrame($response);
        return $frame;
    }

    /**
     * Set the frameClass to be used
     *
     * This must be a Zend_Queue_Stomp_FrameInterface.
     *
     * @param  string $classname - class is an instance of Zend_Queue_Stomp_FrameInterface
     * @return $this;
     */
    public function setFrameClass($classname)
    {
        $this->_options['frameClass'] = $classname;
        return $this;
    }

    /**
     * Get the frameClass
     *
     * @return string
     */
    public function getFrameClass()
    {
        return isset($this->_options['frameClass'])
            ? $this->_options['frameClass']
            : 'Zend_Queue_Stomp_Frame';
    }

    /**
     * Create an empty frame
     *
     * @return Zend_Queue_Stomp_FrameInterface
     */
    public function createFrame()
    {
        $class = $this->getFrameClass();

        if (!class_exists($class)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);
        }

        $frame = new $class();

        if (!$frame instanceof Zend_Queue_Stomp_FrameInterface) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Invalid Frame class provided; must implement Zend_Queue_Stomp_FrameInterface');
        }

        return $frame;
    }
}
