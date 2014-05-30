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
 * @package    Zend_Gdata
 * @subpackage App
 * @version    $Id: LoggingHttpClientAdapterSocket.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Http_Client_Adapter_Socket
 */
#require_once 'Zend/Http/Client/Adapter/Socket.php';

/**
 * Overrides the traditional socket-based adapter class for Zend_Http_Client to
 * enable logging of requests.  All requests are logged to a location specified
 * in the config as $config['logfile'].  Requests and responses are logged after
 * they are sent and received/processed, thus an error could prevent logging.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App_LoggingHttpClientAdapterSocket extends Zend_Http_Client_Adapter_Socket
{

    /**
     * The file handle for writing logs
     *
     * @var resource|null
     */
    protected $log_handle = null;

    /**
     * Log the given message to the log file.  The log file is configured
     * as the config param 'logfile'.  This method opens the file for
     * writing if necessary.
     *
     * @param string $message The message to log
     */
    protected function log($message)
    {
        if ($this->log_handle == null) {
            $this->log_handle = fopen($this->config['logfile'], 'a');
        }
        fwrite($this->log_handle, $message);
    }

    /**
     * Connect to the remote server
     *
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
     * @param int     $timeout
     */
    public function connect($host, $port = 80, $secure = false)
    {
        $this->log("Connecting to: ${host}:${port}");
        return parent::connect($host, $port, $secure);
    }

    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param Zend_Uri_Http $uri
     * @param string        $http_ver
     * @param array         $headers
     * @param string        $body
     * @return string Request as string
     */
    public function write($method, $uri, $http_ver = '1.1', $headers = array(), $body = '')
    {
        $request = parent::write($method, $uri, $http_ver, $headers, $body);
        $this->log("\n\n" . $request);
        return $request;
    }

    /**
     * Read response from server
     *
     * @return string
     */
    public function read()
    {
        $response = parent::read();
        $this->log("${response}\n\n");
        return $response;
    }

    /**
     * Close the connection to the server
     *
     */
    public function close()
    {
        $this->log("Closing socket\n\n");
        parent::close();
    }

}
