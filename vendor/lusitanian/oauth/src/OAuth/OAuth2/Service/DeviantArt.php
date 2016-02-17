<?php

namespace OAuth\OAuth2\Service;

use OAuth\Common\Exception\Exception;
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

class DeviantArt extends AbstractService
{
    /**
     * DeviantArt www url - used to build dialog urls
     */
    const WWW_URL = 'https://www.deviantart.com/';

    /**
     * Defined scopes
     *
     * If you don't think this is scary you should not be allowed on the web at all
     *
     * @link https://www.deviantart.com/developers/authentication
     * @link https://www.deviantart.com/developers/http/v1/20150217
     */
    const SCOPE_FEED                       = 'feed';
    const SCOPE_BROWSE                     = 'browse';
    const SCOPE_COMMENT                    = 'comment.post';
    const SCOPE_STASH                      = 'stash';
    const SCOPE_USER                       = 'user';
    const SCOPE_USERMANAGE                 = 'user.manage';

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://www.deviantart.com/api/v1/oauth2/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://www.deviantart.com/oauth2/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://www.deviantart.com/oauth2/token');
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
            $token->setLifeTime($data['expires_in']);
        }

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);
        unset($data['expires_in']);

        $token->setExtraParams($data);

        return $token;
    }
}
