<?php

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

class Spotify extends AbstractService
{
    /**
     * Scopes
     *
     * @var string
     */
    const SCOPE_PLAYLIST_MODIFY_PUBLIC = 'playlist-modify-public';
    const SCOPE_PLAYLIST_MODIFY_PRIVATE = 'playlist-modify-private';
    const SCOPE_PLAYLIST_READ_PRIVATE = 'playlist-read-private';
    const SCOPE_STREAMING = 'streaming';
    const SCOPE_USER_LIBRARY_MODIFY = 'user-library-modify';
    const SCOPE_USER_LIBRARY_READ = 'user-library-read';
    const SCOPE_USER_READ_PRIVATE = 'user-read-private';
    const SCOPE_USER_READ_EMAIL = 'user-read-email';

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri, true);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://api.spotify.com/v1/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://accounts.spotify.com/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://accounts.spotify.com/api/token');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_HEADER_BEARER;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }


        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);

        if (isset($data['expires_in'])) {
            $token->setLifetime($data['expires_in']);
            unset($data['expires_in']);
        }

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);

        $token->setExtraParams($data);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraOAuthHeaders()
    {
        return array('Authorization' => 'Basic ' .
            base64_encode($this->credentials->getConsumerId() . ':' . $this->credentials->getConsumerSecret()));
    }
}
