<?php
class Braintree_PlanGateway
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
        $path = $this->_config->merchantPath() . '/plans';
        $response = $this->_http->get($path);
        if (key_exists('plans', $response)){
            $plans = array("plan" => $response['plans']);
        } else {
            $plans = array("plan" => array());
        }

        return Braintree_Util::extractAttributeAsArray(
            $plans,
            'plan'
        );
    }
}
