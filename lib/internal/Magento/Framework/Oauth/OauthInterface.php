<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth;

/**
 * Interface OauthInterface
 *
 * This interface provides methods consistent with implementing a 2-legged OAuth authentication mechanism. Methods
 * include creating a request token, getting an access token, and performing certain validations on tokens and
 * token requests. A method is also included for generating an OAuth header that can be used in an HTTP request.
 *
 */
interface OauthInterface
{
    /**#@+
     * OAuth result statuses
     */
    const ERR_OK = 0;

    const ERR_VERSION_REJECTED = 1;

    const ERR_PARAMETER_ABSENT = 2;

    const ERR_PARAMETER_REJECTED = 3;

    const ERR_TIMESTAMP_REFUSED = 4;

    const ERR_NONCE_USED = 5;

    const ERR_SIGNATURE_METHOD_REJECTED = 6;

    const ERR_SIGNATURE_INVALID = 7;

    const ERR_CONSUMER_KEY_REJECTED = 8;

    const ERR_TOKEN_USED = 9;

    const ERR_TOKEN_EXPIRED = 10;

    const ERR_TOKEN_REVOKED = 11;

    const ERR_TOKEN_REJECTED = 12;

    const ERR_VERIFIER_INVALID = 13;

    const ERR_PERMISSION_UNKNOWN = 14;

    const ERR_PERMISSION_DENIED = 15;

    const ERR_METHOD_NOT_ALLOWED = 16;

    const ERR_CONSUMER_KEY_INVALID = 17;

    /**#@-*/

    /**#@+
     * Signature Methods
     */
    const SIGNATURE_SHA1 = 'HMAC-SHA1';

    const SIGNATURE_SHA256 = 'HMAC-SHA256';

    /**#@-*/

    /**
     * Issue a pre-authorization request token to the caller.
     *
     * @param array $params - Array containing parameters necessary for requesting Request Token.
     * <pre>
     * array (
     *         'oauth_version' => '1.0',
     *         'oauth_signature_method' => 'HMAC-SHA1',
     *         'oauth_nonce' => 'rI7PSWxTZRHWU3R',
     *         'oauth_timestamp' => '1377183099',
     *         'oauth_consumer_key' => 'a6aa81cc3e65e2960a4879392445e718',
     *         'oauth_signature' => 'VNg4mhFlXk7%2FvsxMqqUd5DWIj9s%3D'
     * )
     * </pre>
     * @param string $requestUrl - The request Url.
     * @param string $httpMethod - (default: 'POST')
     * @return array - The request token/secret pair.
     * <pre>
     * array (
     *         'oauth_token' => 'gshsjkndtyhwjhdbutfgbsnhtrequikf',
     *         'oauth_token_secret' => 'gshsjkndtyhwjhdbutfgbsnhtrequikf'
     * )
     * </pre>
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function getRequestToken($params, $requestUrl, $httpMethod = 'POST');

    /**
     * Get access token for a pre-authorized request token.
     *
     * @param array $params - Array containing parameters necessary for requesting Access Token.
     * <pre>
     * array (
     *         'oauth_version' => '1.0',
     *         'oauth_signature_method' => 'HMAC-SHA1',
     *         'oauth_token' => 'a6aa81cc3e65e2960a487939244sssss',
     *         'oauth_nonce' => 'rI7PSWxTZRHWU3R',
     *         'oauth_timestamp' => '1377183099',
     *         'oauth_consumer_key' => 'a6aa81cc3e65e2960a4879392445e718',
     *         'oauth_signature' => 'VNg4mhFlXk7%2FvsxMqqUd5DWIj9s%3D',
     *         'oauth_verifier' => 'a6aa81cc3e65e2960a487939244vvvvv'
     * )
     * </pre>
     * @param string $requestUrl - The request Url.
     * @param string $httpMethod - (default: 'POST')
     * @return array - The access token/secret pair.
     * <pre>
     * array (
     *         'oauth_token' => 'gshsjkndtyhwjhdbutfgbsnhtrequikf',
     *         'oauth_token_secret' => 'gshsjkndtyhwjhdbutfgbsnhtrequikf'
     * )
     * </pre>
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function getAccessToken($params, $requestUrl, $httpMethod = 'POST');

    /**
     * Validate an access token request.
     *
     * @param array $params - Array containing parameters necessary for validating Access Token.
     * <pre>
     * array (
     *         'oauth_version' => '1.0',
     *         'oauth_signature_method' => 'HMAC-SHA1',
     *         'oauth_token' => 'a6aa81cc3e65e2960a487939244sssss',
     *         'oauth_nonce' => 'rI7PSWxTZRHWU3R',
     *         'oauth_timestamp' => '1377183099',
     *         'oauth_consumer_key' => 'a6aa81cc3e65e2960a4879392445e718',
     *         'oauth_signature' => 'VNg4mhFlXk7%2FvsxMqqUd5DWIj9s%3D'
     * )
     * </pre>
     * @param string $requestUrl - The request Url.
     * @param string $httpMethod - (default: 'POST')
     * @return int Consumer ID.
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function validateAccessTokenRequest($params, $requestUrl, $httpMethod = 'POST');

    /**
     * Validate an access token string.
     *
     * @param string $accessToken - The access token.
     * @return int - Consumer ID if the access token is valid.
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function validateAccessToken($accessToken);

    /**
     * Build the Oauth authorization header for an authenticated API request
     *
     * @param array $params - Array containing parameters to build the Oauth HTTP Authorization header
     * <pre>
     *  array (
     *      'oauth_consumer_key' => 'edf957ef88492f0a32eb7e1731e85d',
     *      'oauth_consumer_secret' => 'asdawwewefrtyh2f0a32eb7e1731e85d',
     *      'oauth_token' => '7c0709f789e1f38a17aa4b9a28e1b06c',
     *      'oauth_secret' => 'a6agsfrsfgsrjjjjyy487939244ssggg',
     *      'custom_param1' => 'foo',
     *      'custom_param2' => 'bar'
     *   );
     * </pre>
     * @param string $requestUrl e.g 'http://www.example.com/endpoint'
     * @param string $signatureMethod (default: 'HMAC-SHA1')
     * @param string $httpMethod (default: 'POST')
     * @return string
     * <pre>
     * OAuth oauth_version="1.0", oauth_signature_method="HMAC-SHA1", oauth_nonce="5X1aWR2qzf2uFm1",
     * oauth_timestamp="1381930661", oauth_consumer_key="34edf957ef88492f0a32eb7e1731e85d",
     * oauth_token="7c0709f789e1f38a17aa4b9a28e1b06c", oauth_signature="agVxK0epXOOeQK4%2Bc7UAqUXoAok%3D"
     * <pre>
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function buildAuthorizationHeader(
        $params,
        $requestUrl,
        $signatureMethod = self::SIGNATURE_SHA1,
        $httpMethod = 'POST'
    );
}
