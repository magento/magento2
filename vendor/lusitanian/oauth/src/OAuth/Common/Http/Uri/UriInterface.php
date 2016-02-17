<?php

namespace OAuth\Common\Http\Uri;

interface UriInterface
{
    /**
     * @return string
     */
    public function getScheme();

    /**
     * @param string $scheme
     */
    public function setScheme($scheme);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @param string $host
     */
    public function setHost($host);

    /**
     * @return int
     */
    public function getPort();

    /**
     * @param int $port
     */
    public function setPort($port);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $path
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getQuery();

    /**
     * @param string $query
     */
    public function setQuery($query);

    /**
     * Adds a param to the query string.
     *
     * @param string $var
     * @param string $val
     */
    public function addToQuery($var, $val);

    /**
     * @return string
     */
    public function getFragment();

    /**
     * Should return URI user info, masking protected user info data according to rfc3986-3.2.1
     *
     * @return string
     */
    public function getUserInfo();

    /**
     * @param string $userInfo
     */
    public function setUserInfo($userInfo);

    /**
     * Should return the URI Authority, masking protected user info data according to rfc3986-3.2.1
     *
     * @return string
     */
    public function getAuthority();

    /**
     * Should return the URI string, masking protected user info data according to rfc3986-3.2.1
     *
     * @return string the URI string with user protected info masked
     */
    public function __toString();

    /**
     * Should return the URI Authority without masking protected user info data
     *
     * @return string
     */
    public function getRawAuthority();

    /**
     * Should return the URI user info without masking protected user info data
     *
     * @return string
     */
    public function getRawUserInfo();

    /**
     * Build the full URI based on all the properties
     *
     * @return string The full URI without masking user info
     */
    public function getAbsoluteUri();

    /**
     * Build the relative URI based on all the properties
     *
     * @return string The relative URI
     */
    public function getRelativeUri();

    /**
     * @return bool
     */
    public function hasExplicitTrailingHostSlash();

    /**
     * @return bool
     */
    public function hasExplicitPortSpecified();
}
