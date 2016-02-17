<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_HttpTest extends PHPUnit_Framework_TestCase
{
    function testProductionSSL()
    {
        try {
            Braintree_Configuration::environment('production');
            $this->setExpectedException('Braintree_Exception_Authentication');
            $http = new Braintree_Http(Braintree_Configuration::$global);
            $http->get('/');
        } catch (Exception $e) {
            Braintree_Configuration::environment('development');
            throw $e;
        }
        Braintree_Configuration::environment('development');
    }

    function testSandboxSSL()
    {
        try {
            Braintree_Configuration::environment('sandbox');
            $this->setExpectedException('Braintree_Exception_Authentication');
            $http = new Braintree_Http(Braintree_Configuration::$global);
            $http->get('/');
        } catch (Exception $e) {
            Braintree_Configuration::environment('development');
            throw $e;
        }
        Braintree_Configuration::environment('development');
    }

    function testSslError()
    {
        try {
            Braintree_Configuration::environment('sandbox');
            $this->setExpectedException('Braintree_Exception_SSLCertificate');
            $http = new Braintree_Http(Braintree_Configuration::$global);
            //ip address of api.braintreegateway.com
            $http->_doUrlRequest('get', '204.109.13.121');
        } catch (Exception $e) {
            Braintree_Configuration::environment('development');
            throw $e;
        }
        Braintree_Configuration::environment('development');
    }

    function testAuthorizationWithConfig()
    {
        $config = new Braintree_Configuration(array(
            'environment' => 'development',
            'merchant_id' => 'integration_merchant_id',
            'publicKey' => 'badPublicKey',
            'privateKey' => 'badPrivateKey'
        ));

        $http = new Braintree_Http($config);
        $result = $http->_doUrlRequest('GET', $config->baseUrl() . '/merchants/integration_merchant_id/customers');
        $this->assertEquals(401, $result['status']);
    }
}
