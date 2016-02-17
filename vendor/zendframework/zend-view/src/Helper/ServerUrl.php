<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

/**
 * Helper for returning the current server URL (optionally with request URI)
 */
class ServerUrl extends AbstractHelper
{
    /**
     * Host (including port)
     *
     * @var string
     */
    protected $host;

    /**
     * Port
     *
     * @var int
     */
    protected $port;

    /**
     * Scheme
     *
     * @var string
     */
    protected $scheme;

    /**
     * Whether or not to query proxy servers for address
     *
     * @var bool
     */
    protected $useProxy = false;

    /**
     * View helper entry point:
     * Returns the current host's URL like http://site.com
     *
     * @param  string|bool $requestUri  [optional] if true, the request URI
     *                                     found in $_SERVER will be appended
     *                                     as a path. If a string is given, it
     *                                     will be appended as a path. Default
     *                                     is to not append any path.
     * @return string
     */
    public function __invoke($requestUri = null)
    {
        if ($requestUri === true) {
            $path = $_SERVER['REQUEST_URI'];
        } elseif (is_string($requestUri)) {
            $path = $requestUri;
        } else {
            $path = '';
        }

        return $this->getScheme() . '://' . $this->getHost() . $path;
    }

    /**
     * Detect the host based on headers
     *
     * @return void
     */
    protected function detectHost()
    {
        if ($this->setHostFromProxy()) {
            return;
        }

        if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
            // Detect if the port is set in SERVER_PORT and included in HTTP_HOST
            if (isset($_SERVER['SERVER_PORT'])
                && preg_match('/^(?P<host>.*?):(?P<port>\d+)$/', $_SERVER['HTTP_HOST'], $matches)
            ) {
                // If they are the same, set the host to just the hostname
                // portion of the Host header.
                if ((int) $matches['port'] === (int) $_SERVER['SERVER_PORT']) {
                    $this->setHost($matches['host']);
                    return;
                }

                // At this point, we have a SERVER_PORT that differs from the
                // Host header, indicating we likely have a port-forwarding
                // situation. As such, we'll set the host and port from the
                // matched values.
                $this->setPort((int) $matches['port']);
                $this->setHost($matches['host']);
                return;
            }

            $this->setHost($_SERVER['HTTP_HOST']);

            return;
        }

        if (!isset($_SERVER['SERVER_NAME']) || !isset($_SERVER['SERVER_PORT'])) {
            return;
        }

        $name = $_SERVER['SERVER_NAME'];
        $this->setHost($name);
    }

    /**
     * Detect the port
     *
     * @return null
     */
    protected function detectPort()
    {
        if ($this->setPortFromProxy()) {
            return;
        }

        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']) {
            if ($this->isReversedProxy()) {
                $this->setPort(443);
                return;
            }
            $this->setPort($_SERVER['SERVER_PORT']);
            return;
        }
    }

    /**
     * Detect the scheme
     *
     * @return null
     */
    protected function detectScheme()
    {
        if ($this->setSchemeFromProxy()) {
            return;
        }

        switch (true) {
            case (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)):
            case (isset($_SERVER['HTTP_SCHEME']) && ($_SERVER['HTTP_SCHEME'] == 'https')):
            case (443 === $this->getPort()):
            case $this->isReversedProxy():
                $scheme = 'https';
                break;
            default:
                $scheme = 'http';
                break;
        }

        $this->setScheme($scheme);
    }

    protected function isReversedProxy()
    {
        return isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
    }

    /**
     * Detect if a proxy is in use, and, if so, set the host based on it
     *
     * @return bool
     */
    protected function setHostFromProxy()
    {
        if (!$this->useProxy) {
            return false;
        }

        if (!isset($_SERVER['HTTP_X_FORWARDED_HOST']) || empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            return false;
        }

        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        if (strpos($host, ',') !== false) {
            $hosts = explode(',', $host);
            $host = trim(array_pop($hosts));
        }
        if (empty($host)) {
            return false;
        }
        $this->setHost($host);

        return true;
    }

    /**
     * Set port based on detected proxy headers
     *
     * @return bool
     */
    protected function setPortFromProxy()
    {
        if (!$this->useProxy) {
            return false;
        }

        if (!isset($_SERVER['HTTP_X_FORWARDED_PORT']) || empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
            return false;
        }

        $port = $_SERVER['HTTP_X_FORWARDED_PORT'];
        $this->setPort($port);

        return true;
    }

    /**
     * Set the current scheme based on detected proxy headers
     *
     * @return bool
     */
    protected function setSchemeFromProxy()
    {
        if (!$this->useProxy) {
            return false;
        }

        if (isset($_SERVER['SSL_HTTPS'])) {
            $sslHttps = strtolower($_SERVER['SSL_HTTPS']);
            if (in_array($sslHttps, array('on', 1))) {
                $this->setScheme('https');
                return true;
            }
        }

        if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return false;
        }

        $scheme = trim(strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']));
        if (empty($scheme)) {
            return false;
        }

        $this->setScheme($scheme);

        return true;
    }

    /**
     * Sets host
     *
     * @param  string $host
     * @return ServerUrl
     */
    public function setHost($host)
    {
        $port   = $this->getPort();
        $scheme = $this->getScheme();

        if (($scheme == 'http' && (null === $port || $port == 80))
            || ($scheme == 'https' && (null === $port || $port == 443))
        ) {
            $this->host = $host;
            return $this;
        }

        $this->host = $host . ':' . $port;

        return $this;
    }

    /**
     * Returns host
     *
     * @return string
     */
    public function getHost()
    {
        if (null === $this->host) {
            $this->detectHost();
        }

        return $this->host;
    }

    /**
     * Set server port
     *
     * @param  int $port
     * @return ServerUrl
     */
    public function setPort($port)
    {
        $this->port = (int) $port;

        return $this;
    }

    /**
     * Retrieve the server port
     *
     * @return int|null
     */
    public function getPort()
    {
        if (null === $this->port) {
            $this->detectPort();
        }

        return $this->port;
    }

    /**
     * Sets scheme (typically http or https)
     *
     * @param  string $scheme
     * @return ServerUrl
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Returns scheme (typically http or https)
     *
     * @return string
     */
    public function getScheme()
    {
        if (null === $this->scheme) {
            $this->detectScheme();
        }

        return $this->scheme;
    }

    /**
     * Set flag indicating whether or not to query proxy servers
     *
     * @param  bool $useProxy
     * @return ServerUrl
     */
    public function setUseProxy($useProxy = false)
    {
        $this->useProxy = (bool) $useProxy;

        return $this;
    }
}
