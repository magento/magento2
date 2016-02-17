<?php

namespace OAuth\OAuth1\Service;

use OAuth\OAuth1\Signature\SignatureInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Exception\Exception;

class Twitter extends AbstractService
{
    const ENDPOINT_AUTHENTICATE = "https://api.twitter.com/oauth/authenticate";
    const ENDPOINT_AUTHORIZE    = "https://api.twitter.com/oauth/authorize";

    protected $authorizationEndpoint   = self::ENDPOINT_AUTHENTICATE;

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        SignatureInterface $signature,
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $signature, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://api.twitter.com/1.1/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenEndpoint()
    {
        return new Uri('https://api.twitter.com/oauth/request_token');
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        if ($this->authorizationEndpoint != self::ENDPOINT_AUTHENTICATE
        && $this->authorizationEndpoint != self::ENDPOINT_AUTHORIZE) {
            $this->authorizationEndpoint = self::ENDPOINT_AUTHENTICATE;
        }
        return new Uri($this->authorizationEndpoint);
    }

    /**
     * @param string $authorizationEndpoint
     *
     * @throws Exception
     */
    public function setAuthorizationEndpoint($endpoint)
    {
        if ($endpoint != self::ENDPOINT_AUTHENTICATE && $endpoint != self::ENDPOINT_AUTHORIZE) {
            throw new Exception(
                sprintf("'%s' is not a correct Twitter authorization endpoint.", $endpoint)
            );
        }
        $this->authorizationEndpoint = $endpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://api.twitter.com/oauth/access_token');
    }

    /**
     * {@inheritdoc}
     */
    protected function parseRequestTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true') {
            throw new TokenResponseException('Error in retrieving token.');
        }

        return $this->parseAccessTokenResponse($responseBody);
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response: ' . $responseBody);
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        } elseif (!isset($data["oauth_token"]) || !isset($data["oauth_token_secret"])) {
            throw new TokenResponseException('Invalid response. OAuth Token data not set: ' . $responseBody);
        }

        $token = new StdOAuth1Token();

        $token->setRequestToken($data['oauth_token']);
        $token->setRequestTokenSecret($data['oauth_token_secret']);
        $token->setAccessToken($data['oauth_token']);
        $token->setAccessTokenSecret($data['oauth_token_secret']);

        $token->setEndOfLife(StdOAuth1Token::EOL_NEVER_EXPIRES);
        unset($data['oauth_token'], $data['oauth_token_secret']);
        $token->setExtraParams($data);

        return $token;
    }
}
