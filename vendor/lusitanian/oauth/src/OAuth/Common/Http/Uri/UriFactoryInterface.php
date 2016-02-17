<?php

namespace OAuth\Common\Http\Uri;

/**
 * Factory interface for uniform resource indicators
 */
interface UriFactoryInterface
{
    /**
     * Factory method to build a URI from a super-global $_SERVER array.
     *
     * @param array $_server
     *
     * @return UriInterface
     */
    public function createFromSuperGlobalArray(array $_server);

    /**
     * Creates a URI from an absolute URI
     *
     * @param string $absoluteUri
     *
     * @return UriInterface
     */
    public function createFromAbsolute($absoluteUri);

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
    public function createFromParts($scheme, $userInfo, $host, $port, $path = '', $query = '', $fragment = '');
}
