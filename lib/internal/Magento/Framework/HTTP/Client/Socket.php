<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to work with HTTP protocol using sockets
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\HTTP\Client;

/**
 * Socket client
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
class Socket implements \Magento\Framework\HTTP\ClientInterface
{
    /**
     * Hostname
     * @var string
     */
    private $_host = 'localhost';

    /**
     * @var int
     */
    private $_port = 80;

    /**
     * Stream resource
     * @var object
     */
    private $_sock = null;

    /**
     * Request headers
     * @var array
     */
    private $_headers = [];

    /**
     * Fields for POST method - hash
     * @var array
     */
    private $_postFields = [];

    /**
     * Request cookies
     * @var array
     */
    private $_cookies = [];

    /**
     * @var array
     */
    private $_responseHeaders = [];

    /**
     * @var string
     */
    private $_responseBody = '';

    /**
     * @var int
     */
    private $_responseStatus = 0;

    /**
     * Request timeout
     * @var int
     */
    private $_timeout = 300;

    /**
     * TODO
     * @var int
     */
    private $_redirectCount = 0;

    /**
     * Set request timeout, msec
     *
     * @param int $value
     * @return void
     */
    public function setTimeout(int $value): void
    {
        $this->_timeout = (int)$value;
    }

    /**
     * Constructor
     *
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host = null, int $port = 80)
    {
        if ($host) {
            $this->connect($host, (int)$port);
        }
    }

    /**
     * Set connection params
     *
     * @param string $host
     * @param int $port
     * @return void
     */
    public function connect(string $host, int $port = 80): void
    {
        $this->_host = $host;
        $this->_port = (int)$port;
    }

    /**
     * Disconnect
     *
     * @return void
     */
    public function disconnect(): void
    {
        @fclose($this->_sock);
    }

    /**
     * Set headers from hash
     *
     * @param array $headers
     * @return void
     */
    public function setHeaders(array $headers): void
    {
        $this->_headers = $headers;
    }

    /**
     * Add header
     *
     * @param string $name name, ex. "Location"
     * @param string $value value ex. "http://google.com"
     * @return void
     */
    public function addHeader(string $name, string $value): void
    {
        $this->_headers[$name] = $value;
    }

    /**
     * Remove specified header
     *
     * @param string $name
     * @return void
     */
    public function removeHeader(string $name): void
    {
        unset($this->_headers[$name]);
    }

    /**
     * Authorization: Basic header
     *
     * Login credentials support
     *
     * @param string $login username
     * @param string $pass password
     * @return void
     */
    public function setCredentials(string $login, string $pass): void
    {
        $val = base64_encode("{$login}:{$pass}");
        $this->addHeader("Authorization", "Basic {$val}");
    }

    /**
     * Add cookie
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addCookie(string $name, string $value): void
    {
        $this->_cookies[$name] = $value;
    }

    /**
     * Remove cookie
     *
     * @param string $name
     * @return void
     */
    public function removeCookie(string $name): void
    {
        unset($this->_cookies[$name]);
    }

    /**
     * Set cookies array
     *
     * @param array $cookies
     * @return void
     */
    public function setCookies(array $cookies): void
    {
        $this->_cookies = $cookies;
    }

    /**
     * Clear cookies
     *
     * @return void
     */
    public function removeCookies(): void
    {
        $this->setCookies([]);
    }

    /**
     * Make GET request
     *
     * @param string $uri full uri path
     * @return void
     */
    public function get(string $uri): void
    {
        $this->makeRequest("GET", $this->parseUrl($uri));
    }

    /**
     * Make DELETE request
     *
     * @param string $uri full uri path
     * @return void
     */
    public function delete(string $uri): void
    {
        $this->makeRequest("DELETE", $this->parseUrl($uri));
    }

    /**
     * Set host, port from full url and return relative url
     *
     * @param string $uri ex. http://google.com/index.php?a=b
     * @return string ex. /index.php?a=b
     * @throws \InvalidArgumentException
     */
    protected function parseUrl(string $uri): string
    {
        $parts = parse_url($uri);
        if (!empty($parts['user']) && !empty($parts['pass'])) {
            $this->setCredentials($parts['user'], $parts['pass']);
        }
        if (!empty($parts['port'])) {
            $this->_port = (int)$parts['port'];
        }

        if (!empty($parts['host'])) {
            $this->_host = $parts['host'];
        } else {
            throw new \InvalidArgumentException("Uri doesn't contain host part");
        }

        if (!empty($parts['path'])) {
            $requestUri = $parts['path'];
        } else {
            throw new \InvalidArgumentException("Uri doesn't contain path part");
        }
        if (!empty($parts['query'])) {
            $requestUri .= "?" . $parts['query'];
        }
        return $requestUri;
    }

    /**
     * Make POST request
     *
     * @param string $uri
     * @param array|string $params use string in case of JSON or XML POST request
     * @return void
     */
    public function post(string $uri, array|string $params): void
    {
        $this->makeRequest("POST", $this->parseUrl($uri), $params);
    }

    /**
     * Make PATCH request
     *
     * @param string $uri
     * @param array|string $params use string in case of JSON or XML POST request
     * @return void
     */
    public function patch(string $uri, array|string $params): void
    {
        $this->makeRequest("PATCH", $this->parseUrl($uri), $params);
    }

    /**
     * Make PUT request
     *
     * @param string $uri
     * @param array|string $params use string in case of JSON or XML POST request
     * @return void
     */
    public function put(string $uri, array|string $params): void
    {
        $this->makeRequest("PUT", $this->parseUrl($uri), $params);
    }

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->_responseHeaders;
    }

    /**
     * Get response body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->_responseBody;
    }

    /**
     * Get cookies response hash
     *
     * @return array
     */
    public function getCookies(): array
    {
        if (empty($this->_responseHeaders['Set-Cookie'])) {
            return [];
        }
        $out = [];
        foreach ($this->_responseHeaders['Set-Cookie'] as $row) {
            $values = explode("; ", $row ?? '');
            $c = count($values);
            if (!$c) {
                continue;
            }
            list($key, $val) = explode("=", $values[0]);
            if ($val === null) {
                continue;
            }
            $out[trim($key)] = trim($val);
        }
        return $out;
    }

    /**
     * Get cookies array with details
     * (domain, expire time etc)
     *
     * @return array
     */
    public function getCookiesFull(): array
    {
        if (empty($this->_responseHeaders['Set-Cookie'])) {
            return [];
        }
        $out = [];
        foreach ($this->_responseHeaders['Set-Cookie'] as $row) {
            $values = explode("; ", $row ?? '');
            $c = count($values);
            if (!$c) {
                continue;
            }
            list($key, $val) = explode("=", $values[0]);
            if ($val === null) {
                continue;
            }
            $out[trim($key)] = ['value' => trim($val)];
            array_shift($values);
            $c--;
            if (!$c) {
                continue;
            }
            for ($i = 0; $i < $c; $i++) {
                list($subkey, $val) = explode("=", $values[$i]);
                $out[trim($key)][trim($subkey)] = $val !== null ? trim($val) : '';
            }
        }
        return $out;
    }

    /**
     * Process response headers
     *
     * @return void
     */
    protected function processResponseHeaders(): void
    {
        $crlf = "\r\n";
        $this->_responseHeaders = [];
        while (!feof($this->_sock)) {
            $line = fgets($this->_sock, 1024);
            if ($line === $crlf) {
                return;
            }
            $name = $value = '';
            $out = explode(": ", trim($line), 2);
            if (count($out) == 2) {
                $name = $out[0];
                $value = $out[1];
            }
            if (!empty($value)) {
                if ($name == "Set-Cookie") {
                    if (!isset($this->_responseHeaders[$name])) {
                        $this->_responseHeaders[$name] = [];
                    }
                    $this->_responseHeaders[$name][] = $value;
                } else {
                    $this->_responseHeaders[$name] = $value;
                }
            }
        }
    }

    /**
     * Process response body
     *
     * @return void
     */
    protected function processResponseBody(): void
    {
        $this->_responseBody = '';

        while (!feof($this->_sock)) {
            $this->_responseBody .= @fread($this->_sock, 1024);
        }
    }

    /**
     * Process response
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function processResponse(): void
    {
        $response = '';
        $responseLine = trim(fgets($this->_sock, 1024));

        $line = explode(" ", $responseLine, 3);
        if (count($line) != 3) {
            $this->doError("Invalid response line returned from server: " . $responseLine);
        }
        $this->_responseStatus = (int)$line[1];
        $this->processResponseHeaders();

        $this->processRedirect();

        $this->processResponseBody();
    }

    /**
     * Process redirect
     *
     * @return void
     */
    protected function processRedirect(): void // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
    {
        // TODO: implement redirects support
    }

    /**
     * Get response status code
     *
     * @see \Magento\Framework\HTTP\Client#getStatus()
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->_responseStatus;
    }

    /**
     * Make request
     *
     * @param string $method
     * @param string $uri
     * @param array|string $params use string in case of JSON or XML POST request
     * @return void
     * @throws \Exception
     */
    protected function makeRequest(string $method, string $uri, array|string $params = []): void
    {
        $errno = $errstr = '';
        $this->_sock = @fsockopen($this->_host, $this->_port, $errno, $errstr, $this->_timeout);
        if (!$this->_sock) {
            $this->doError(sprintf("[errno: %d] %s", $errno, $errstr));
        }

        $crlf = "\r\n";
        $isPost = $method == "POST";

        $appendHeaders = [];
        $paramsStr = false;
        if ($isPost && $params) {
            $paramsStr = is_array($params) ? http_build_query($params) : $params;
            $appendHeaders['Content-type'] = 'application/x-www-form-urlencoded';
            $appendHeaders['Content-length'] = strlen($paramsStr);
        }

        $out = "{$method} {$uri} HTTP/1.1{$crlf}";
        $out .= $this->headersToString($appendHeaders);
        $out .= $crlf;
        if ($paramsStr) {
            $out .= $paramsStr . $crlf;
        }

        fwrite($this->_sock, $out);
        $this->processResponse();
    }

    /**
     * Throw error exception
     *
     * @param string $string
     * @return void
     * @throws \Exception
     */
    public function doError(string $string): void
    {
        // phpcs:ignore Magento2.Exceptions.DirectThrow
        throw new \Exception($string);
    }

    /**
     * Convert headers hash to string
     *
     * @param array $append
     * @return string
     */
    protected function headersToString(array $append = []): string
    {
        $headers = [];
        $headers["Host"] = $this->_host;
        $headers['Connection'] = "close";
        $headers = array_merge($headers, $this->_headers, $append);
        $str = [];
        foreach ($headers as $k => $v) {
            $str[] = "{$k}: {$v}\r\n";
        }
        return implode($str);
    }

    /**
     * TODO
     *
     * @param array $arr
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setOptions(array $arr): void // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
    {
        // Stub
    }

    /**
     * TODO
     *
     * @param string $name
     * @param string $value
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setOption(string $name, string $value): void // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
    {
        // Stub
    }
}
