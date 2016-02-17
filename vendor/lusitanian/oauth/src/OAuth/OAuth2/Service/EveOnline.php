<?php
/**
 * Contains EveOnline class.
 * PHP version 5.4
 * @copyright 2014 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace OAuth\OAuth2\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class EveOnline
 */
class EveOnline extends AbstractService
{
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://login.eveonline.com');
        }
    }

    /**
     * Returns the authorization API endpoint.
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri($this->baseApiUri . '/oauth/authorize');
    }

    /**
     * Returns the access token API endpoint.
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri($this->baseApiUri . '/oauth/token');
    }

    /**
     * Parses the access token response and returns a TokenInterface.
     *
     * @param string $responseBody
     *
     * @return TokenInterface
     * @throws TokenResponseException
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error_description'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error_description'] . '"');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        $token->setLifeTime($data['expires_in']);

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);
        unset($data['expires_in']);

        $token->setExtraParams($data);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_HEADER_BEARER;
    }
}
