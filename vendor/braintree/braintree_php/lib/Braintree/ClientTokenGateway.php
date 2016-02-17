<?php

class Braintree_ClientTokenGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Braintree_Http($gateway->config);
    }

    public function generate($params=array())
    {
        if (!array_key_exists("version", $params)) {
            $params["version"] = Braintree_ClientToken::DEFAULT_VERSION;
        }

        $this->conditionallyVerifyKeys($params);
        $generateParams = array("client_token" => $params);

        return $this->_doGenerate('/client_token', $generateParams);
    }

    /**
     * sends the generate request to the gateway
     *
     * @ignore
     * @param var $url
     * @param array $params
     * @return mixed
     */
    public function _doGenerate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    public function conditionallyVerifyKeys($params)
    {
        if (array_key_exists("customerId", $params)) {
            Braintree_Util::verifyKeys($this->generateWithCustomerIdSignature(), $params);
        } else {
            Braintree_Util::verifyKeys($this->generateWithoutCustomerIdSignature(), $params);
        }
    }

    public function generateWithCustomerIdSignature()
    {
        return array("version", "customerId", "proxyMerchantId", array("options" => array("makeDefault", "verifyCard", "failOnDuplicatePaymentMethod")), "merchantAccountId");
    }

    public function generateWithoutCustomerIdSignature()
    {
        return array("version", "proxyMerchantId", "merchantAccountId");
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * If the request is successful, returns a client token string.
     * Otherwise, throws an InvalidArgumentException with the error
     * response from the Gateway or an HTTP status code exception.
     *
     * @ignore
     * @param array $response gateway response values
     * @return string client token
     * @throws InvalidArgumentException | HTTP status code exception
     */
    private function _verifyGatewayResponse($response)
    {
        if (isset($response['clientToken'])) {
            return $response['clientToken']['value'];
        } elseif (isset($response['apiErrorResponse'])) {
            throw new InvalidArgumentException(
                $response['apiErrorResponse']['message']
            );
        } else {
            throw new Braintree_Exception_Unexpected(
                "Expected clientToken or apiErrorResponse"
            );
        }
    }

}
