<?php
class Braintree_CreditCardVerificationGateway
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

    public function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = Braintree_CreditCardVerificationSearch::ids()->in($ids)->toparam();
        $path = $this->_config->merchantPath() . '/verifications/advanced_search';
        $response = $this->_http->post($path, array('search' => $criteria));

        return Braintree_Util::extractattributeasarray(
            $response['creditCardVerifications'],
            'verification'
        );
    }

    public function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $path = $this->_config->merchantPath() . '/verifications/advanced_search_ids';
        $response = $this->_http->post($path, array('search' => $criteria));
        $pager = array(
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => array($query)
            );

        return new Braintree_ResourceCollection($response, $pager);
    }
}
