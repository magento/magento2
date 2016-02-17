<?php

namespace OAuth\Common\Http\Uri;

use InvalidArgumentException;

/**
 * Standards-compliant URI class.
 */
class Uri implements UriInterface
{
    /**
     * @var string
     */
    private $scheme = 'http';

    /**
     * @var string
     */
    private $userInfo = '';

    /**
     * @var string
     */
    private $rawUserInfo = '';

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port = 80;

    /**
     * @var string
     */
    private $path = '/';

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var string
     */
    private $fragment = '';

    /**
     * @var bool
     */
    private $explicitPortSpecified = false;

    /**
     * @var bool
     */
    private $explicitTrailingHostSlash = false;

    /**
     * @param string $uri
     */
    public function __construct($uri = null)
    {
        if (null !== $uri) {
            $this->parseUri($uri);
        }
    }

    /**
     * @param string $uri
     *
     * @throws \InvalidArgumentException
     */
    protected function parseUri($uri)
    {
        if (false === ($uriParts = parse_url($uri))) {
            // congratulations if you've managed to get parse_url to fail,
            // it seems to always return some semblance of a parsed url no matter what
            throw new InvalidArgumentException("Invalid URI: $uri");
        }

        if (!isset($uriParts['scheme'])) {
            throw new InvalidArgumentException('Invalid URI: http|https scheme required');
        }

        $this->scheme = $uriParts['scheme'];
        $this->host = $uriParts['host'];

        if (isset($uriParts['port'])) {
            $this->port = $uriParts['port'];
            $this->explicitPortSpecified = true;
        } else {
            $this->port = strcmp('https', $uriParts['scheme']) ? 80 : 443;
            $this->explicitPortSpecified = false;
        }

        if (isset($uriParts['path'])) {
            $this->path = $uriParts['path'];
            if ('/' === $uriParts['path']) {
                $this->explicitTrailingHostSlash = true;
            }
        } else {
            $this->path = '/';
        }

        $this->query = isset($uriParts['query']) ? $uriParts['query'] : '';
        $this->fragment = isset($uriParts['fragment']) ? $uriParts['fragment'] : '';

        $userInfo = '';
        if (!empty($uriParts['user'])) {
            $userInfo .= $uriParts['user'];
        }
        if ($userInfo && !empty($uriParts['pass'])) {
            $userInfo .= ':' . $uriParts['pass'];
        }

        $this->setUserInfo($userInfo);
    }

    /**
     * @param string $rawUserInfo
     *
     * @return string
     */
    protected function protectUserInfo($rawUserInfo)
    {
        $colonPos = strpos($rawUserInfo, ':');

        // rfc3986-3.2.1 | http://tools.ietf.org/html/rfc3986#section-3.2
        // "Applications should not render as clear text any data
        // after the first colon (":") character found within a userinfo
        // subcomponent unless the data after the colon is the empty string
        // (indicating no password)"
        if ($colonPos !== false && strlen($rawUserInfo)-1 > $colonPos) {
            return substr($rawUserInfo, 0, $colonPos) . ':********';
        } else {
            return $rawUserInfo;
        }
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getRawUserInfo()
    {
        return $this->rawUserInfo;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Uses protected user info by default as per rfc3986-3.2.1
     * Uri::getRawAuthority() is available if plain-text password information is desirable.
     *
     * @return string
     */
    public function getAuthority()
    {
        $authority = $this->userInfo ? $this->userInfo.'@' : '';
        $authority .= $this->host;

        if ($this->explicitPortSpecified) {
            $authority .= ":{$this->port}";
        }

        return $authority;
    }

    /**
     * @return string
     */
    public function getRawAuthority()
    {
        $authority = $this->rawUserInfo ? $this->rawUserInfo.'@' : '';
        $authority .= $this->host;

        if ($this->explicitPortSpecified) {
            $authority .= ":{$this->port}";
        }

        return $authority;
    }

    /**
     * @return string
     */
    public function getAbsoluteUri()
    {
        $uri = $this->scheme . '://' . $this->getRawAuthority();

        if ('/' === $this->path) {
            $uri .= $this->explicitTrailingHostSlash ? '/' : '';
        } else {
            $uri .= $this->path;
        }

        if (!empty($this->query)) {
            $uri .= "?{$this->query}";
        }

        if (!empty($this->fragment)) {
            $uri .= "#{$this->fragment}";
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function getRelativeUri()
    {
        $uri = '';

        if ('/' === $this->path) {
            $uri .= $this->explicitTrailingHostSlash ? '/' : '';
        } else {
            $uri .= $this->path;
        }

        return $uri;
    }

    /**
     * Uses protected user info by default as per rfc3986-3.2.1
     * Uri::getAbsoluteUri() is available if plain-text password information is desirable.
     *
     * @return string
     */
    public function __toString()
    {
        $uri = $this->scheme . '://' . $this->getAuthority();

        if ('/' === $this->path) {
            $uri .= $this->explicitTrailingHostSlash ? '/' : '';
        } else {
            $uri .= $this->path;
        }

        if (!empty($this->query)) {
            $uri .= "?{$this->query}";
        }

        if (!empty($this->fragment)) {
            $uri .= "#{$this->fragment}";
        }

        return $uri;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        if (empty($path)) {
            $this->path = '/';
            $this->explicitTrailingHostSlash = false;
        } else {
            $this->path = $path;
            if ('/' === $this->path) {
                $this->explicitTrailingHostSlash = true;
            }
        }
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @param string $var
     * @param string $val
     */
    public function addToQuery($var, $val)
    {
        if (strlen($this->query) > 0) {
            $this->query .= '&';
        }
        $this->query .= http_build_query(array($var => $val), '', '&');
    }

    /**
     * @param string $fragment
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
    }

    /**
     * @param string $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }


    /**
     * @param string $userInfo
     */
    public function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo ? $this->protectUserInfo($userInfo) : '';
        $this->rawUserInfo = $userInfo;
    }


    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = intval($port);

        if (('https' === $this->scheme && $this->port === 443) || ('http' === $this->scheme && $this->port === 80)) {
            $this->explicitPortSpecified = false;
        } else {
            $this->explicitPortSpecified = true;
        }
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return bool
     */
    public function hasExplicitTrailingHostSlash()
    {
        return $this->explicitTrailingHostSlash;
    }

    /**
     * @return bool
     */
    public function hasExplicitPortSpecified()
    {
        return $this->explicitPortSpecified;
    }
}
