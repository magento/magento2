<?php
class Braintree_DiscountGateway
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

    public function all()
    {
        $path = $this->_config->merchantPath() . '/discounts';
        $response = $this->_http->get($path);

        $discounts = array("discount" => $response['discounts']);

        return Braintree_Util::extractAttributeAsArray(
            $discounts,
            'discount'
        );
    }
}
