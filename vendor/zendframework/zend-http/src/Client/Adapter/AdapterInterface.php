<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Client\Adapter;

/**
 * An interface description for Zend\Http\Client\Adapter classes.
 *
 * These classes are used as connectors for Zend\Http\Client, performing the
 * tasks of connecting, writing, reading and closing connection to the server.
 */
interface AdapterInterface
{
    /**
     * Set the configuration array for the adapter
     *
     * @param array $options
     */
    public function setOptions($options = array());

    /**
     * Connect to the remote server
     *
     * @param string  $host
     * @param int     $port
     * @param  bool $secure
     */
    public function connect($host, $port = 80, $secure = false);

    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param \Zend\Uri\Uri $url
     * @param string        $httpVer
     * @param array         $headers
     * @param string        $body
     * @return string Request as text
     */
    public function write($method, $url, $httpVer = '1.1', $headers = array(), $body = '');

    /**
     * Read response from server
     *
     * @return string
     */
    public function read();

    /**
     * Close the connection to the server
     *
     */
    public function close();
}
