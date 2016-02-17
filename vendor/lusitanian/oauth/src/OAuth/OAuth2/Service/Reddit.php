<?php

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

class Reddit extends AbstractService
{
    /**
     * Defined scopes
     *
     * @link http://www.reddit.com/dev/api/oauth
     */
    // User scopes
    const SCOPE_EDIT                         = 'edit';
    const SCOPE_HISTORY                      = 'history';
    const SCOPE_IDENTITY                     = 'identity';
    const SCOPE_MYSUBREDDITS                 = 'mysubreddits';
    const SCOPE_PRIVATEMESSAGES              = 'privatemessages';
    const SCOPE_READ                         = 'read';
    const SCOPE_SAVE                         = 'save';
    const SCOPE_SUBMIT                       = 'submit';
    const SCOPE_SUBSCRIBE                    = 'subscribe';
    const SCOPE_VOTE                         = 'vote';
    // Mod Scopes
    const SCOPE_MODCONFIG                    = 'modconfig';
    const SCOPE_MODFLAIR                     = 'modflair';
    const SCOPE_MODLOG                       = 'modlog';
    const SCOPE_MODPOST                      = 'modpost';

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri, true);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://oauth.reddit.com');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://ssl.reddit.com/api/v1/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://ssl.reddit.com/api/v1/access_token');
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
        // Reddit uses a Basic OAuth header
        return array('Authorization' => 'Basic ' .
            base64_encode($this->credentials->getConsumerId() . ':' . $this->credentials->getConsumerSecret()));
    }
}
