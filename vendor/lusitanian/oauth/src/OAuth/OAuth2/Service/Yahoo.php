<?php

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;

class Yahoo extends AbstractService
{

    /**
    * {@inheritdoc}
    */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://api.login.yahoo.com/oauth2/request_auth');
    }

    /**
    * {@inheritdoc}
    */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://api.login.yahoo.com/oauth2/get_token');
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
        $token->setLifetime($data['expires_in']);

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
        $encodedCredentials = base64_encode($this->credentials->getConsumerId() . ':' . $this->credentials->getConsumerSecret());
        return array('Authorization' => 'Basic ' . $encodedCredentials);
    }
}
