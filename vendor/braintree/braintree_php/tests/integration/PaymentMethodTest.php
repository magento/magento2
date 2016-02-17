<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_PaymentMethodTest extends PHPUnit_Framework_TestCase
{
    function testCreate_fromVaultedCreditCardNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099'
            ),
            'share' => true
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertNotNull($result->paymentMethod->imageUrl);
    }

    function testGatewayCreate_fromVaultedCreditCardNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099'
            ),
            'share' => true
        ));

        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $result = $gateway->paymentMethod()->create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
        $this->assertNotNull($result->paymentMethod->imageUrl);
    }

    function testCreate_fromFakeApplePayNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree_Test_Nonces::$applePayVisa
        ));

        $this->assertTrue($result->success);
        $applePayCard = $result->paymentMethod;
        $this->assertNotNull($applePayCard->token);
        $this->assertSame(Braintree_ApplePayCard::VISA, $applePayCard->cardType);
        $this->assertContains("Visa ", $applePayCard->paymentInstrumentName);
        $this->assertTrue($applePayCard->default);
        $this->assertContains('apple_pay', $applePayCard->imageUrl);
        $this->assertTrue(intval($applePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($applePayCard->expirationYear) > 0);
    }

    function testCreate_fromUnvalidatedCreditCardNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => array(
                    'validate' => false
                )
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('411111', $result->paymentMethod->bin);
        $this->assertSame('1111', $result->paymentMethod->last4);
        $this->assertNotNull($result->paymentMethod->token);
    }

    function testCreate_fromUnvalidatedFuturePaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertSame('jane.doe@example.com', $result->paymentMethod->email);
        $this->assertSame($paymentMethodToken, $result->paymentMethod->token);
    }

    function testCreate_fromAbstractPaymentMethodNonce()
    {
        $customer = Braintree_Customer::createNoValidate();

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree_Test_Nonces::$abstractTransactable
        ));

        $this->assertTrue($result->success);
        $this->assertNotNull($result->paymentMethod->token);
    }

    function testCreate_doesNotWorkForUnvalidatedOnetimePaypalAccountNonce()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
    }

    function testCreate_handlesValidationErrorsForPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CONSENT_CODE_OR_ACCESS_TOKEN_IS_REQUIRED, $errors[1]->code);
    }

    function testCreate_allowsPassingDefaultOptionWithNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $card1 = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Cardholder',
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $this->assertTrue($card1->isDefault());

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => array(
                    'validate' => false
                )
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'makeDefault' => true
            )
        ));

        $card2 = $result->paymentMethod;
        $card1 = Braintree_CreditCard::find($card1->token);
        $this->assertFalse($card1->isDefault());
        $this->assertTrue($card2->isDefault());
    }

    function testCreate_overridesNonceToken()
    {
        $customer = Braintree_Customer::createNoValidate();
        $firstToken = 'FIRST_TOKEN-' . strval(rand());
        $secondToken = 'SECOND_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'token' => $firstToken,
                'number' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
                'options' => array(
                    'validate' => false
                )
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $secondToken
        ));

        $card = $result->paymentMethod;
        $this->assertEquals($secondToken, $card->token);
    }

    function testCreate_respectsVerifyCardAndVerificationMerchantAccountIdWhenIncludedOutsideOfTheNonce()
    {
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4000111111111115',
                'expirationMonth' => '11',
                'expirationYear' => '2099',
            )
        ));
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => array(
                'verifyCard' => 'true',
                'verificationMerchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId()
            )
        ));

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Result_CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals(Braintree_TestHelper::nonDefaultMerchantAccountId(), $result->creditCardVerification->merchantAccountId);
    }

    function testCreate_respectsFailOnDuplicatePaymentMethodWhenIncludedOutsideTheNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($result->success);

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => "05/2012"
            )
        ));
        $updateResult = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => array(
                'failOnDuplicatePaymentMethod' => 'true',
            )
        ));

        $this->assertFalse($updateResult->success);
        $resultErrors = $updateResult->errors->deepAll();
        $this->assertEquals("81724", $resultErrors[0]->code);
    }

    function testCreate_allowsPassingABillingAddressOutsideOfTheNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => array(
                    'validate' => false
                )
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => array(
                'streetAddress' => '123 Abc Way'
            )
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod,'Braintree_CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = Braintree_CreditCard::find($token);
        $this->assertTrue(NULL != $foundCreditCard);
        $this->assertEquals('123 Abc Way', $foundCreditCard->billingAddress->streetAddress);
    }

    function testCreate_overridesTheBillingAddressInTheNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => array(
                    'validate' => false
                ),
                'billingAddress' => array(
                    'streetAddress' => '456 Xyz Way'
                )
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => array(
                'streetAddress' => '123 Abc Way'
            )
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod,'Braintree_CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = Braintree_CreditCard::find($token);
        $this->assertTrue(NULL != $foundCreditCard);
        $this->assertEquals('123 Abc Way', $foundCreditCard->billingAddress->streetAddress);
    }

    function testCreate_doesNotOverrideTheBillingAddressForAVaultedCreditCard()
    {
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'customerId' => $customer->id,
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'billingAddress' => array(
                    'streetAddress' => '456 Xyz Way'
                )
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => array(
                'streetAddress' => '123 Abc Way'
            )
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod,'Braintree_CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = Braintree_CreditCard::find($token);
        $this->assertTrue(NULL != $foundCreditCard);
        $this->assertEquals('456 Xyz Way', $foundCreditCard->billingAddress->streetAddress);
    }

    function testCreate_allowsPassingABillingAddressIdOutsideOfTheNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            'credit_card' => array(
                'number' => '4111111111111111',
                'expirationMonth' => '12',
                'expirationYear' => '2020',
                'options' => array(
                    'validate' => false
                )
            )
        ));

        $address = Braintree_Address::create(array(
            'customerId' => $customer->id,
            'firstName' => 'Bobby',
            'lastName' => 'Tables'
        ))->address;
        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddressId' => $address->id
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod,'Braintree_CreditCard'));
        $token = $result->paymentMethod->token;

        $foundCreditCard = Braintree_CreditCard::find($token);
        $this->assertTrue(NULL != $foundCreditCard);
        $this->assertEquals('Bobby', $foundCreditCard->billingAddress->firstName);
        $this->assertEquals('Tables', $foundCreditCard->billingAddress->lastName);
    }

    function testCreate_doesNotReturnAnErrorIfCreditCardOptionsArePresentForAPaypalNonce()
    {
        $customer = Braintree_Customer::createNoValidate();
        $originalToken = 'paypal-account-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'consent-code',
                'token' => $originalToken
            )
        ));

        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'options' => array(
                'verifyCard' => 'true',
                'failOnDuplicatePaymentMethod' => 'true',
                'verificationMerchantAccountId' => 'Not a Real Merchant Account Id'
            )
        ));

        $this->assertTrue($result->success);
    }

    function testCreate_ignoresPassedBillingAddressParamsForPaypalAccount()
    {
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'PAYPAL_CONSENT_CODE',
            )
        ));
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddress' => array(
                'streetAddress' => '123 Abc Way'
            )
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod,'Braintree_PaypalAccount'));
        $token = $result->paymentMethod->token;

        $foundPaypalAccount = Braintree_PaypalAccount::find($token);
        $this->assertTrue(NULL != $foundPaypalAccount);
    }

    function testCreate_ignoresPassedBillingAddressIdForPaypalAccount()
    {
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPaypalAccount(array(
            'paypalAccount' => array(
                'consentCode' => 'PAYPAL_CONSENT_CODE',
            )
        ));
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id,
            'billingAddressId' => 'address_id'
        ));

        $this->assertTrue($result->success);
        $this->assertTrue(is_a($result->paymentMethod,'Braintree_PaypalAccount'));
        $token = $result->paymentMethod->token;

        $foundPaypalAccount = Braintree_PaypalAccount::find($token);
        $this->assertTrue(NULL != $foundPaypalAccount);
    }

    function testCreate_acceptsNumberAndOtherCreditCardParameters()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree_Test_Nonces::$transactable,
            'cardholderName' => 'Jane Doe',
            'cvv' => '123',
            'expirationMonth' => '10',
            'expirationYear' => '24',
            'number' => '4242424242424242'
        ));

        $this->assertTrue($result->success);
        $this->assertTrue('Jane Doe' == $result->paymentMethod->cardholderName);
        $this->assertTrue('10' == $result->paymentMethod->expirationMonth);
        $this->assertTrue('2024' == $result->paymentMethod->expirationYear);
        $this->assertTrue('424242' == $result->paymentMethod->bin);
        $this->assertTrue('4242' == $result->paymentMethod->last4);
    }

    function testFind_returnsCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $paymentMethodToken
        ));
        $this->assertTrue($creditCardResult->success);

        $foundCreditCard = Braintree_PaymentMethod::find($creditCardResult->creditCard->token);

        $this->assertEquals($paymentMethodToken, $foundCreditCard->token);
        $this->assertEquals('510510', $foundCreditCard->bin);
        $this->assertEquals('5100', $foundCreditCard->last4);
        $this->assertEquals('05/2011', $foundCreditCard->expirationDate);
    }

    function testFind_returnsCreditCardsWithSubscriptions()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
        ));
        $this->assertTrue($creditCardResult->success);

        $subscriptionId = strval(rand());
        Braintree_Subscription::create(array(
            'id' => $subscriptionId,
            'paymentMethodToken' => $creditCardResult->creditCard->token,
            'planId' => 'integration_trialless_plan',
            'price' => '1.00'
        ));

        $foundCreditCard = Braintree_PaymentMethod::find($creditCardResult->creditCard->token);
        $this->assertEquals($subscriptionId, $foundCreditCard->subscriptions[0]->id);
        $this->assertEquals('integration_trialless_plan', $foundCreditCard->subscriptions[0]->planId);
        $this->assertEquals('1.00', $foundCreditCard->subscriptions[0]->price);
    }

    function testFind_returnsPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
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

        $foundPayPalAccount = Braintree_PaymentMethod::find($paymentMethodToken);

        $this->assertSame('jane.doe@example.com', $foundPayPalAccount->email);
        $this->assertSame($paymentMethodToken, $foundPayPalAccount->token);
    }

    function testFind_returnsApplePayCards()
    {
        $paymentMethodToken = 'APPLE_PAY-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_Test_Nonces::$applePayVisa;
        Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $paymentMethodToken
        ));

        $foundApplePayCard = Braintree_PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundApplePayCard->token);
        $this->assertInstanceOf('Braintree_ApplePayCard', $foundApplePayCard);
        $this->assertTrue(intval($foundApplePayCard->expirationMonth) > 0);
        $this->assertTrue(intval($foundApplePayCard->expirationYear) > 0);
    }

    function testFind_returnsCoinbaseAccounts()
    {
        $customer = Braintree_Customer::createNoValidate();
        $result = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree_Test_Nonces::$coinbase
        ));

        $this->assertTrue($result->success);
        $coinbaseAccount = $result->paymentMethod;
        $this->assertNotNull($coinbaseAccount->token);
        $foundCoinbaseAccount = Braintree_PaymentMethod::find($coinbaseAccount->token);
        $this->assertInstanceOf('Braintree_CoinbaseAccount', $foundCoinbaseAccount);
        $this->assertNotNull($foundCoinbaseAccount->userId);
        $this->assertNotNull($foundCoinbaseAccount->userName);
        $this->assertNotNull($foundCoinbaseAccount->userEmail);
    }


    function testFind_returnsAbstractPaymentMethods()
    {
        $paymentMethodToken = 'ABSTRACT-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $nonce = Braintree_test_Nonces::$abstractTransactable;
        Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce,
            'token' => $paymentMethodToken
        ));

        $foundPaymentMethod = Braintree_PaymentMethod::find($paymentMethodToken);

        $this->assertSame($paymentMethodToken, $foundPaymentMethod-> token);
    }

    function testFind_throwsIfCannotBeFound()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find('NON_EXISTENT_TOKEN');
    }

    function testUpdate_updatesTheCreditCard()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree_PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'cvv' => '456',
            'number' => Braintree_Test_CreditCardNumbers::$masterCard,
            'expirationDate' => "06/2013"
        ));

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("New Holder", $updatedCreditCard->cardholderName);
        $this->assertSame(substr(Braintree_Test_CreditCardNumbers::$masterCard, 0, 6), $updatedCreditCard->bin);
        $this->assertSame(substr(Braintree_Test_CreditCardNumbers::$masterCard, -4), $updatedCreditCard->last4);
        $this->assertSame("06/2013", $updatedCreditCard->expirationDate);
    }

    function testUpdate_createsANewBillingAddressByDefault()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree_PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL'
            )
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("IL", $updatedCreditCard->billingAddress->region);
        $this->assertSame(NULL, $updatedCreditCard->billingAddress->streetAddress);
        $this->assertFalse($creditCard->billingAddress->id == $updatedCreditCard->billingAddress->id);
    }

    function testUpdate_updatesTheBillingAddressIfOptionIsSpecified()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree_PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'region' => 'IL',
                'options' => array(
                    'updateExisting' => 'true'
                )
            ),
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("IL", $updatedCreditCard->billingAddress->region);
        $this->assertSame("123 Nigeria Ave", $updatedCreditCard->billingAddress->streetAddress);
        $this->assertTrue($creditCard->billingAddress->id == $updatedCreditCard->billingAddress->id);
    }

    function testUpdate_updatesTheCountryViaCodes()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012",
            'billingAddress' => array(
                'streetAddress' => '123 Nigeria Ave'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree_PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'countryName' => 'American Samoa',
                'countryCodeAlpha2' => 'AS',
                'countryCodeAlpha3' => 'ASM',
                'countryCodeNumeric' => '016',
                'options' => array(
                    'updateExisting' => 'true'
                )
            ),
        ));

        $this->assertTrue($updateResult->success);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("American Samoa", $updatedCreditCard->billingAddress->countryName);
        $this->assertSame("AS", $updatedCreditCard->billingAddress->countryCodeAlpha2);
        $this->assertSame("ASM", $updatedCreditCard->billingAddress->countryCodeAlpha3);
        $this->assertSame("016", $updatedCreditCard->billingAddress->countryCodeNumeric);
    }

    function testUpdate_canPassExpirationMonthAndExpirationYear()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree_PaymentMethod::update($creditCard->token, array(
            'number' => Braintree_Test_CreditCardNumbers::$masterCard,
            'expirationMonth' => "07",
            'expirationYear' => "2011"
        ));

        $this->assertTrue($updateResult->success);
        $this->assertSame($updateResult->paymentMethod->token, $creditCard->token);
        $updatedCreditCard = $updateResult->paymentMethod;
        $this->assertSame("07", $updatedCreditCard->expirationMonth);
        $this->assertSame("07", $updatedCreditCard->expirationMonth);
        $this->assertSame("07/2011", $updatedCreditCard->expirationDate);
    }

    function testUpdate_verifiesTheUpdateIfOptionsVerifyCardIsTrue()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree_PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'cvv' => '456',
            'number' => Braintree_Test_CreditCardNumbers::$failsSandboxVerification['MasterCard'],
            'expirationDate' => "06/2013",
            'options' => array(
                'verifyCard' => 'true'
            )
        ));

        $this->assertFalse($updateResult->success);
        $this->assertEquals(Braintree_Result_CreditCardVerification::PROCESSOR_DECLINED, $updateResult->creditCardVerification->status);
        $this->assertEquals(NULL, $updateResult->creditCardVerification->gatewayRejectionReason);
    }

    function testUpdate_canUpdateTheBillingAddress()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'cvv' => '123',
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => '05/2012',
            'billingAddress' => array(
                'firstName' => 'Old First Name',
                'lastName' => 'Old Last Name',
                'company' => 'Old Company',
                'streetAddress' => '123 Old St',
                'extendedAddress' => 'Apt Old',
                'locality' => 'Old City',
                'region' => 'Old State',
                'postalCode' => '12345',
                'countryName' => 'Canada'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree_PaymentMethod::update($creditCard->token, array(
            'billingAddress' => array(
                'firstName' => 'New First Name',
                'lastName' => 'New Last Name',
                'company' => 'New Company',
                'streetAddress' => '123 New St',
                'extendedAddress' => 'Apt New',
                'locality' => 'New City',
                'region' => 'New State',
                'postalCode' => '56789',
                'countryName' => 'United States of America'
            )
        ));

        $this->assertTrue($updateResult->success);
        $address = $updateResult->paymentMethod->billingAddress;
        $this->assertSame('New First Name', $address->firstName);
        $this->assertSame('New Last Name', $address->lastName);
        $this->assertSame('New Company', $address->company);
        $this->assertSame('123 New St', $address->streetAddress);
        $this->assertSame('Apt New', $address->extendedAddress);
        $this->assertSame('New City', $address->locality);
        $this->assertSame('New State', $address->region);
        $this->assertSame('56789', $address->postalCode);
        $this->assertSame('United States of America', $address->countryName);
    }

    function testUpdate_returnsAnErrorIfInvalid()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'cardholderName' => 'Original Holder',
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2012"
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $updateResult = Braintree_PaymentMethod::update($creditCard->token, array(
            'cardholderName' => 'New Holder',
            'number' => 'invalid',
            'expirationDate' => "05/2014",
        ));

        $this->assertFalse($updateResult->success);
        $numberErrors = $updateResult->errors->forKey('creditCard')->onAttribute('number');
        $this->assertEquals("Credit card number must be 12-19 digits.", $numberErrors[0]->message);
    }

    function testUpdate_canUpdateTheDefault()
    {
        $customer = Braintree_Customer::createNoValidate();

        $creditCardResult1 = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2009"
        ));
        $this->assertTrue($creditCardResult1->success);
        $creditCard1 = $creditCardResult1->creditCard;

        $creditCardResult2 = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2009"
        ));
        $this->assertTrue($creditCardResult2->success);
        $creditCard2 = $creditCardResult2->creditCard;

        $this->assertTrue($creditCard1->default);
        $this->assertFalse($creditCard2->default);


        $updateResult = Braintree_PaymentMethod::update($creditCard2->token, array(
            'options' => array(
                'makeDefault' => 'true'
            )
        ));
        $this->assertTrue($updateResult->success);

        $this->assertFalse(Braintree_PaymentMethod::find($creditCard1->token)->default);
        $this->assertTrue(Braintree_PaymentMethod::find($creditCard2->token)->default);
    }

    function testUpdate_updatesAPaypalAccountsToken()
    {
        $customer = Braintree_Customer::createNoValidate();
        $originalToken = 'paypal-account-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token' => $originalToken
            )
        ));

        $originalResult = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id
        ));
        $this->assertTrue($originalResult->success);

        $originalPaypalAccount = $originalResult->paymentMethod;

        $updatedToken = 'UPDATED_TOKEN-' . strval(rand());
        $updateResult = Braintree_PaymentMethod::update($originalPaypalAccount->token, array(
            'token' => $updatedToken
        ));
        $this->assertTrue($updateResult->success);

        $updatedPaypalAccount = Braintree_PaymentMethod::find($updatedToken);
        $this->assertEquals($originalPaypalAccount->email, $updatedPaypalAccount->email);

        $this->setExpectedException('Braintree_Exception_NotFound', 'payment method with token ' . $originalToken . ' not found');
        Braintree_PaymentMethod::find($originalToken);

    }

    function testUpdate_canMakeAPaypalAccountTheDefaultPaymentMethod()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => Braintree_Test_CreditCardNumbers::$visa,
            'expirationDate' => "05/2009",
            'options' => array(
                'makeDefault' => 'true'
            )
        ));
        $this->assertTrue($creditCardResult->success);
        $creditCard = $creditCardResult->creditCard;

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
            )
        ));

        $originalToken = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $nonce,
            'customerId' => $customer->id
        ))->paymentMethod->token;

        $updateResult = Braintree_PaymentMethod::update($originalToken, array(
            'options' => array(
                'makeDefault' => 'true'
            )
        ));
        $this->assertTrue($updateResult->success);

        $updatedPaypalAccount = Braintree_PaymentMethod::find($originalToken);
        $this->assertTrue($updatedPaypalAccount->default);

    }

    function testUpdate_returnsAnErrorIfATokenForAccountIsUsedToAttemptAnUpdate()
    {
        $customer = Braintree_Customer::createNoValidate();
        $firstToken = 'paypal-account-' . strval(rand());
        $secondToken = 'paypal-account-' . strval(rand());

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $firstNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token' => $firstToken
            )
        ));
        $firstResult = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $firstNonce,
            'customerId' => $customer->id
        ));
        $this->assertTrue($firstResult->success);
        $firstPaypalAccount = $firstResult->paymentMethod;

        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $secondNonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'consent-code',
                'token' => $secondToken
            )
        ));
        $secondResult = Braintree_PaymentMethod::create(array(
            'paymentMethodNonce' => $secondNonce,
            'customerId' => $customer->id
        ));
        $this->assertTrue($secondResult->success);
        $secondPaypalAccount = $firstResult->paymentMethod;


        $updateResult = Braintree_PaymentMethod::update($firstToken, array(
            'token' => $secondToken
        ));

        $this->assertFalse($updateResult->success);
        $resultErrors = $updateResult->errors->deepAll();
        $this->assertEquals("92906", $resultErrors[0]->code);

    }

    function testDelete_worksWithCreditCards()
    {
        $paymentMethodToken = 'CREDIT_CARD_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $creditCardResult = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'number' => '5105105105105100',
            'expirationDate' => '05/2011',
            'token' => $paymentMethodToken
        ));
        $this->assertTrue($creditCardResult->success);

        Braintree_PaymentMethod::delete($paymentMethodToken);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
        integrationMerchantConfig();
    }

    function testDelete_worksWithPayPalAccounts()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Braintree_Customer::createNoValidate();
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $paypalAccountResult = Braintree_PaymentMethod::create(array(
            'customerId' => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($paypalAccountResult->success);

        Braintree_PaymentMethod::delete($paymentMethodToken);

        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
    }

}
