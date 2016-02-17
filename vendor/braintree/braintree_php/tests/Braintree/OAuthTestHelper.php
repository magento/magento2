<?php

class Braintree_OAuthTestHelper
{
    public static function createGrant($gateway, $params)
    {
        $http = new Braintree_HttpOAuth($gateway->config);
        $response = $http->post('/oauth_testing/grants', $params);
        return $response['code'];
    }

    public static function createCredentials($params)
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => $params['clientId'],
            'clientSecret' => $params['clientSecret']
        ));

        $code = Braintree_OAuthTestHelper::createGrant($gateway, array(
            'merchant_public_id' => $params['merchantId'],
            'scope' => 'read_write'
        ));

        $credentials = $gateway->oauth()->createTokenFromCode(array(
            'code' => $code,
            'scope' => 'read_write',
        ));

        return $credentials;
    }
}
