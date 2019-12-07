<?php
/**
 * oAuth client for Magento REST API.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Authentication\Rest;

use Magento\TestFramework\Helper\Bootstrap;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\OAuth1\Signature\SignatureInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\OAuth1\Token\TokenInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OauthClient extends AbstractService
{
    /**
     * The maximum timeout for http request in seconds
     */
    const DEFAULT_TIMEOUT = 120;

    /** @var string|null */
    protected $_oauthVerifier = null;

    public function __construct(
        Credentials $credentials,
        ClientInterface $httpClient = null,
        TokenStorageInterface $storage = null,
        SignatureInterface $signature = null,
        UriInterface $baseApiUri = null
    ) {
        if (!isset($httpClient)) {
            $httpClient = new \Magento\TestFramework\Authentication\Rest\CurlClient();
            $httpClient->setTimeout(self::DEFAULT_TIMEOUT);
        }
        if (!isset($storage)) {
            $storage = new \OAuth\Common\Storage\Memory();
        }
        if (!isset($signature)) {
            $signature = new \Magento\TestFramework\Authentication\Rest\OauthClient\Signature($credentials);
        }
        parent::__construct($credentials, $httpClient, $storage, $signature, $baseApiUri);
    }

    /**
     * @return UriInterface
     */
    public function getRequestTokenEndpoint()
    {
        return new Uri(TESTS_BASE_URL . '/oauth/token/request');
    }

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        throw new \OAuth\Common\Exception\Exception(
            'Magento REST API is 2-legged. Current operation is not available.'
        );
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri(TESTS_BASE_URL . '/oauth/token/access');
    }

    /**
     * Returns the TestModule1 Rest API endpoint.
     *
     * @return UriInterface
     */
    public function getTestApiEndpoint()
    {
        $defaultStoreCode = Bootstrap::getObjectManager()->get(\Magento\Store\Model\StoreManagerInterface::class)
            ->getStore()->getCode();
        return new Uri(TESTS_BASE_URL . '/rest/' . $defaultStoreCode . '/V1/testmodule1');
    }

    /**
     * Parses the access token response and returns a TokenInterface.
     *
     * @return TokenInterface
     * @param string $responseBody
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        return $this->_parseToken($responseBody);
    }

    /**
     * Parses the request token response and returns a TokenInterface.
     *
     * @return TokenInterface
     * @param string $responseBody
     * @throws TokenResponseException
     */
    protected function parseRequestTokenResponse($responseBody)
    {
        $data = $this->_parseResponseBody($responseBody);
        if (isset($data['oauth_verifier'])) {
            $this->_oauthVerifier = $data['oauth_verifier'];
        }
        return $this->_parseToken($responseBody);
    }

    /**
     * Parse response body and create oAuth token object based on parameters provided.
     *
     * @param string $responseBody
     * @return StdOAuth1Token
     * @throws TokenResponseException
     */
    protected function _parseToken($responseBody)
    {
        $data = $this->_parseResponseBody($responseBody);
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

    /**
     * Parse response body and return data in array.
     *
     * @param string $responseBody
     * @return array
     * @throws \OAuth\Common\Http\Exception\TokenResponseException
     */
    protected function _parseResponseBody($responseBody)
    {
        if (!is_string($responseBody)) {
            throw new TokenResponseException("Response body is expected to be a string.");
        }
        parse_str($responseBody, $data);
        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException("Error occurred: '{$data['error']}'");
        }
        return $data;
    }

    /**
     * Retrieve oAuth verifier that was obtained during request token request.
     *
     * @return string
     * @throws \OAuth\Common\Http\Exception\TokenResponseException
     */
    public function getOauthVerifier()
    {
        if (!isset($this->_oauthVerifier) || isEmpty($this->_oauthVerifier)) {
            throw new TokenResponseException("oAuth verifier must be obtained during request token request.");
        }
        return $this->_oauthVerifier;
    }

    /**
     * @override to fix since parent implementation from lib not sending the oauth_verifier when requesting access token
     * Builds the authorization header for an authenticated API request
     * @param string $method
     * @param UriInterface $uri the uri the request is headed
     * @param \OAuth\OAuth1\Token\TokenInterface $token
     * @param $bodyParams array
     * @return string
     */
    protected function buildAuthorizationHeaderForAPIRequest(
        $method,
        UriInterface $uri,
        TokenInterface $token,
        $bodyParams = null
    ) {
        $this->signature->setTokenSecret($token->getAccessTokenSecret());
        $parameters = $this->getBasicAuthorizationHeaderInfo();
        if (isset($parameters['oauth_callback'])) {
            unset($parameters['oauth_callback']);
        }

        $parameters = array_merge($parameters, ['oauth_token' => $token->getAccessToken()]);
        $parameters = array_merge($parameters, $bodyParams);
        $parameters['oauth_signature'] = $this->signature->getSignature($uri, $parameters, $method);

        $authorizationHeader = 'OAuth ';
        $delimiter = '';

        foreach ($parameters as $key => $value) {
            $authorizationHeader .= $delimiter . rawurlencode($key) . '="' . rawurlencode($value) . '"';
            $delimiter = ', ';
        }

        return $authorizationHeader;
    }

    /**
     * Builds the oAuth authorization header for an authenticated API request
     *
     * @param UriInterface $uri the uri the request is headed
     * @param \OAuth\OAuth1\Token\TokenInterface $token
     * @param string $tokenSecret used to verify the passed token
     * @param array $bodyParams
     * @param string $method HTTP method to use
     * @return array
     */
    public function buildOauthAuthorizationHeader($uri, $token, $tokenSecret, $bodyParams, $method = 'GET')
    {
        $uri = new Uri($uri);
        $tokenObj = new StdOAuth1Token();
        $tokenObj->setAccessToken($token);
        $tokenObj->setAccessTokenSecret($tokenSecret);
        $tokenObj->setEndOfLife(StdOAuth1Token::EOL_NEVER_EXPIRES);
        return [
            'Authorization: ' . $this->buildAuthorizationHeaderForAPIRequest($method, $uri, $tokenObj, $bodyParams)
        ];
    }

    /**
     * Builds the bearer token authorization header
     *
     * @param string $token
     * @return array
     */
    public function buildBearerTokenAuthorizationHeader($token)
    {
        return [
            'Authorization: Bearer ' . $token
        ];
    }

    /**
     * Validates a Test REST api call access using oauth access token
     *
     * @param TokenInterface $token The access token.
     * @param string $method HTTP method.
     * @return array
     * @throws TokenResponseException
     */
    public function validateAccessToken($token, $method = 'GET')
    {
        //Need to add Accept header else Magento errors out with 503
        $extraAuthenticationHeaders = ['Accept' => 'application/json'];

        $this->signature->setTokenSecret($token->getAccessTokenSecret());

        $authorizationHeader = [
            'Authorization' => $this->buildAuthorizationHeaderForAPIRequest(
                $method,
                $this->getTestApiEndpoint(),
                $token,
                []
            ),
        ];

        $headers = array_merge($authorizationHeader, $extraAuthenticationHeaders);

        $responseBody = $this->httpClient->retrieveResponse($this->getTestApiEndpoint(), [], $headers, $method);

        return json_decode($responseBody);
    }
}
