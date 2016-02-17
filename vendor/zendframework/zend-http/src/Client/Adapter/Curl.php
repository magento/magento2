<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Client\Adapter;

use Traversable;
use Zend\Http\Client\Adapter\AdapterInterface as HttpAdapter;
use Zend\Http\Client\Adapter\Exception as AdapterException;
use Zend\Stdlib\ArrayUtils;

/**
 * An adapter class for Zend\Http\Client based on the curl extension.
 * Curl requires libcurl. See for full requirements the PHP manual: http://php.net/curl
 */
class Curl implements HttpAdapter, StreamInterface
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = array();

    /**
     * What host/port are we connected to?
     *
     * @var array
     */
    protected $connectedTo = array(null, null);

    /**
     * The curl session handle
     *
     * @var resource|null
     */
    protected $curl = null;

    /**
     * List of cURL options that should never be overwritten
     *
     * @var array
     */
    protected $invalidOverwritableCurlOptions;

    /**
     * Response gotten from server
     *
     * @var string
     */
    protected $response = null;

    /**
     * Stream for storing output
     *
     * @var resource
     */
    protected $outputStream;

    /**
     * Adapter constructor
     *
     * Config is set using setOptions()
     *
     * @throws AdapterException\InitializationException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new AdapterException\InitializationException(
                'cURL extension has to be loaded to use this Zend\Http\Client adapter'
            );
        }
        $this->invalidOverwritableCurlOptions = array(
            CURLOPT_HTTPGET,
            CURLOPT_POST,
            CURLOPT_UPLOAD,
            CURLOPT_CUSTOMREQUEST,
            CURLOPT_HEADER,
            CURLOPT_RETURNTRANSFER,
            CURLOPT_HTTPHEADER,
            CURLOPT_INFILE,
            CURLOPT_INFILESIZE,
            CURLOPT_PORT,
            CURLOPT_MAXREDIRS,
            CURLOPT_CONNECTTIMEOUT,
        );
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param  array|Traversable $options
     * @return Curl
     * @throws AdapterException\InvalidArgumentException
     */
    public function setOptions($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (!is_array($options)) {
            throw new AdapterException\InvalidArgumentException(
                'Array or Traversable object expected, got ' . gettype($options)
            );
        }

        /** Config Key Normalization */
        foreach ($options as $k => $v) {
            unset($options[$k]); // unset original value
            $options[str_replace(array('-', '_', ' ', '.'), '', strtolower($k))] = $v; // replace w/ normalized
        }

        if (isset($options['proxyuser']) && isset($options['proxypass'])) {
            $this->setCurlOption(CURLOPT_PROXYUSERPWD, $options['proxyuser'] . ":" . $options['proxypass']);
            unset($options['proxyuser'], $options['proxypass']);
        }

        if (isset($options['sslverifypeer'])) {
            $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $options['sslverifypeer']);
            unset($options['sslverifypeer']);
        }

        foreach ($options as $k => $v) {
            $option = strtolower($k);
            switch ($option) {
                case 'proxyhost':
                    $this->setCurlOption(CURLOPT_PROXY, $v);
                    break;
                case 'proxyport':
                    $this->setCurlOption(CURLOPT_PROXYPORT, $v);
                    break;
                default:
                    if (is_array($v) && isset($this->config[$option]) && is_array($this->config[$option])) {
                        $v = ArrayUtils::merge($this->config[$option], $v);
                    }
                    $this->config[$option] = $v;
                    break;
            }
        }

        return $this;
    }

    /**
     * Retrieve the array of all configuration options
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Direct setter for cURL adapter related options.
     *
     * @param  string|int $option
     * @param  mixed $value
     * @return Curl
     */
    public function setCurlOption($option, $value)
    {
        if (!isset($this->config['curloptions'])) {
            $this->config['curloptions'] = array();
        }
        $this->config['curloptions'][$option] = $value;
        return $this;
    }

    /**
     * Initialize curl
     *
     * @param  string  $host
     * @param  int     $port
     * @param  bool $secure
     * @return void
     * @throws AdapterException\RuntimeException if unable to connect
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If we're already connected, disconnect first
        if ($this->curl) {
            $this->close();
        }

        // Do the actual connection
        $this->curl = curl_init();
        if ($port != 80) {
            curl_setopt($this->curl, CURLOPT_PORT, intval($port));
        }

        if (isset($this->config['timeout'])) {
            if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {
                curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT_MS, $this->config['timeout'] * 1000);
            } else {
                curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->config['timeout']);
            }

            if (defined('CURLOPT_TIMEOUT_MS')) {
                curl_setopt($this->curl, CURLOPT_TIMEOUT_MS, $this->config['timeout'] * 1000);
            } else {
                curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->config['timeout']);
            }
        }

        if (isset($this->config['maxredirects'])) {
            // Set Max redirects
            curl_setopt($this->curl, CURLOPT_MAXREDIRS, $this->config['maxredirects']);
        }

        if (!$this->curl) {
            $this->close();

            throw new AdapterException\RuntimeException('Unable to Connect to ' . $host . ':' . $port);
        }

        if ($secure !== false) {
            // Behave the same like Zend\Http\Adapter\Socket on SSL options.
            if (isset($this->config['sslcert'])) {
                curl_setopt($this->curl, CURLOPT_SSLCERT, $this->config['sslcert']);
            }
            if (isset($this->config['sslpassphrase'])) {
                curl_setopt($this->curl, CURLOPT_SSLCERTPASSWD, $this->config['sslpassphrase']);
            }
        }

        // Update connected_to
        $this->connectedTo = array($host, $port);
    }

    /**
     * Send request to the remote server
     *
     * @param  string        $method
     * @param  \Zend\Uri\Uri $uri
     * @param  float         $httpVersion
     * @param  array         $headers
     * @param  string        $body
     * @return string        $request
     * @throws AdapterException\RuntimeException If connection fails, connected
     *     to wrong host, no PUT file defined, unsupported method, or unsupported
     *     cURL option.
     * @throws AdapterException\InvalidArgumentException if $method is currently not supported
     */
    public function write($method, $uri, $httpVersion = 1.1, $headers = array(), $body = '')
    {
        // Make sure we're properly connected
        if (!$this->curl) {
            throw new AdapterException\RuntimeException("Trying to write but we are not connected");
        }

        if ($this->connectedTo[0] != $uri->getHost() || $this->connectedTo[1] != $uri->getPort()) {
            throw new AdapterException\RuntimeException("Trying to write but we are connected to the wrong host");
        }

        // set URL
        curl_setopt($this->curl, CURLOPT_URL, $uri->__toString());

        // ensure correct curl call
        $curlValue = true;
        switch ($method) {
            case 'GET':
                $curlMethod = CURLOPT_HTTPGET;
                break;

            case 'POST':
                $curlMethod = CURLOPT_POST;
                break;

            case 'PUT':
                // There are two different types of PUT request, either a Raw Data string has been set
                // or CURLOPT_INFILE and CURLOPT_INFILESIZE are used.
                if (is_resource($body)) {
                    $this->config['curloptions'][CURLOPT_INFILE] = $body;
                }
                if (isset($this->config['curloptions'][CURLOPT_INFILE])) {
                    // Now we will probably already have Content-Length set, so that we have to delete it
                    // from $headers at this point:
                    if (!isset($headers['Content-Length'])
                        && !isset($this->config['curloptions'][CURLOPT_INFILESIZE])
                    ) {
                        throw new AdapterException\RuntimeException(
                            'Cannot set a file-handle for cURL option CURLOPT_INFILE'
                            . ' without also setting its size in CURLOPT_INFILESIZE.'
                        );
                    }

                    if (isset($headers['Content-Length'])) {
                        $this->config['curloptions'][CURLOPT_INFILESIZE] = (int) $headers['Content-Length'];
                        unset($headers['Content-Length']);
                    }

                    if (is_resource($body)) {
                        $body = '';
                    }

                    $curlMethod = CURLOPT_UPLOAD;
                } else {
                    $curlMethod = CURLOPT_CUSTOMREQUEST;
                    $curlValue = "PUT";
                }
                break;

            case 'PATCH':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "PATCH";
                break;

            case 'DELETE':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "DELETE";
                break;

            case 'OPTIONS':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "OPTIONS";
                break;

            case 'TRACE':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "TRACE";
                break;

            case 'HEAD':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "HEAD";
                break;

            default:
                // For now, through an exception for unsupported request methods
                throw new AdapterException\InvalidArgumentException("Method '$method' currently not supported");
        }

        if (is_resource($body) && $curlMethod != CURLOPT_UPLOAD) {
            throw new AdapterException\RuntimeException("Streaming requests are allowed only with PUT");
        }

        // get http version to use
        $curlHttp = ($httpVersion == 1.1) ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0;

        // mark as HTTP request and set HTTP method
        curl_setopt($this->curl, CURLOPT_HTTP_VERSION, $curlHttp);
        curl_setopt($this->curl, $curlMethod, $curlValue);

        if ($this->outputStream) {
            // headers will be read into the response
            curl_setopt($this->curl, CURLOPT_HEADER, false);
            curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, "readHeader"));
            // and data will be written into the file
            curl_setopt($this->curl, CURLOPT_FILE, $this->outputStream);
        } else {
            // ensure headers are also returned
            curl_setopt($this->curl, CURLOPT_HEADER, true);

            // ensure actual response is returned
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        }

        // Treating basic auth headers in a special way
        if (array_key_exists('Authorization', $headers) && 'Basic' == substr($headers['Authorization'], 0, 5)) {
            curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->curl, CURLOPT_USERPWD, base64_decode(substr($headers['Authorization'], 6)));
            unset($headers['Authorization']);
        }

        // set additional headers
        if (!isset($headers['Accept'])) {
            $headers['Accept'] = '';
        }
        $curlHeaders = array();
        foreach ($headers as $key => $value) {
            $curlHeaders[] = $key . ': ' . $value;
        }

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $curlHeaders);

        /**
         * Make sure POSTFIELDS is set after $curlMethod is set:
         * @link http://de2.php.net/manual/en/function.curl-setopt.php#81161
         */
        if (in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'), true)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($curlMethod == CURLOPT_UPLOAD) {
            // this covers a PUT by file-handle:
            // Make the setting of this options explicit (rather than setting it through the loop following a bit lower)
            // to group common functionality together.
            curl_setopt($this->curl, CURLOPT_INFILE, $this->config['curloptions'][CURLOPT_INFILE]);
            curl_setopt($this->curl, CURLOPT_INFILESIZE, $this->config['curloptions'][CURLOPT_INFILESIZE]);
            unset($this->config['curloptions'][CURLOPT_INFILE]);
            unset($this->config['curloptions'][CURLOPT_INFILESIZE]);
        }

        // set additional curl options
        if (isset($this->config['curloptions'])) {
            foreach ((array) $this->config['curloptions'] as $k => $v) {
                if (!in_array($k, $this->invalidOverwritableCurlOptions)) {
                    if (curl_setopt($this->curl, $k, $v) == false) {
                        throw new AdapterException\RuntimeException(sprintf(
                            'Unknown or erroreous cURL option "%s" set',
                            $k
                        ));
                    }
                }
            }
        }

        // send the request

        $response = curl_exec($this->curl);
        // if we used streaming, headers are already there
        if (!is_resource($this->outputStream)) {
            $this->response = $response;
        }

        $request  = curl_getinfo($this->curl, CURLINFO_HEADER_OUT);
        $request .= $body;

        if (empty($this->response)) {
            throw new AdapterException\RuntimeException("Error in cURL request: " . curl_error($this->curl));
        }

        // separating header from body because it is dangerous to accidentially replace strings in the body
        $responseHeaderSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $responseHeaders = substr($this->response, 0, $responseHeaderSize);

        // cURL automatically decodes chunked-messages, this means we have to
        // disallow the Zend\Http\Response to do it again.
        $responseHeaders = preg_replace("/Transfer-Encoding:\s*chunked\\r\\n/", "", $responseHeaders);

        // cURL can automatically handle content encoding; prevent double-decoding from occurring
        if (isset($this->config['curloptions'][CURLOPT_ENCODING])
            && '' == $this->config['curloptions'][CURLOPT_ENCODING]
        ) {
            $responseHeaders = preg_replace("/Content-Encoding:\s*gzip\\r\\n/", '', $responseHeaders);
        }

        // cURL automatically handles Proxy rewrites, remove the "HTTP/1.0 200 Connection established" string:
        $responseHeaders = preg_replace(
            "/HTTP\/1.0\s*200\s*Connection\s*established\\r\\n\\r\\n/",
            '',
            $responseHeaders
        );

        // replace old header with new, cleaned up, header
        $this->response = substr_replace($this->response, $responseHeaders, 0, $responseHeaderSize);

        // Eliminate multiple HTTP responses.
        do {
            $parts = preg_split('|(?:\r?\n){2}|m', $this->response, 2);
            $again = false;

            if (isset($parts[1]) && preg_match("|^HTTP/1\.[01](.*?)\r\n|mi", $parts[1])) {
                $this->response = $parts[1];
                $again          = true;
            }
        } while ($again);

        return $request;
    }

    /**
     * Return read response from server
     *
     * @return string
     */
    public function read()
    {
        return $this->response;
    }

    /**
     * Close the connection to the server
     *
     */
    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
        $this->curl         = null;
        $this->connectedTo = array(null, null);
    }

    /**
     * Get cUrl Handle
     *
     * @return resource
     */
    public function getHandle()
    {
        return $this->curl;
    }

    /**
     * Set output stream for the response
     *
     * @param resource $stream
     * @return Curl
     */
    public function setOutputStream($stream)
    {
        $this->outputStream = $stream;
        return $this;
    }

    /**
     * Header reader function for CURL
     *
     * @param resource $curl
     * @param string $header
     * @return int
     */
    public function readHeader($curl, $header)
    {
        $this->response .= $header;
        return strlen($header);
    }
}
