<?php
/**
 * Braintree OAuthGateway module
 * PHP Version 5
 * Creates and manages Braintree Addresses
 *
 * @package   Braintree
 * @copyright 2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_OAuthGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_http = new Braintree_HttpOAuth($gateway->config);

        $this->_config->assertHasClientCredentials();
    }

    public function createTokenFromCode($params)
    {
        $params['grantType'] = "authorization_code";
        $response = $this->_http->post('/oauth/access_tokens', $params);
        return $this->_verifyGatewayResponse($response);
    }

    public function createTokenFromRefreshToken($params)
    {
        $params['grantType'] = "refresh_token";
        $response = $this->_http->post('/oauth/access_tokens', $params);
        return $this->_verifyGatewayResponse($response);
    }

    private function _verifyGatewayResponse($response)
    {
        $result = Braintree_OAuthCredentials::factory($response);
        $result->success = !isset($response['error']);
        return $result;
    }

    public function connectUrl($params = array())
    {
        $query = Braintree_Util::camelCaseToDelimiterArray($params, '_');
        $query['client_id'] = $this->_config->getClientId();
        $url = $this->_config->baseUrl() . '/oauth/connect?' . http_build_query($query);

        return $this->signUrl($url);
    }

    private function signUrl($url)
    {
        $key = hash('sha256', $this->_config->getClientSecret(), true);
        return $url . '&signature=' . hash_hmac('sha256', $url, $key) . '&algorithm=SHA256';
    }
}
