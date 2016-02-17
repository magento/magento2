<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_CustomerTest extends PHPUnit_Framework_TestCase
{
    function testAll_smokeTest()
    {
        $all = Braintree_Customer::all();
        $this->assertTrue($all->maximumCount() > 0);
    }

    function testAllWithManyResults()
    {
        $collection = Braintree_Customer::all();
        $this->assertTrue($collection->maximumCount() > 1);
        $customer = $collection->firstItem();
        $this->assertTrue(intval($customer->id) > 0);
        $this->assertTrue($customer instanceof Braintree_Customer);
    }

    function testCreate()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com'
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals('Mike', $customer->firstName);
        $this->assertEquals('Jones', $customer->lastName);
        $this->assertEquals('Jones Co.', $customer->company);
        $this->assertEquals('mike.jones@example.com', $customer->email);
        $this->assertEquals('419.555.1234', $customer->phone);
        $this->assertEquals('419.555.1235', $customer->fax);
        $this->assertEquals('http://example.com', $customer->website);
        $this->assertNotNull($customer->merchantId);
    }

    function testGatewayCreate()
    {
        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $result = $gateway->customer()->create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals('Mike', $customer->firstName);
        $this->assertEquals('Jones', $customer->lastName);
        $this->assertNotNull($customer->merchantId);
    }

    function testCreateWithAccessToken()
    {
        $credentials = Braintree_OAuthTestHelper::createCredentials(array(
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'merchantId' => 'integration_merchant_id',
        ));

        $gateway = new Braintree_Gateway(array(
            'accessToken' => $credentials->accessToken,
        ));

        $result = $gateway->customer()->create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals('Mike', $customer->firstName);
        $this->assertEquals('Jones', $customer->lastName);
        $this->assertNotNull($customer->merchantId);
    }

    function testCreateCustomerWithCardUsingNonce()
    {
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number" => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            ),
            "share" => true
        ));

        $result = Braintree_Customer::create(array(
            'creditCard' => array(
                'paymentMethodNonce' => $nonce
            )
        ));

        $this->assertTrue($result->success);
        $this->assertSame("411111", $result->customer->creditCards[0]->bin);
        $this->assertSame("1111", $result->customer->creditCards[0]->last4);
    }

    function testCreateCustomerWithApplePayCard()
    {
        $nonce = Braintree_Test_Nonces::$applePayVisa;
        $result = Braintree_Customer::create(array(
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($result->success);
        $customer = $result->customer;
        $this->assertNotNull($customer->applePayCards[0]);
        $this->assertNotNull($customer->paymentMethods()[0]);
    }

    function testCreateCustomerWithCoinbase()
    {
        $nonce = Braintree_Test_Nonces::$coinbase;
        $result = Braintree_Customer::create(array(
            'paymentMethodNonce' => $nonce
        ));
        $this->assertTrue($result->success);
        $customer = $result->customer;
        $this->assertNotNull($customer->coinbaseAccounts[0]);
        $this->assertNotNull($customer->coinbaseAccounts[0]->userId);
        $this->assertNotNull($customer->coinbaseAccounts[0]->userName);
        $this->assertNotNull($customer->coinbaseAccounts[0]->userEmail);
        $this->assertNotNull($customer->paymentMethods()[0]);
    }


    function testCreate_withUnicode()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => "Здравствуйте",
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com'
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals("Здравствуйте", $customer->firstName);
        $this->assertEquals('Jones', $customer->lastName);
        $this->assertEquals('Jones Co.', $customer->company);
        $this->assertEquals('mike.jones@example.com', $customer->email);
        $this->assertEquals('419.555.1234', $customer->phone);
        $this->assertEquals('419.555.1235', $customer->fax);
        $this->assertEquals('http://example.com', $customer->website);
        $this->assertNotNull($customer->merchantId);
    }

    function testCreate_withCountry()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Bat',
            'lastName' => 'Manderson',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'billingAddress' => array(
                   'countryName' => 'Gabon',
                   'countryCodeAlpha2' => 'GA',
                   'countryCodeAlpha3' => 'GAB',
                   'countryCodeNumeric' => '266'
                )
            )
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals('Gabon', $customer->creditCards[0]->billingAddress->countryName);
        $this->assertEquals('GA', $customer->creditCards[0]->billingAddress->countryCodeAlpha2);
        $this->assertEquals('GAB', $customer->creditCards[0]->billingAddress->countryCodeAlpha3);
        $this->assertEquals('266', $customer->creditCards[0]->billingAddress->countryCodeNumeric);
        $this->assertEquals(1, preg_match('/\A\w{32}\z/', $customer->creditCards[0]->uniqueNumberIdentifier));
    }

    function testCreate_withVenmoSdkSession()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Bat',
            'lastName' => 'Manderson',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'options' => array(
                    'venmoSdkSession' => Braintree_Test_VenmoSdk::getTestSession()
                )
            )
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals(true, $customer->creditCards[0]->venmoSdk);
    }

    function testCreate_withVenmoSdkPaymentMethodCode()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Bat',
            'lastName' => 'Manderson',
            'creditCard' => array(
                'venmoSdkPaymentMethodCode' => Braintree_Test_VenmoSdk::$visaPaymentMethodCode
            )
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals("411111", $customer->creditCards[0]->bin);
    }

    function testCreate_blankCustomer()
    {
        $result = Braintree_Customer::create();
        $this->assertEquals(true, $result->success);
        $this->assertNotNull($result->customer->id);

        $result = Braintree_Customer::create(array());
        $this->assertEquals(true, $result->success);
        $this->assertNotNull($result->customer->id);
    }

    function testCreate_withSpecialChars()
    {
        $result = Braintree_Customer::create(array('firstName' => '<>&"\''));
        $this->assertEquals(true, $result->success);
        $this->assertEquals('<>&"\'', $result->customer->firstName);
    }

    function testCreate_withCustomFields()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'customFields' => array(
                'store_me' => 'some custom value'
            )
        ));
        $this->assertEquals(true, $result->success);
        $customFields = $result->customer->customFields;
        $this->assertEquals('some custom value', $customFields['store_me']);
    }

    function testCreate_withFraudParams()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '123',
                'cardholderName' => 'Mike Jones',
                'deviceSessionId' => 'abc123',
                'fraudMerchantId' => '456'
            )
        ));
        $this->assertEquals(true, $result->success);
    }

    function testCreate_withCreditCard()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '123',
                'cardholderName' => 'Mike Jones'
            )
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals('Mike', $customer->firstName);
        $this->assertEquals('Jones', $customer->lastName);
        $this->assertEquals('Jones Co.', $customer->company);
        $this->assertEquals('mike.jones@example.com', $customer->email);
        $this->assertEquals('419.555.1234', $customer->phone);
        $this->assertEquals('419.555.1235', $customer->fax);
        $this->assertEquals('http://example.com', $customer->website);
        $creditCard = $customer->creditCards[0];
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('Mike Jones', $creditCard->cardholderName);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('05', $creditCard->expirationMonth);
        $this->assertEquals('2012', $creditCard->expirationYear);
    }

    function testCreate_withDuplicateCardCheck()
    {
        $customer = Braintree_Customer::createNoValidate();

        $attributes = array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '123',
                'cardholderName' => 'Mike Jones',
                'options' => array(
                    'failOnDuplicatePaymentMethod' => true
                )
            )
        );
        Braintree_Customer::create($attributes);
        $result = Braintree_Customer::create($attributes);

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('customer')->forKey('creditCard')->onAttribute('number');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_DUPLICATE_CARD_EXISTS, $errors[0]->code);
        $this->assertEquals(1, preg_match('/Duplicate card exists in the vault\./', $result->message));
    }

    function testCreate_withCreditCardAndSpecificVerificationMerchantAccount()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '123',
                'cardholderName' => 'Mike Jones',
                'options' => array(
                    'verificationMerchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
                    'verifyCard' => true
                )
            )
        ));
        Braintree_TestHelper::assertPrintable($result);
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Result_CreditCardVerification::PROCESSOR_DECLINED, $result->creditCardVerification->status);
        $this->assertEquals('2000', $result->creditCardVerification->processorResponseCode);
        $this->assertEquals('Do Not Honor', $result->creditCardVerification->processorResponseText);
        $this->assertEquals('M', $result->creditCardVerification->cvvResponseCode);
        $this->assertEquals(null, $result->creditCardVerification->avsErrorResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsPostalCodeResponseCode);
        $this->assertEquals('I', $result->creditCardVerification->avsStreetAddressResponseCode);
    }

    function testCreate_withCreditCardAndBillingAddress()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '123',
                'cardholderName' => 'Mike Jones',
                'billingAddress' => array(
                    'firstName' => 'Drew',
                    'lastName' => 'Smith',
                    'company' => 'Smith Co.',
                    'streetAddress' => '1 E Main St',
                    'extendedAddress' => 'Suite 101',
                    'locality' => 'Chicago',
                    'region' => 'IL',
                    'postalCode' => '60622',
                    'countryName' => 'United States of America'
                )
            )
        ));
        Braintree_TestHelper::assertPrintable($result);
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals('Mike', $customer->firstName);
        $this->assertEquals('Jones', $customer->lastName);
        $this->assertEquals('Jones Co.', $customer->company);
        $this->assertEquals('mike.jones@example.com', $customer->email);
        $this->assertEquals('419.555.1234', $customer->phone);
        $this->assertEquals('419.555.1235', $customer->fax);
        $this->assertEquals('http://example.com', $customer->website);
        $creditCard = $customer->creditCards[0];
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('Mike Jones', $creditCard->cardholderName);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('05', $creditCard->expirationMonth);
        $this->assertEquals('2012', $creditCard->expirationYear);
        $address = $customer->addresses[0];
        $this->assertEquals($address, $creditCard->billingAddress);
        $this->assertEquals('Drew', $address->firstName);
        $this->assertEquals('Smith', $address->lastName);
        $this->assertEquals('Smith Co.', $address->company);
        $this->assertEquals('1 E Main St', $address->streetAddress);
        $this->assertEquals('Suite 101', $address->extendedAddress);
        $this->assertEquals('Chicago', $address->locality);
        $this->assertEquals('IL', $address->region);
        $this->assertEquals('60622', $address->postalCode);
        $this->assertEquals('United States of America', $address->countryName);
    }

    function testCreate_withValidationErrors()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => 'invalid',
                'billingAddress' => array(
                    'streetAddress' => str_repeat('x', 256)
                )
            )
        ));
        Braintree_TestHelper::assertPrintable($result);
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('customer')->onAttribute('email');
        $this->assertEquals(Braintree_Error_Codes::CUSTOMER_EMAIL_IS_INVALID, $errors[0]->code);
        $errors = $result->errors->forKey('customer')->forKey('creditCard')->onAttribute('number');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_NUMBER_INVALID_LENGTH, $errors[0]->code);
        $errors = $result->errors->forKey('customer')->forKey('creditCard')->forKey('billingAddress')->onAttribute('streetAddress');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_STREET_ADDRESS_IS_TOO_LONG, $errors[0]->code);
    }

    function testCreate_countryValidations_inconsistency()
    {
        $result = Braintree_Customer::create(array(
            'creditCard' => array(
                'billingAddress' => array(
                    'countryName' => 'Georgia',
                    'countryCodeAlpha2' => 'TF'
                )
            )
        ));
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('customer')->forKey('creditCard')->forKey('billingAddress')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_INCONSISTENT_COUNTRY, $errors[0]->code);
    }

    function testCreateNoValidate_returnsCustomer()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'firstName' => 'Paul',
            'lastName' => 'Martin'
        ));
        $this->assertEquals('Paul', $customer->firstName);
        $this->assertEquals('Martin', $customer->lastName);
    }

    function testCreateNoValidate_throwsIfInvalid()
    {
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        $customer = Braintree_Customer::createNoValidate(array('email' => 'invalid'));
    }

    function testCreate_worksWithFuturePayPalNonce()
    {
        $nonce = Braintree_Test_Nonces::$paypalFuturePayment;

        $result = Braintree_Customer::create(array(
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
    }

    function testCreate_doesNotWorkWithOnetimePayPalNonce()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $result = Braintree_Customer::create(array(
            'paymentMethodNonce' => $nonce
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('customer')->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);
    }

    function testDelete_deletesTheCustomer()
    {
        $result = Braintree_Customer::create(array());
        $this->assertEquals(true, $result->success);
        Braintree_Customer::find($result->customer->id);
        Braintree_Customer::delete($result->customer->id);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_Customer::find($result->customer->id);
    }

    function testFind()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com'
        ));
        $this->assertEquals(true, $result->success);
        $customer = Braintree_Customer::find($result->customer->id);
        $this->assertEquals('Mike', $customer->firstName);
        $this->assertEquals('Jones', $customer->lastName);
        $this->assertEquals('Jones Co.', $customer->company);
        $this->assertEquals('mike.jones@example.com', $customer->email);
        $this->assertEquals('419.555.1234', $customer->phone);
        $this->assertEquals('419.555.1235', $customer->fax);
        $this->assertEquals('http://example.com', $customer->website);
    }

    function testFind_throwsExceptionIfNotFound()
    {
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_Customer::find("does-not-exist");
    }

    function testUpdate()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Old First',
            'lastName' => 'Old Last',
            'company' => 'Old Company',
            'email' => 'old.email@example.com',
            'phone' => 'old phone',
            'fax' => 'old fax',
            'website' => 'http://old.example.com'
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $updateResult = Braintree_Customer::update($customer->id, array(
            'firstName' => 'New First',
            'lastName' => 'New Last',
            'company' => 'New Company',
            'email' => 'new.email@example.com',
            'phone' => 'new phone',
            'fax' => 'new fax',
            'website' => 'http://new.example.com'
        ));
        $this->assertEquals(true, $updateResult->success);
        $this->assertEquals('New First', $updateResult->customer->firstName);
        $this->assertEquals('New Last', $updateResult->customer->lastName);
        $this->assertEquals('New Company', $updateResult->customer->company);
        $this->assertEquals('new.email@example.com', $updateResult->customer->email);
        $this->assertEquals('new phone', $updateResult->customer->phone);
        $this->assertEquals('new fax', $updateResult->customer->fax);
        $this->assertEquals('http://new.example.com', $updateResult->customer->website);
    }

    function testUpdate_withCountry()
    {
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Bat',
            'lastName' => 'Manderson',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'billingAddress' => array(
                    'countryName' => 'United States of America',
                    'countryCodeAlpha2' => 'US',
                    'countryCodeAlpha3' => 'USA',
                    'countryCodeNumeric' => '840'
                )
            )
        ))->customer;

        $result = Braintree_Customer::update($customer->id, array(
            'firstName' => 'Bat',
            'lastName' => 'Manderson',
            'creditCard' => array(
				'options' => array(
					'updateExistingToken' => $customer->creditCards[0]->token
				),
                'billingAddress' => array(
                    'countryName' => 'Gabon',
                    'countryCodeAlpha2' => 'GA',
                    'countryCodeAlpha3' => 'GAB',
                    'countryCodeNumeric' => '266',
                    'options' => array(
                        'updateExisting' => true
                    )
            	)
            )
        ));

        $this->assertEquals(true, $result->success);
        $updatedCustomer = $result->customer;
        $this->assertEquals('Gabon', $updatedCustomer->creditCards[0]->billingAddress->countryName);
        $this->assertEquals('GA', $updatedCustomer->creditCards[0]->billingAddress->countryCodeAlpha2);
        $this->assertEquals('GAB', $updatedCustomer->creditCards[0]->billingAddress->countryCodeAlpha3);
        $this->assertEquals('266', $updatedCustomer->creditCards[0]->billingAddress->countryCodeNumeric);
    }

    function testUpdate_withUpdatingExistingCreditCard()
    {
        $create_result = Braintree_Customer::create(array(
            'firstName' => 'Old First',
            'lastName' => 'Old Last',
            'website' => 'http://old.example.com',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cardholderName' => 'Old Cardholder'
            )
        ));
        $this->assertEquals(true, $create_result->success);
        $customer = $create_result->customer;
        $creditCard = $customer->creditCards[0];
        $result = Braintree_Customer::update($customer->id, array(
            'firstName' => 'New First',
            'lastName' => 'New Last',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '11/14',
                'cardholderName' => 'New Cardholder',
                'options' => array(
                    'updateExistingToken' => $creditCard->token
                )
            )
        ));
        $this->assertEquals(true, $result->success);
        $this->assertEquals('New First', $result->customer->firstName);
        $this->assertEquals('New Last', $result->customer->lastName);
        $this->assertEquals(1, sizeof($result->customer->creditCards));
        $creditCard = $result->customer->creditCards[0];
        $this->assertEquals('411111', $creditCard->bin);
        $this->assertEquals('11/2014', $creditCard->expirationDate);
        $this->assertEquals('New Cardholder', $creditCard->cardholderName);
    }

    function testUpdate_forBillingAddressAndExistingCreditCardAndCustomerDetailsTogether()
    {
        $create_result = Braintree_Customer::create(array(
            'firstName' => 'Old First',
            'lastName' => 'Old Last',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '123',
                'cardholderName' => 'Old Cardholder',
                'billingAddress' => array(
                    'firstName' => 'Drew',
                    'lastName' => 'Smith'
                )
            )
        ));
        $this->assertEquals(true, $create_result->success);
        $customer = $create_result->customer;
        $creditCard = $customer->creditCards[0];
        $result = Braintree_Customer::update($customer->id, array(
            'firstName' => 'New Customer First',
            'lastName' => 'New Customer Last',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '11/14',
                'options' => array(
                    'updateExistingToken' => $creditCard->token
                ),
                'billingAddress' => array(
                    'firstName' => 'New Billing First',
                    'lastName' => 'New Billing Last',
                    'options' => array(
                        'updateExisting' => true
                    )
                )
            )
        ));
        $this->assertEquals(true, $result->success);
        $this->assertEquals('New Customer First', $result->customer->firstName);
        $this->assertEquals('New Customer Last', $result->customer->lastName);
        $this->assertEquals(1, sizeof($result->customer->creditCards));
        $this->assertEquals(1, sizeof($result->customer->addresses));

        $creditCard = $result->customer->creditCards[0];
        $this->assertEquals('411111', $creditCard->bin);
        $this->assertEquals('11/2014', $creditCard->expirationDate);

        $billingAddress = $creditCard->billingAddress;
        $this->assertEquals('New Billing First', $billingAddress->firstName);
        $this->assertEquals('New Billing Last', $billingAddress->lastName);
    }

    function testUpdate_withNewCreditCardAndExistingBillingAddress()
    {
        $customer = Braintree_Customer::create()->customer;
        $address = Braintree_Address::create(array(
            'customerId' => $customer->id,
            'firstName' => 'Dan'
        ))->address;

        $result = Braintree_Customer::update($customer->id, array(
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '11/14',
                'billingAddressId' => $address->id
            )
        ));

        $billingAddress = $result->customer->creditCards[0]->billingAddress;
        $this->assertEquals($address->id, $billingAddress->id);
        $this->assertEquals('Dan', $billingAddress->firstName);
    }

    function testUpdate_worksWithFuturePayPalNonce()
    {
        $customerResult = Braintree_Customer::create(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'options' => array(
                    'makeDefault' => true
                )
            )
        ));
        $paypalAccountToken = 'PAYPALToken-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paypalAccountToken,
                'options' => array(
                    'makeDefault' => true
                )
            )
        ));

        $result = Braintree_Customer::update($customerResult->customer->id, array(
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $this->assertEquals($result->customer->defaultPaymentMethod()->token, $paypalAccountToken);

    }

    function testUpdate_doesNotWorkWithOnetimePayPalNonce()
    {
        $customerResult = Braintree_Customer::create(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'options' => array(
                    'makeDefault' => true
                )
            )
        ));
        $paypalAccountToken = 'PAYPALToken-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token' => $paypalAccountToken,
                'options' => array(
                    'makeDefault' => true
                )
            )
        ));

        $result = Braintree_Customer::update($customerResult->customer->id, array(
            'paymentMethodNonce' => $nonce
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('customer')->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CANNOT_VAULT_ONE_TIME_USE_PAYPAL_ACCOUNT, $errors[0]->code);

    }

    function testUpdateNoValidate()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => 'Old First',
            'lastName' => 'Old Last',
            'company' => 'Old Company',
            'email' => 'old.email@example.com',
            'phone' => 'old phone',
            'fax' => 'old fax',
            'website' => 'http://old.example.com'
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $updated = Braintree_Customer::updateNoValidate($customer->id, array(
            'firstName' => 'New First',
            'lastName' => 'New Last',
            'company' => 'New Company',
            'email' => 'new.email@example.com',
            'phone' => 'new phone',
            'fax' => 'new fax',
            'website' => 'http://new.example.com'
        ));
        $this->assertEquals('New First', $updated->firstName);
        $this->assertEquals('New Last', $updated->lastName);
        $this->assertEquals('New Company', $updated->company);
        $this->assertEquals('new.email@example.com', $updated->email);
        $this->assertEquals('new phone', $updated->phone);
        $this->assertEquals('new fax', $updated->fax);
        $this->assertEquals('http://new.example.com', $updated->website);
    }

    function testCreateFromTransparentRedirect()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createCustomerViaTr(
            array(
                'customer' => array(
                    'first_name' => 'Joe',
                    'last_name' => 'Martin',
                    'credit_card' => array(
                        'number' => '5105105105105100',
                        'expiration_date' => '05/12'
                    )
                )
            ),
            array(
            )
        );
        $result = Braintree_Customer::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('Joe', $result->customer->firstName);
        $this->assertEquals('Martin', $result->customer->lastName);
        $creditCard = $result->customer->creditCards[0];
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
    }

    function testCreateFromTransparentRedirect_withParamsInTrData()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createCustomerViaTr(
            array(
            ),
            array(
                'customer' => array(
                    'firstName' => 'Joe',
                    'lastName' => 'Martin',
                    'creditCard' => array(
                        'number' => '5105105105105100',
                        'expirationDate' => '05/12'
                    )
                )
            )
        );
        $result = Braintree_Customer::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('Joe', $result->customer->firstName);
        $this->assertEquals('Martin', $result->customer->lastName);
        $creditCard = $result->customer->creditCards[0];
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
    }

    function testCreateFromTransparentRedirect_withValidationErrors()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createCustomerViaTr(
            array(
                'customer' => array(
                    'first_name' => str_repeat('x', 256),
                    'credit_card' => array(
                        'number' => 'invalid',
                        'expiration_date' => ''
                    )
                )
            ),
            array(
            )
        );
        $result = Braintree_Customer::createFromTransparentRedirect($queryString);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('customer')->onAttribute('firstName');
        $this->assertEquals(Braintree_Error_Codes::CUSTOMER_FIRST_NAME_IS_TOO_LONG, $errors[0]->code);
        $errors = $result->errors->forKey('customer')->forKey('creditCard')->onAttribute('number');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_NUMBER_INVALID_LENGTH, $errors[0]->code);
        $errors = $result->errors->forKey('customer')->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_EXPIRATION_DATE_IS_REQUIRED, $errors[0]->code);
    }

    function testCreateWithInvalidUTF8Bytes()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => "Jos\xe8 Maria",
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals("Jos\xc3\xa8 Maria", $customer->firstName);
    }

    function testCreateWithValidUTF8Bytes()
    {
        $result = Braintree_Customer::create(array(
            'firstName' => "Jos\303\251",
        ));
        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals("Jos\303\251", $customer->firstName);
    }

    function testUpdateFromTransparentRedirect()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $customer = Braintree_Customer::createNoValidate();
        $queryString = $this->updateCustomerViaTr(
            array(
                'customer' => array(
                    'first_name' => 'Joe',
                    'last_name' => 'Martin',
                    'email' => 'joe.martin@example.com'
                )
            ),
            array(
                'customerId' => $customer->id
            )
        );
        $result = Braintree_Customer::updateFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('Joe', $result->customer->firstName);
        $this->assertEquals('Martin', $result->customer->lastName);
        $this->assertEquals('joe.martin@example.com', $result->customer->email);
    }

    function testUpdateFromTransparentRedirect_withParamsInTrData()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $customer = Braintree_Customer::createNoValidate();
        $queryString = $this->updateCustomerViaTr(
            array(
            ),
            array(
                'customerId' => $customer->id,
                'customer' => array(
                    'firstName' => 'Joe',
                    'lastName' => 'Martin',
                    'email' => 'joe.martin@example.com'
                )
            )
        );
        $result = Braintree_Customer::updateFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('Joe', $result->customer->firstName);
        $this->assertEquals('Martin', $result->customer->lastName);
        $this->assertEquals('joe.martin@example.com', $result->customer->email);
    }

    function testUpdateFromTransparentRedirect_withValidationErrors()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $customer = Braintree_Customer::createNoValidate();
        $queryString = $this->updateCustomerViaTr(
            array(
                'customer' => array(
                    'first_name' => str_repeat('x', 256),
                )
            ),
            array(
                'customerId' => $customer->id
            )
        );
        $result = Braintree_Customer::updateFromTransparentRedirect($queryString);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('customer')->onAttribute('firstName');
        $this->assertEquals(Braintree_Error_Codes::CUSTOMER_FIRST_NAME_IS_TOO_LONG, $errors[0]->code);
    }

    function testUpdateFromTransparentRedirect_withUpdateExisting()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cardholderName' => 'Mike Jones',
                'billingAddress' => array(
                    'firstName' => 'Drew',
                    'lastName' => 'Smith'
                )
            )
        ))->customer;

        $queryString = $this->updateCustomerViaTr(
            array(),
            array(
                'customerId' => $customer->id,
                'customer' => array(
                    'firstName' => 'New First',
                    'lastName' => 'New Last',
                    'creditCard' => array(
                        'number' => '4111111111111111',
                        'expirationDate' => '05/13',
                        'cardholderName' => 'New Cardholder',
                        'options' => array(
                            'updateExistingToken' => $customer->creditCards[0]->token
                        ),
                        'billingAddress' => array(
                            'firstName' => 'New First Billing',
                            'lastName' => 'New Last Billing',
                            'options' => array(
                                'updateExisting' => true
                            )
                        )
                    )
                )
            )
        );
        $result = Braintree_Customer::updateFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);

        $this->assertEquals(true, $result->success);
        $customer = $result->customer;
        $this->assertEquals('New First', $customer->firstName);
        $this->assertEquals('New Last', $customer->lastName);

        $this->assertEquals(1, sizeof($result->customer->creditCards));
        $creditCard = $customer->creditCards[0];
        $this->assertEquals('411111', $creditCard->bin);
        $this->assertEquals('1111', $creditCard->last4);
        $this->assertEquals('New Cardholder', $creditCard->cardholderName);
        $this->assertEquals('05/2013', $creditCard->expirationDate);

        $this->assertEquals(1, sizeof($result->customer->addresses));
        $address = $customer->addresses[0];
        $this->assertEquals($address, $creditCard->billingAddress);
        $this->assertEquals('New First Billing', $address->firstName);
        $this->assertEquals('New Last Billing', $address->lastName);
    }

    function testSale_createsASaleUsingGivenToken()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $result = Braintree_Customer::sale($customer->id, array(
            'amount' => '100.00'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $result->transaction->creditCardDetails->token);
    }

    function testSaleNoValidate_createsASaleUsingGivenToken()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $transaction = Braintree_Customer::saleNoValidate($customer->id, array(
            'amount' => '100.00'
        ));
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
    }

    function testSaleNoValidate_throwsIfInvalid()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        Braintree_Customer::saleNoValidate($customer->id, array(
            'amount' => 'invalid'
        ));
    }

    function testCredit_createsACreditUsingGivenCustomerId()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $result = Braintree_Customer::credit($customer->id, array(
            'amount' => '100.00'
        ));
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Braintree_Transaction::CREDIT, $result->transaction->type);
        $this->assertEquals($customer->id, $result->transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $result->transaction->creditCardDetails->token);
    }

    function testCreditNoValidate_createsACreditUsingGivenId()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $transaction = Braintree_Customer::creditNoValidate($customer->id, array(
            'amount' => '100.00'
        ));
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals(Braintree_Transaction::CREDIT, $transaction->type);
        $this->assertEquals($customer->id, $transaction->customerDetails->id);
        $this->assertEquals($creditCard->token, $transaction->creditCardDetails->token);
    }

    function testCreditNoValidate_throwsIfInvalid()
    {
        $customer = Braintree_Customer::createNoValidate(array(
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $creditCard = $customer->creditCards[0];
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        Braintree_Customer::creditNoValidate($customer->id, array(
            'amount' => 'invalid'
        ));
    }

    function createCustomerViaTr($regularParams, $trParams)
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $trData = Braintree_TransparentRedirect::createCustomerData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return Braintree_TestHelper::submitTrRequest(
            Braintree_Customer::createCustomerUrl(),
            $regularParams,
            $trData
        );
    }

    function updateCustomerViaTr($regularParams, $trParams)
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $trData = Braintree_TransparentRedirect::updateCustomerData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return Braintree_TestHelper::submitTrRequest(
            Braintree_Customer::updateCustomerUrl(),
            $regularParams,
            $trData
        );
    }
}
