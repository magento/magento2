<?php

namespace OAuth\OAuth2\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Service\ServiceInterface as BaseServiceInterface;
use OAuth\Common\Http\Uri\UriInterface;

/**
 * Defines the common methods across OAuth 2 services.
 */
interface ServiceInterface extends BaseServiceInterface
{
    /**
     * Authorization methods for various services
     */
    const AUTHORIZATION_METHOD_HEADER_OAUTH    = 0;
    const AUTHORIZATION_METHOD_HEADER_BEARER   = 1;
    const AUTHORIZATION_METHOD_QUERY_STRING    = 2;
    const AUTHORIZATION_METHOD_QUERY_STRING_V2 = 3;
    const AUTHORIZATION_METHOD_QUERY_STRING_V3 = 4;
    const AUTHORIZATION_METHOD_QUERY_STRING_V4 = 5;

    /**
     * Retrieves and stores/returns the OAuth2 access token after a successful authorization.
     *
     * @param string $code The access code from the callback.
     *
     * @return TokenInterface $token
     *
     * @throws TokenResponseException
     */
    public function requestAccessToken($code);
}
