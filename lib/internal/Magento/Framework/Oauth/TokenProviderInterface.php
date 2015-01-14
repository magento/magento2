<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth;

/**
 * Interface TokenProviderInterface
 *
 * This interface provides token manipulation, such as creating a request token and getting an access token as well
 * as methods for performing certain validations on tokens and token requests. Consumer methods are also provided to
 * help clients manipulating tokens validate and acquire the associated token consumer.
 *
 */
interface TokenProviderInterface
{
    /**
     * Validate the consumer.
     *
     * @param ConsumerInterface $consumer - The consumer.
     * @return bool - True if the consumer is valid.
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function validateConsumer($consumer);

    /**
     * Create a request token for the specified consumer.
     * Example:
     * <pre>
     *     array(
     *         'oauth_token' => 'gshsjkndtyhwjhdbutfgbsnhtrequikf,
     *         'oauth_token_secret' => 'gshsjkndtyhwjhdbutfgbsnhtrequikf'
     *     )
     * </pre>
     *
     * @param ConsumerInterface $consumer
     * @return array - The request token and secret.
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function createRequestToken($consumer);

    /**
     * Validates the request token and verifier. Verifies the request token is associated with the consumer.
     *
     * @param string $requestToken - The 'oauth_token' request token value.
     * @param ConsumerInterface $consumer - The consumer given the 'oauth_consumer_key'.
     * @param string $oauthVerifier - The 'oauth_verifier' value.
     * @return string - The request token secret (i.e. 'oauth_token_secret').
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function validateRequestToken($requestToken, $consumer, $oauthVerifier);

    /**
     * Retrieve access token for the specified consumer given the consumer key.
     * Example:
     * <pre>
     *     array(
     *         'oauth_token' => 'gshsjkndtyhwjhdbutfgbsnhtrequikf,
     *         'oauth_token_secret' => 'gshsjkndtyhwjhdbutfgbsnhtrequikf'
     *     )
     * </pre>
     *
     * @param ConsumerInterface $consumer - The consumer given the 'oauth_consumer_key'.
     * @return array - The access token and secret.
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function getAccessToken($consumer);

    /**
     * Validates the Oauth token type and verifies that it's associated with the consumer.
     *
     * @param string $accessToken - The 'oauth_token' access token value.
     * @param ConsumerInterface $consumer - The consumer given the 'oauth_consumer_key'.
     * @return string - The access token secret.
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function validateAccessTokenRequest($accessToken, $consumer);

    /**
     * Validate an access token string.
     *
     * @param string $accessToken - The 'oauth_token' access token string.
     * @return int - Consumer ID if the access token is valid.
     * @throws \Magento\Framework\Oauth\Exception - Validation errors.
     */
    public function validateAccessToken($accessToken);

    /**
     * Perform basic validation of an Oauth token, of any type (e.g. request, access, etc.).
     *
     * @param string $oauthToken - The token string.
     * @return bool - True if the Oauth token passes basic validation.
     */
    public function validateOauthToken($oauthToken);

    /**
     * Retrieve a consumer given the consumer's key.
     *
     * @param string $consumerKey - The 'oauth_consumer_key' value.
     * @return ConsumerInterface
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function getConsumerByKey($consumerKey);
}
