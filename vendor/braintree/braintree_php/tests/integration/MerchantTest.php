<?php
require_once __DIR__ . '/../TestHelper.php';

class Braintree_MerchantTest extends PHPUnit_Framework_TestCase
{
    function testCreateMerchant()
    {
        $gateway = new Braintree_Gateway(array(
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ));
        $result = $gateway->merchant()->create(array(
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
        ));

        $this->assertEquals(true, $result->success);
        $merchant = $result->merchant;
        $this->assertNotNull($merchant->id);
        $credentials = $result->credentials;
        $this->assertNotNull($credentials->accessToken);
    }

    /**
    * @expectedException Braintree_Exception_Configuration
    * @expectedExceptionMessage clientId needs to be set.
    */
    function testAssertsHasCredentials()
    {
        $gateway = new Braintree_Gateway(array(
            'clientSecret' => 'client_secret$development$integration_client_secret'
        ));
        $gateway->merchant()->create(array(
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
        ));
    }
}
