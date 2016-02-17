<?php

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

/**
 * Dailymotion service.
 *
 * @author Mouhamed SEYE <mouhamed@seye.pro>
 * @link http://www.dailymotion.com/doc/api/authentication.html
 */
class Dailymotion extends AbstractService
{
    /**
     * Scopes
     *
     * @var string
     */
    const SCOPE_EMAIL         = 'email',
          SCOPE_PROFILE       = 'userinfo',
          SCOPE_VIDEOS        = 'manage_videos',
          SCOPE_COMMENTS      = 'manage_comments',
          SCOPE_PLAYLIST      = 'manage_playlists',
          SCOPE_TILES         = 'manage_tiles',
          SCOPE_SUBSCRIPTIONS = 'manage_subscriptions',
          SCOPE_FRIENDS       = 'manage_friends',
          SCOPE_FAVORITES     = 'manage_favorites',
          SCOPE_GROUPS        = 'manage_groups';

    /**
     * Dialog form factors
     *
     * @var string
     */
    const DISPLAY_PAGE   = 'page',
          DISPLAY_POPUP  = 'popup',
          DISPLAY_MOBILE = 'mobile';

    /**
    * {@inheritdoc}
    */
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://api.dailymotion.com/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://api.dailymotion.com/oauth/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://api.dailymotion.com/oauth/token');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_HEADER_OAUTH;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error_description']) || isset($data['error'])) {
            throw new TokenResponseException(
                sprintf(
                    'Error in retrieving token: "%s"',
                    isset($data['error_description']) ? $data['error_description'] : $data['error']
                )
            );
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
    protected function getExtraOAuthHeaders()
    {
        return array('Accept' => 'application/json');
    }
}
