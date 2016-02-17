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
 * PayPal service.
 *
 * @author Flávio Heleno <flaviohbatista@gmail.com>
 * @link https://developer.paypal.com/webapps/developer/docs/integration/direct/log-in-with-paypal/detailed/
 */
class Paypal extends AbstractService
{
    /**
     * Defined scopes
     * @link https://developer.paypal.com/webapps/developer/docs/integration/direct/log-in-with-paypal/detailed/
     * @see  #attributes
     */
    const SCOPE_OPENID           = 'openid';
    const SCOPE_PROFILE          = 'profile';
    const SCOPE_PAYPALATTRIBUTES = 'https://uri.paypal.com/services/paypalattributes';
    const SCOPE_EMAIL            = 'email';
    const SCOPE_ADDRESS          = 'address';
    const SCOPE_PHONE            = 'phone';
    const SCOPE_EXPRESSCHECKOUT  = 'https://uri.paypal.com/services/expresscheckout';

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://api.paypal.com/v1/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://api.paypal.com/v1/identity/openidconnect/tokenservice');
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
        } elseif (isset($data['message'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['message'] . '"');
        } elseif (isset($data['name'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['name'] . '"');
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
}
