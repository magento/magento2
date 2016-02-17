<?php

namespace OAuth\Common\Http\Uri;

use RuntimeException;

/**
 * Factory class for uniform resource indicators
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * Factory method to build a URI from a super-global $_SERVER array.
     *
     * @param array $_server
     *
     * @return UriInterface
     */
    public function createFromSuperGlobalArray(array $_server)
    {
        if ($uri = $this->attemptProxyStyleParse($_server)) {
            return $uri;
        }

        $scheme = $this->detectScheme($_server);
        $host = $this->detectHost($_server);
        $port = $this->detectPort($_server);
        $path = $this->detectPath($_server);
        $query = $this->detectQuery($_server);

        return $this->createFromParts($scheme, '', $host, $port, $path, $query);
    }

    /**
     * @param string $absoluteUri
     *
     * @return UriInterface
     */
    public function createFromAbsolute($absoluteUri)
    {
        return new Uri($absoluteUri);
    }

    /**
     * Factory method to build a URI from parts
     *
     * @param string $scheme
     * @param string $userInfo
     * @param string $host
     * @param string $port
     * @param string $path
     * @param string $query
     * @param string $fragment
     *
     * @return UriInterface
     */
    public function createFromParts($scheme, $userInfo, $host, $port, $path = '', $query = '', $fragment = '')
    {
        $uri = new Uri();
        $uri->setScheme($scheme);
        $uri->setUserInfo($userInfo);
        $uri->setHost($host);
        $uri->setPort($port);
        $uri->setPath($path);
        $uri->setQuery($query);
        $uri->setFragment($fragment);

        return $uri;
    }

    /**
     * @param array $_server
     *
     * @return UriInterface|null
     */
    private function attemptProxyStyleParse($_server)
    {
        // If the raw HTTP request message arrives with a proxy-style absolute URI in the
        // initial request line, the absolute URI is stored in $_SERVER['REQUEST_URI'] and
        // we only need to parse that.
        if (isset($_server['REQUEST_URI']) && parse_url($_server['REQUEST_URI'], PHP_URL_SCHEME)) {
            return new Uri($_server['REQUEST_URI']);
        }

        return null;
    }

    /**
     * @param array $_server
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function detectPath($_server)
    {
        if (isset($_server['REQUEST_URI'])) {
            $uri = $_server['REQUEST_URI'];
        } elseif (isset($_server['REDIRECT_URL'])) {
            $uri = $_server['REDIRECT_URL'];
        } else {
            throw new RuntimeException('Could not detect URI path from superglobal');
        }

        $queryStr = strpos($uri, '?');
        if ($queryStr !== false) {
            $uri = substr($uri, 0, $queryStr);
        }

        return $uri;
    }

    /**
     * @param array $_server
     *
     * @return string
     */
    private function detectHost(array $_server)
    {
        $host = isset($_server['HTTP_HOST']) ? $_server['HTTP_HOST'] : '';

        if (strstr($host, ':')) {
            $host = parse_url($host, PHP_URL_HOST);
        }

        return $host;
    }

    /**
     * @param array $_server
     *
     * @return string
     */
    private function detectPort(array $_server)
    {
        return isset($_server['SERVER_PORT']) ? $_server['SERVER_PORT'] : 80;
    }

    /**
     * @param array $_server
     *
     * @return string
     */
    private function detectQuery(array $_server)
    {
        return isset($_server['QUERY_STRING']) ? $_server['QUERY_STRING'] : '';
    }

    /**
     * Determine URI scheme component from superglobal array
     *
     * When using ISAPI with IIS, the value will be "off" if the request was
     * not made through the HTTPS protocol. As a result, we filter the
     * value to a bool.
     *
     * @param array $_server A super-global $_SERVER array
     *
     * @return string Returns http or https depending on the URI scheme
     */
    private function detectScheme(array $_server)
    {
        if (isset($_server['HTTPS']) && filter_var($_server['HTTPS'], FILTER_VALIDATE_BOOLEAN)) {
            return 'https';
        } else {
            return 'http';
        }
    }
}
