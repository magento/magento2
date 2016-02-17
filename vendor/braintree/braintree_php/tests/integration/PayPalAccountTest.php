<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_PayPalAccountTest extends PHPUnit_Framework_TestCase
{
    function testFind()
    {
        $paymentMethodToken = 'PAYPALToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $foundPayPalAccount = Braintree_PayPalAccount::find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
        $this->assertNotNull($foundPayPalAccount->imageUrl);
    }

    function testGatewayFind()
    {
        $paymentMethodToken = 'PAYPALToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $foundPayPalAccount = $gateway->paypalAccount()->find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
        $this->assertNotNull($foundPayPalAccount->imageUrl);
    }

    function testFind_doesNotReturnIncorrectPaymentMethodType()
    {
        $creditCardToken = 'creditCardToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
            'token' => $creditCardToken
        ));
        $this->assertTrue($result->success);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PayPalAccount::find($creditCardToken);
    }

    function test_PayPalAccountExposesTimestamps()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree_Test_Nonces::$paypalFuturePayment
        ));
        $this->assertTrue($result->success);

        $foundPayPalAccount = Braintree_PayPalAccount::find($result->paymentMethod->token);

        $this->assertNotNull($result->paymentMethod->createdAt);
        $this->assertNotNull($result->paymentMethod->updatedAt);
    }

    function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PayPalAccount::find('invalid-token');
    }

    function testFind_throwsUsefulErrorMessagesWhenEmpty()
    {
        $this->setExpectedException('InvalidArgumentException', 'expected paypal account id to be set');
        Braintree_PayPalAccount::find('');
    }

    function testFind_throwsUsefulErrorMessagesWhenInvalid()
    {
        $this->setExpectedException('InvalidArgumentException', '@ is an invalid paypal account token');
        Braintree_PayPalAccount::find('@');
    }

    function testFind_returnsSubscriptionsAssociatedWithAPaypalAccount()
    {
        $customer = Braintree_Customer::createNoValidate();
        $paymentMethodToken = 'paypal-account-' . strval(rand());

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id
        ));
        $this->assertTrue($result->success);

        $token = $result->paymentMethod->token;
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $subscription1 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $subscription2 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $paypalAccount = Braintree_PayPalAccount::find($token);
        $getIds = function($sub) { return $sub->id; };
        $subIds = array_map($getIds, $paypalAccount->subscriptions);
        $this->assertTrue(in_array($subscription1->id, $subIds));
        $this->assertTrue(in_array($subscription2->id, $subIds));
    }

    function testUpdate()
    {
        $originalToken = 'ORIGINAL_PAYPALToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $originalToken
            )
        ));

        $createResult = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($createResult->success);

        $newToken = 'NEW_PAYPALToken-' . strval(rand());
        $updateResult = Braintree_PayPalAccount::update($originalToken, array(
            'token' => $newToken
        ));

        $this->assertTrue($updateResult->success);
        $this->assertEquals($newToken, $updateResult->paypalAccount->token);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PayPalAccount::find($originalToken);

    }

    function testUpdateAndMakeDefault()
    {
        $customer = Braintree_Customer::createNoValidate();

        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ));
        $this->assertTrue($creditCardResult->success);

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE'
            )
        ));

        $createResult = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($createResult->success);

        $updateResult = Braintree_PayPalAccount::update($createResult->paymentMethod->token, array(
            'options' => array('makeDefault' => true)
        ));

        $this->assertTrue($updateResult->success);
        $this->assertTrue($updateResult->paypalAccount->isDefault());
    }

    function testUpdate_handleErrors()
    {
        $customer = Braintree_Customer::createNoValidate();

        $firstToken = 'FIRST_PAYPALToken-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $firstNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $firstToken
            )
        ));
        $firstPaypalAccount = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $firstNonce
        ));
        $this->assertTrue($firstPaypalAccount->success);

        $secondToken = 'SECOND_PAYPALToken-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $secondNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $secondToken
            )
        ));
        $secondPaypalAccount = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $secondNonce
        ));
        $this->assertTrue($secondPaypalAccount->success);

        $updateResult = Braintree_PayPalAccount::update($firstToken, array(
            'token' => $secondToken
        ));

        $this->assertFalse($updateResult->success);
        $errors = $updateResult->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_TOKEN_IS_IN_USE, $errors[0]->code);
    }

    function testDelete()
    {
        $paymentMethodToken = 'PAYPALToken-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        Braintree_PayPalAccount::delete($paymentMethodToken);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PayPalAccount::find($paymentMethodToken);
    }

    function testSale_createsASaleUsingGivenToken()
    {
        $nonce = Braintree_Test_Nonces::$paypalFuturePayment;
        $customer = Braintree_Customer::createNoValidate(array(
            'paymentMethodNonce' => $nonce
        ));
        $paypalAccount = $customer->paypalAccounts[0];

        $result = Braintree_PayPalAccount::sale($paypalAccount->token, array(
            'amount' => '100.00'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($paypalAccount->token, $result->transaction->paypalDetails->token);
    }
}
