<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Client\Adapter;

use Zend\Http\Client;
use Zend\Http\Client\Adapter\Exception as AdapterException;
use Zend\Http\Response;
use Zend\Stdlib\ErrorHandler;

/**
 * HTTP Proxy-supporting Zend\Http\Client adapter class, based on the default
 * socket based adapter.
 *
 * Should be used if proxy HTTP access is required. If no proxy is set, will
 * fall back to Zend\Http\Client\Adapter\Socket behavior. Just like the
 * default Socket adapter, this adapter does not require any special extensions
 * installed.
 */
class Proxy extends Socket
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = array(
        'ssltransport'       => 'ssl',
        'sslcert'            => null,
        'sslpassphrase'      => null,
        'sslverifypeer'      => true,
        'sslcapath'          => null,
        'sslallowselfsigned' => false,
        'sslusecontext'      => false,
        'proxy_host'         => '',
        'proxy_port'         => 8080,
        'proxy_user'         => '',
        'proxy_pass'         => '',
        'proxy_auth'         => Client::AUTH_BASIC,
        'persistent'         => false
    );

    /**
     * Whether HTTPS CONNECT was already negotiated with the proxy or not
     *
     * @var bool
     */
    protected $negotiated = false;

    /**
     * Set the configuration array for the adapter
     *
     * @param array $options
     */
    public function setOptions($options = array())
    {
        //enforcing that the proxy keys are set in the form proxy_*
        foreach ($options as $k => $v) {
            if (preg_match("/^proxy[a-z]+/", $k)) {
                $options['proxy_' . substr($k, 5, strlen($k))] = $v;
                unset($options[$k]);
            }
        }

        parent::setOptions($options);
    }

    /**
     * Connect to the remote server
     *
     * Will try to connect to the proxy server. If no proxy was set, will
     * fall back to the target server (behave like regular Socket adapter)
     *
     * @param string  $host
     * @param int     $port
     * @param  bool $secure
     * @throws AdapterException\RuntimeException
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If no proxy is set, fall back to Socket adapter
        if (! $this->config['proxy_host']) {
            parent::connect($host, $port, $secure);
            return;
        }

        /* Url might require stream context even if proxy connection doesn't */
        if ($secure) {
            $this->config['sslusecontext'] = true;
        }

        // Connect (a non-secure connection) to the proxy server
        parent::connect(
            $this->config['proxy_host'],
            $this->config['proxy_port'],
            false
        );
    }

    /**
     * Send request to the proxy server
     *
     * @param string        $method
     * @param \Zend\Uri\Uri $uri
     * @param string        $httpVer
     * @param array         $headers
     * @param string        $body
     * @throws AdapterException\RuntimeException
     * @return string Request as string
     */
    public function write($method, $uri, $httpVer = '1.1', $headers = array(), $body = '')
    {
        // If no proxy is set, fall back to default Socket adapter
        if (! $this->config['proxy_host']) {
            return parent::write($method, $uri, $httpVer, $headers, $body);
        }

        // Make sure we're properly connected
        if (! $this->socket) {
            throw new AdapterException\RuntimeException("Trying to write but we are not connected");
        }

        $host = $this->config['proxy_host'];
        $port = $this->config['proxy_port'];

        if ($this->connectedTo[0] != "tcp://$host" || $this->connectedTo[1] != $port) {
            throw new AdapterException\RuntimeException("Trying to write but we are connected to the wrong proxy server");
        }

        // Add Proxy-Authorization header
        if ($this->config['proxy_user'] && ! isset($headers['proxy-authorization'])) {
            $headers['proxy-authorization'] = Client::encodeAuthHeader(
                $this->config['proxy_user'], $this->config['proxy_pass'], $this->config['proxy_auth']
            );
        }

        // if we are proxying HTTPS, preform CONNECT handshake with the proxy
        if ($uri->getScheme() == 'https' && (! $this->negotiated)) {
            $this->connectHandshake($uri->getHost(), $uri->getPort(), $httpVer, $headers);
            $this->negotiated = true;
        }

        // Save request method for later
        $this->method = $method;

        // Build request headers
        if ($this->negotiated) {
            $path = $uri->getPath();
            if ($uri->getQuery()) {
                $path .= '?' . $uri->getQuery();
            }
            $request = "$method $path HTTP/$httpVer\r\n";
        } else {
            $request = "$method $uri HTTP/$httpVer\r\n";
        }

        // Add all headers to the request string
        foreach ($headers as $k => $v) {
            if (is_string($k)) {
                $v = "$k: $v";
            }
            $request .= "$v\r\n";
        }

        if (is_resource($body)) {
            $request .= "\r\n";
        } else {
            // Add the request body
            $request .= "\r\n" . $body;
        }

        // Send the request
        ErrorHandler::start();
        $test  = fwrite($this->socket, $request);
        $error = ErrorHandler::stop();
        if (!$test) {
            throw new AdapterException\RuntimeException("Error writing request to proxy server", 0, $error);
        }

        if (is_resource($body)) {
            if (stream_copy_to_stream($body, $this->socket) == 0) {
                throw new AdapterException\RuntimeException('Error writing request to server');
            }
        }

        return $request;
    }

    /**
     * Preform handshaking with HTTPS proxy using CONNECT method
     *
     * @param string  $host
     * @param int $port
     * @param string  $httpVer
     * @param array   $headers
     * @throws AdapterException\RuntimeException
     */
    protected function connectHandshake($host, $port = 443, $httpVer = '1.1', array &$headers = array())
    {
        $request = "CONNECT $host:$port HTTP/$httpVer\r\n" .
                   "Host: " . $host . "\r\n";

        // Add the user-agent header
        if (isset($this->config['useragent'])) {
            $request .= "User-agent: " . $this->config['useragent'] . "\r\n";
        }

        // If the proxy-authorization header is set, send it to proxy but remove
        // it from headers sent to target host
        if (isset($headers['proxy-authorization'])) {
            $request .= "Proxy-authorization: " . $headers['proxy-authorization'] . "\r\n";
            unset($headers['proxy-authorization']);
        }

        $request .= "\r\n";

        // Send the request
        ErrorHandler::start();
        $test  = fwrite($this->socket, $request);
        $error = ErrorHandler::stop();
        if (!$test) {
            throw new AdapterException\RuntimeException("Error writing request to proxy server", 0, $error);
        }

        // Read response headers only
        $response = '';
        $gotStatus = false;
        ErrorHandler::start();
        while ($line = fgets($this->socket)) {
            $gotStatus = $gotStatus || (strpos($line, 'HTTP') !== false);
            if ($gotStatus) {
                $response .= $line;
                if (!rtrim($line)) {
                    break;
                }
            }
        }
        ErrorHandler::stop();

        // Check that the response from the proxy is 200
        if (Response::extractCode($response) != 200) {
            throw new AdapterException\RuntimeException("Unable to connect to HTTPS proxy. Server response: " . $response);
        }

        // If all is good, switch socket to secure mode. We have to fall back
        // through the different modes
        $modes = array(
            STREAM_CRYPTO_METHOD_TLS_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv23_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv2_CLIENT
        );

        $success = false;
        foreach ($modes as $mode) {
            $success = stream_socket_enable_crypto($this->socket, true, $mode);
            if ($success) {
                break;
            }
        }

        if (! $success) {
            throw new AdapterException\RuntimeException("Unable to connect to" .
                    " HTTPS server through proxy: could not negotiate secure connection.");
        }
    }

    /**
     * Close the connection to the server
     *
     */
    public function close()
    {
        parent::close();
        $this->negotiated = false;
    }

    /**
     * Destructor: make sure the socket is disconnected
     *
     */
    public function __destruct()
    {
        if ($this->socket) {
            $this->close();
        }
    }
}
