<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_ClientTokenTest extends PHPUnit_Framework_TestCase
{
    function test_ClientTokenAuthorizesRequest()
    {
        $clientToken = Braintree_TestHelper::decodedClientToken();
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $response = $http->get_cards(array(
            "authorization_fingerprint" => $authorizationFingerprint,
            "shared_customer_identifier" => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        ));

        $this->assertEquals(200, $response["status"]);
    }

    function test_VersionOptionSupported()
    {
        $clientToken = Braintree_ClientToken::generate(array("version" => 1));
        $version = json_decode($clientToken)->version;
        $this->assertEquals(1, $version);
    }

    function test_VersionDefaultsToTwo()
    {
        $encodedClientToken = Braintree_ClientToken::generate();
        $clientToken = base64_decode($encodedClientToken);
        $version = json_decode($clientToken)->version;
        $this->assertEquals(2, $version);
    }

    function testGateway_VersionDefaultsToTwo()
    {
        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $encodedClientToken = $gateway->clientToken()->generate();
        $clientToken = base64_decode($encodedClientToken);
        $version = json_decode($clientToken)->version;
        $this->assertEquals(2, $version);
    }

    function test_GatewayRespectsVerifyCard()
    {
        $result = Braintree_Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $clientToken = Braintree_TestHelper::decodedClientToken(array(
            "customerId" => $customerId,
            "options" => array(
                "verifyCard" => true
            )
        ));
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $response = $http->post('/client_api/v1/payment_methods/credit_cards.json', json_encode(array(
            "credit_card" => array(
                "number" => "4000111111111115",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $authorizationFingerprint,
            "shared_customer_identifier" => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        )));

        $this->assertEquals(422, $response["status"]);
    }

    function test_GatewayRespectsFailOnDuplicatePaymentMethod()
    {
        $result = Braintree_Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $clientToken = Braintree_TestHelper::decodedClientToken(array(
            "customerId" => $customerId,
        ));
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $response = $http->post('/client_api/v1/payment_methods/credit_cards.json', json_encode(array(
            "credit_card" => array(
                "number" => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $authorizationFingerprint,
            "shared_customer_identifier" => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        )));
        $this->assertEquals(201, $response["status"]);

        $clientToken = Braintree_TestHelper::decodedClientToken(array(
            "customerId" => $customerId,
            "options" => array(
                "failOnDuplicatePaymentMethod" => true
            )
        ));
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $response = $http->post('/client_api/v1/payment_methods/credit_cards.json', json_encode(array(
            "credit_card" => array(
                "number" => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $authorizationFingerprint,
            "shared_customer_identifier" => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        )));
        $this->assertEquals(422, $response["status"]);
    }

    function test_GatewayRespectsMakeDefault()
    {
        $result = Braintree_Customer::create();
        $this->assertTrue($result->success);
        $customerId = $result->customer->id;

        $result = Braintree_CreditCard::create(array(
            'customerId' => $customerId,
            'number' => '4111111111111111',
            'expirationDate' => '11/2099'
        ));
        $this->assertTrue($result->success);

        $clientToken = Braintree_TestHelper::decodedClientToken(array(
            "customerId" => $customerId,
            "options" => array(
                "makeDefault" => true
            )
        ));
        $authorizationFingerprint = json_decode($clientToken)->authorizationFingerprint;

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $response = $http->post('/client_api/v1/payment_methods/credit_cards.json', json_encode(array(
            "credit_card" => array(
                "number" => "4242424242424242",
                "expirationDate" => "11/2099"
            ),
            "authorization_fingerprint" => $authorizationFingerprint,
            "shared_customer_identifier" => "fake_identifier",
            "shared_customer_identifier_type" => "testing"
        )));

        $this->assertEquals(201, $response["status"]);

        $customer = Braintree_Customer::find($customerId);
        $this->assertEquals(2, count($customer->creditCards));
        foreach ($customer->creditCards as $creditCard) {
            if ($creditCard->last4 == "4242") {
                $this->assertTrue($creditCard->default);
            }
        }
    }

    function test_ClientTokenAcceptsMerchantAccountId()
    {
        $clientToken = Braintree_TestHelper::decodedClientToken(array(
            'merchantAccountId' => 'my_merchant_account'
        ));
        $merchantAccountId = json_decode($clientToken)->merchantAccountId;

        $this->assertEquals('my_merchant_account', $merchantAccountId);
    }

    function test_GenerateRaisesExceptionOnGateway422()
    {
        $this->setExpectedException('InvalidArgumentException', 'customer_id');

        Braintree_ClientToken::generate(array(
            "customerId" => "not_a_customer"
        ));
    }
}
