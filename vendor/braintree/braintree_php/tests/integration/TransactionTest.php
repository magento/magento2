<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_TransactionTest extends PHPUnit_Framework_TestCase
{
    function testCloneTransaction()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'orderId' => '123',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/2011',
            ),
            'customer' => array(
                'firstName' => 'Dan',
            ),
            'billing' => array(
                'firstName' => 'Carl',
            ),
            'shipping' => array(
                'firstName' => 'Andrew',
            )
      ));
      $this->assertTrue($result->success);
      $transaction = $result->transaction;

      $cloneResult = Braintree_Transaction::cloneTransaction(
          $transaction->id,
          array(
              'amount' => '123.45',
              'channel' => 'MyShoppingCartProvider',
              'options' => array('submitForSettlement' => false)
          )
      );
      Braintree_TestHelper::assertPrintable($cloneResult);
      $this->assertTrue($cloneResult->success);
      $cloneTransaction = $cloneResult->transaction;
      $this->assertEquals('Dan', $cloneTransaction->customerDetails->firstName);
      $this->assertEquals('Carl', $cloneTransaction->billingDetails->firstName);
      $this->assertEquals('Andrew', $cloneTransaction->shippingDetails->firstName);
      $this->assertEquals('510510******5100', $cloneTransaction->creditCardDetails->maskedNumber);
      $this->assertEquals('authorized', $cloneTransaction->status);
      $this->assertEquals('123.45', $cloneTransaction->amount);
      $this->assertEquals('MyShoppingCartProvider', $cloneTransaction->channel);
    }

    function testCreateTransactionUsingNonce()
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

        $result = Braintree_Transaction::sale(array(
            'amount' => '47.00',
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('47.00', $transaction->amount);
    }

    function testGatewayCreateTransactionUsingNonce()
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

        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $result = $gateway->transaction()->sale(array(
            'amount' => '47.00',
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('47.00', $transaction->amount);
    }

    function testCreateTransactionUsingFakeApplePayNonce()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '47.00',
            'paymentMethodNonce' => Braintree_Test_Nonces::$applePayAmEx
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('47.00', $transaction->amount);
        $applePayDetails = $transaction->applePayCardDetails;
        $this->assertSame(Braintree_ApplePayCard::AMEX, $applePayDetails->cardType);
        $this->assertContains("AmEx ", $applePayDetails->paymentInstrumentName);
        $this->assertTrue(intval($applePayDetails->expirationMonth) > 0);
        $this->assertTrue(intval($applePayDetails->expirationYear) > 0);
        $this->assertNotNull($applePayDetails->cardholderName);
    }

    function testCreateTransactionUsingFakeCoinbaseNonce()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '17.00',
            'paymentMethodNonce' => Braintree_Test_Nonces::$coinbase
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertNotNull($transaction->coinbaseDetails);
        $this->assertNotNull($transaction->coinbaseDetails->userId);
        $this->assertNotNull($transaction->coinbaseDetails->userName);
        $this->assertNotNull($transaction->coinbaseDetails->userEmail);
    }

    function testCreateTransactionReturnsPaymentInstrumentType()
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

        $result = Braintree_Transaction::sale(array(
            'amount' => '47.00',
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_PaymentInstrumentType::CREDIT_CARD, $transaction->paymentInstrumentType);
    }

    function testCloneTransactionAndSubmitForSettlement()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/2011',
            )
        ));

      $this->assertTrue($result->success);
      $transaction = $result->transaction;

      $cloneResult = Braintree_Transaction::cloneTransaction($transaction->id, array('amount' => '123.45', 'options' => array('submitForSettlement' => true)));
      $cloneTransaction = $cloneResult->transaction;
      $this->assertEquals('submitted_for_settlement', $cloneTransaction->status);
    }

    function testCloneWithValidations()
    {
        $result = Braintree_Transaction::credit(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/2011'
            )
      ));
      $this->assertTrue($result->success);
      $transaction = $result->transaction;

      $cloneResult = Braintree_Transaction::cloneTransaction($transaction->id, array('amount' => '123.45'));
      $this->assertFalse($cloneResult->success);

      $baseErrors = $cloneResult->errors->forKey('transaction')->onAttribute('base');

      $this->assertEquals(Braintree_Error_Codes::TRANSACTION_CANNOT_CLONE_CREDIT, $baseErrors[0]->code);
    }

    function testSale()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
        $this->assertEquals('The Cardholder', $transaction->creditCardDetails->cardholderName);
    }

    function testSaleWithAccessToken()
    {
        $credentials = Braintree_OAuthTestHelper::createCredentials(array(
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'merchantId' => 'integration_merchant_id',
        ));

        $gateway = new Braintree_Gateway(array(
            'accessToken' => $credentials->accessToken,
        ));

        $result = $gateway->transaction()->sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
        $this->assertEquals('The Cardholder', $transaction->creditCardDetails->cardholderName);
    }

    function testSaleWithRiskData()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertNotNull($transaction->riskData);
        $this->assertNotNull($transaction->riskData->decision);
    }

    function testRecurring()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'recurring' => true,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(true, $transaction->recurring);
    }

    function testSale_withServiceFee()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '10.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultSubMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount' => '1.00'
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('1.00', $transaction->serviceFeeAmount);
    }

    function testSale_isInvalidIfTransactionMerchantAccountIsNotSub()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '10.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount' => '1.00'
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;
        $serviceFeeErrors = $result->errors->forKey('transaction')->onAttribute('serviceFeeAmount');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_SERVICE_FEE_AMOUNT_NOT_ALLOWED_ON_MASTER_MERCHANT_ACCOUNT, $serviceFeeErrors[0]->code);
    }

    function testSale_isInvalidIfSubMerchantAccountHasNoServiceFee()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '10.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultSubMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;
        $serviceFeeErrors = $result->errors->forKey('transaction')->onAttribute('merchantAccountId');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_SUB_MERCHANT_ACCOUNT_REQUIRES_SERVICE_FEE_AMOUNT, $serviceFeeErrors[0]->code);
    }

    function testSale_withVenmoSdkSession()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '10.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'venmoSdkSession' => Braintree_Test_VenmoSdk::getTestSession()
            )
        ));
        $this->assertEquals(true, $result->success);
        $transaction = $result->transaction;
        $this->assertEquals(true, $transaction->creditCardDetails->venmoSdk);
    }

    function testSale_withVenmoSdkPaymentMethodCode()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '10.00',
            'venmoSdkPaymentMethodCode' => Braintree_Test_VenmoSdk::$visaPaymentMethodCode
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals("411111", $transaction->creditCardDetails->bin);
    }

    function testSale_withLevel2Attributes()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'expirationDate' => '05/2011',
                'number' => '5105105105105100'
            ),
            'taxExempt' => true,
            'taxAmount' => '10.00',
            'purchaseOrderNumber' => '12345'
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $this->assertTrue($transaction->taxExempt);
        $this->assertEquals('10.00', $transaction->taxAmount);
        $this->assertEquals('12345', $transaction->purchaseOrderNumber);
    }

    function testSale_withInvalidTaxAmountAttribute()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'expirationDate' => '05/2011',
                'number' => '5105105105105100'
            ),
            'taxAmount' => 'abc'
        ));

        $this->assertFalse($result->success);

        $taxAmountErrors = $result->errors->forKey('transaction')->onAttribute('taxAmount');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_TAX_AMOUNT_FORMAT_IS_INVALID, $taxAmountErrors[0]->code);
    }

    function testSale_withServiceFeeTooLarge()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '10.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultSubMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount' => '20.00'
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('serviceFeeAmount');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_SERVICE_FEE_AMOUNT_IS_TOO_LARGE, $errors[0]->code);
    }

    function testSale_withTooLongPurchaseOrderAttribute()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'expirationDate' => '05/2011',
                'number' => '5105105105105100'
            ),
            'purchaseOrderNumber' => 'aaaaaaaaaaaaaaaaaa'
        ));

        $this->assertFalse($result->success);

        $purchaseOrderNumberErrors = $result->errors->forKey('transaction')->onAttribute('purchaseOrderNumber');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_PURCHASE_ORDER_NUMBER_IS_TOO_LONG, $purchaseOrderNumberErrors[0]->code);
    }

    function testSale_withInvalidPurchaseOrderNumber()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'expirationDate' => '05/2011',
                'number' => '5105105105105100'
            ),
            'purchaseOrderNumber' => "\x80\x90\xA0"
        ));

        $this->assertFalse($result->success);

        $purchaseOrderNumberErrors = $result->errors->forKey('transaction')->onAttribute('purchaseOrderNumber');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_PURCHASE_ORDER_NUMBER_IS_INVALID, $purchaseOrderNumberErrors[0]->code);
    }

    function testSale_withAllAttributes()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'orderId' => '123',
            'channel' => 'MyShoppingCardProvider',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => '5105105105105100',
                'expirationDate' => '05/2011',
                'cvv' => '123'
            ),
            'customer' => array(
                'firstName' => 'Dan',
                'lastName' => 'Smith',
                'company' => 'Braintree',
                'email' => 'dan@example.com',
                'phone' => '419-555-1234',
                'fax' => '419-555-1235',
                'website' => 'http://braintreepayments.com'
            ),
            'billing' => array(
                'firstName' => 'Carl',
                'lastName' => 'Jones',
                'company' => 'Braintree',
                'streetAddress' => '123 E Main St',
                'extendedAddress' => 'Suite 403',
                'locality' => 'Chicago',
                'region' => 'IL',
                'postalCode' => '60622',
                'countryName' => 'United States of America',
                'countryCodeAlpha2' => 'US',
                'countryCodeAlpha3' => 'USA',
                'countryCodeNumeric' => '840'
            ),
            'shipping' => array(
                'firstName' => 'Andrew',
                'lastName' => 'Mason',
                'company' => 'Braintree',
                'streetAddress' => '456 W Main St',
                'extendedAddress' => 'Apt 2F',
                'locality' => 'Bartlett',
                'region' => 'IL',
                'postalCode' => '60103',
                'countryName' => 'United States of America',
                'countryCodeAlpha2' => 'US',
                'countryCodeAlpha3' => 'USA',
                'countryCodeNumeric' => '840'
            )
      ));
      Braintree_TestHelper::assertPrintable($result);
      $this->assertTrue($result->success);
      $transaction = $result->transaction;

      $this->assertNotNull($transaction->id);
      $this->assertNotNull($transaction->createdAt);
      $this->assertNotNull($transaction->updatedAt);
      $this->assertNull($transaction->refundId);

      $this->assertEquals(Braintree_TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
      $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
      $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
      $this->assertEquals('100.00', $transaction->amount);
      $this->assertEquals('USD', $transaction->currencyIsoCode);
      $this->assertEquals('123', $transaction->orderId);
      $this->assertEquals('MyShoppingCardProvider', $transaction->channel);
      $this->assertEquals('MasterCard', $transaction->creditCardDetails->cardType);
      $this->assertEquals('1000', $transaction->processorResponseCode);
      $this->assertEquals('Approved', $transaction->processorResponseText);
      $this->assertNull($transaction->voiceReferralNumber);
      $this->assertFalse($transaction->taxExempt);

      $this->assertEquals('M', $transaction->avsPostalCodeResponseCode);
      $this->assertEquals('M', $transaction->avsStreetAddressResponseCode);
      $this->assertEquals('M', $transaction->cvvResponseCode);

      $this->assertEquals('Dan', $transaction->customerDetails->firstName);
      $this->assertEquals('Smith', $transaction->customerDetails->lastName);
      $this->assertEquals('Braintree', $transaction->customerDetails->company);
      $this->assertEquals('dan@example.com', $transaction->customerDetails->email);
      $this->assertEquals('419-555-1234', $transaction->customerDetails->phone);
      $this->assertEquals('419-555-1235', $transaction->customerDetails->fax);
      $this->assertEquals('http://braintreepayments.com', $transaction->customerDetails->website);

      $this->assertEquals('Carl', $transaction->billingDetails->firstName);
      $this->assertEquals('Jones', $transaction->billingDetails->lastName);
      $this->assertEquals('Braintree', $transaction->billingDetails->company);
      $this->assertEquals('123 E Main St', $transaction->billingDetails->streetAddress);
      $this->assertEquals('Suite 403', $transaction->billingDetails->extendedAddress);
      $this->assertEquals('Chicago', $transaction->billingDetails->locality);
      $this->assertEquals('IL', $transaction->billingDetails->region);
      $this->assertEquals('60622', $transaction->billingDetails->postalCode);
      $this->assertEquals('United States of America', $transaction->billingDetails->countryName);
      $this->assertEquals('US', $transaction->billingDetails->countryCodeAlpha2);
      $this->assertEquals('USA', $transaction->billingDetails->countryCodeAlpha3);
      $this->assertEquals('840', $transaction->billingDetails->countryCodeNumeric);

      $this->assertEquals('Andrew', $transaction->shippingDetails->firstName);
      $this->assertEquals('Mason', $transaction->shippingDetails->lastName);
      $this->assertEquals('Braintree', $transaction->shippingDetails->company);
      $this->assertEquals('456 W Main St', $transaction->shippingDetails->streetAddress);
      $this->assertEquals('Apt 2F', $transaction->shippingDetails->extendedAddress);
      $this->assertEquals('Bartlett', $transaction->shippingDetails->locality);
      $this->assertEquals('IL', $transaction->shippingDetails->region);
      $this->assertEquals('60103', $transaction->shippingDetails->postalCode);
      $this->assertEquals('United States of America', $transaction->shippingDetails->countryName);
      $this->assertEquals('US', $transaction->shippingDetails->countryCodeAlpha2);
      $this->assertEquals('USA', $transaction->shippingDetails->countryCodeAlpha3);
      $this->assertEquals('840', $transaction->shippingDetails->countryCodeNumeric);

      $this->assertNotNull($transaction->processorAuthorizationCode);
      $this->assertEquals('510510', $transaction->creditCardDetails->bin);
      $this->assertEquals('5100', $transaction->creditCardDetails->last4);
      $this->assertEquals('510510******5100', $transaction->creditCardDetails->maskedNumber);
      $this->assertEquals('The Cardholder', $transaction->creditCardDetails->cardholderName);
      $this->assertEquals('05', $transaction->creditCardDetails->expirationMonth);
      $this->assertEquals('2011', $transaction->creditCardDetails->expirationYear);
      $this->assertNotNull($transaction->creditCardDetails->imageUrl);
    }

    function testSale_withCustomFields()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'store_me' => 'custom value'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $customFields = $transaction->customFields;
        $this->assertEquals('custom value', $customFields['store_me']);
    }

    function testSale_withExpirationMonthAndYear()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationMonth' => '5',
                'expirationYear' => '2012'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('05', $transaction->creditCardDetails->expirationMonth);
        $this->assertEquals('2012', $transaction->creditCardDetails->expirationYear);
    }

    function testSale_underscoresAllCustomFields()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'storeMe' => 'custom value'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $customFields = $transaction->customFields;
        $this->assertEquals('custom value', $customFields['store_me']);
    }

    function testSale_withInvalidCustomField()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'invalidKey' => 'custom value'
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('customFields');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_CUSTOM_FIELD_IS_INVALID, $errors[0]->code);
        $this->assertEquals('Custom field is invalid: invalidKey.', $errors[0]->message);
    }

    function testSale_withMerchantAccountId()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_TestHelper::nonDefaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testSale_withoutMerchantAccountIdFallsBackToDefault()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testSale_withShippingAddressId()
    {
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            )
        ))->customer;

        $address = Braintree_Address::create(array(
            'customerId' => $customer->id,
            'streetAddress' => '123 Fake St.'
        ))->address;

        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'customerId' => $customer->id,
            'shippingAddressId' => $address->id
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('123 Fake St.', $transaction->shippingDetails->streetAddress);
        $this->assertEquals($address->id, $transaction->shippingDetails->id);
    }

    function testSale_withBillingAddressId()
    {
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            )
        ))->customer;

        $address = Braintree_Address::create(array(
            'customerId' => $customer->id,
            'streetAddress' => '123 Fake St.'
        ))->address;

        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'customerId' => $customer->id,
            'billingAddressId' => $address->id
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('123 Fake St.', $transaction->billingDetails->streetAddress);
        $this->assertEquals($address->id, $transaction->billingDetails->id);
    }

    function testSaleNoValidate()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testSale_withProcessorDecline()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$decline,
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::PROCESSOR_DECLINED, $result->transaction->status);
        $this->assertEquals(2000, $result->transaction->processorResponseCode);
        $this->assertEquals("Do Not Honor", $result->transaction->processorResponseText);
        $this->assertEquals("2000 : Do Not Honor", $result->transaction->additionalProcessorResponse);
    }

    function testSale_withExistingCustomer()
    {
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com'
        ))->customer;

        $transaction = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'customerId' => $customer->id,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            )
        ))->transaction;
        $this->assertEquals($transaction->creditCardDetails->maskedNumber, '401288******1881');
        $this->assertNull($transaction->vaultCreditCard());
    }

    function testSale_andStoreShippingAddressInVault()
    {
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com'
        ))->customer;

        $transaction = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'customerId' => $customer->id,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            ),
            'shipping' => array(
                'firstName' => 'Darren',
                'lastName' => 'Stevens'
            ),
            'options' => array(
                'storeInVault' => true,
                'storeShippingAddressInVault' => true
            )
        ))->transaction;

        $customer = Braintree_Customer::find($customer->id);
        $this->assertEquals('Darren', $customer->addresses[0]->firstName);
        $this->assertEquals('Stevens', $customer->addresses[0]->lastName);
    }

    function testSale_withExistingCustomer_storeInVault()
    {
        $customer = Braintree_Customer::create(array(
            'firstName' => 'Mike',
            'lastName' => 'Jones',
            'company' => 'Jones Co.',
            'email' => 'mike.jones@example.com',
            'phone' => '419.555.1234',
            'fax' => '419.555.1235',
            'website' => 'http://example.com'
        ))->customer;

        $transaction = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'customerId' => $customer->id,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number' => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'storeInVault' => true
            )
        ))->transaction;
        $this->assertEquals($transaction->creditCardDetails->maskedNumber, '401288******1881');
        $this->assertEquals($transaction->vaultCreditCard()->maskedNumber, '401288******1881');
    }

    function testCredit()
    {
        $result = Braintree_Transaction::credit(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status);
        $this->assertEquals(Braintree_Transaction::CREDIT, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testCreditNoValidate()
    {
        $transaction = Braintree_Transaction::creditNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::CREDIT, $transaction->type);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testCredit_withMerchantAccountId()
    {
        $result = Braintree_Transaction::credit(array(
            'amount' => '100.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_TestHelper::nonDefaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testCredit_withoutMerchantAccountIdFallsBackToDefault()
    {
        $result = Braintree_Transaction::credit(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testCredit_withServiceFeeNotAllowed()
    {
        $result = Braintree_Transaction::credit(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount' => '12.75'
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_SERVICE_FEE_IS_NOT_ALLOWED_ON_CREDITS, $errors[0]->code);
    }

    function testSubmitForSettlement_nullAmount()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $submitResult = Braintree_Transaction::submitForSettlement($transaction->id);
        $this->assertEquals(true, $submitResult->success);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $submitResult->transaction->status);
        $this->assertEquals('100.00', $submitResult->transaction->amount);
    }

    function testSubmitForSettlement_amountLessThanServiceFee()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '10.00',
            'merchantAccountId' => Braintree_TestHelper::nonDefaultSubMerchantAccountId(),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount' => '5.00'
        ));
        $submitResult = Braintree_Transaction::submitForSettlement($transaction->id, '1.00');
        $errors = $submitResult->errors->forKey('transaction')->onAttribute('amount');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_SETTLEMENT_AMOUNT_IS_LESS_THAN_SERVICE_FEE_AMOUNT, $errors[0]->code);
    }

    function testSubmitForSettlement_withAmount()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $submitResult = Braintree_Transaction::submitForSettlement($transaction->id, '50.00');
        $this->assertEquals(true, $submitResult->success);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $submitResult->transaction->status);
        $this->assertEquals('50.00', $submitResult->transaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenValidWithoutAmount()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $submittedTransaction = Braintree_Transaction::submitForSettlementNoValidate($transaction->id);
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $submittedTransaction->status);
        $this->assertEquals('100.00', $submittedTransaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenValidWithAmount()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $submittedTransaction = Braintree_Transaction::submitForSettlementNoValidate($transaction->id, '99.00');
        $this->assertEquals(Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $submittedTransaction->status);
        $this->assertEquals('99.00', $submittedTransaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenInvalid()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        $submittedTransaction = Braintree_Transaction::submitForSettlementNoValidate($transaction->id, '101.00');
    }

    function testVoid()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $voidResult = Braintree_Transaction::void($transaction->id);
        $this->assertEquals(true, $voidResult->success);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voidResult->transaction->status);
    }

    function test_countryValidationError_inconsistency()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing' => array(
                'countryCodeAlpha2' => 'AS',
                'countryCodeAlpha3' => 'USA'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_INCONSISTENT_COUNTRY, $errors[0]->code);
    }

    function test_countryValidationError_incorrectAlpha2()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing' => array(
                'countryCodeAlpha2' => 'ZZ'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeAlpha2');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_COUNTRY_CODE_ALPHA2_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function test_countryValidationError_incorrectAlpha3()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing' => array(
                'countryCodeAlpha3' => 'ZZZ'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeAlpha3');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_COUNTRY_CODE_ALPHA3_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function test_countryValidationError_incorrectNumericCode()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing' => array(
                'countryCodeNumeric' => '000'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeNumeric');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_COUNTRY_CODE_NUMERIC_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function testVoid_withValidationError()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $voided = Braintree_Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voided->status);
        $result = Braintree_Transaction::void($transaction->id);
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_CANNOT_BE_VOIDED, $errors[0]->code);
    }

    function testVoidNoValidate()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $voided = Braintree_Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voided->status);
    }

    function testVoidNoValidate_throwsIfNotInvalid()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $voided = Braintree_Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voided->status);
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        $voided = Braintree_Transaction::voidNoValidate($transaction->id);
    }

    function testFind()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $transaction = Braintree_Transaction::find($result->transaction->id);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Braintree_Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testFindExposesDisbursementDetails()
    {
        $transaction = Braintree_Transaction::find("deposittransaction");

        $this->assertEquals(true, $transaction->isDisbursed());

        $disbursementDetails = $transaction->disbursementDetails;
        $this->assertEquals('100.00', $disbursementDetails->settlementAmount);
        $this->assertEquals('USD', $disbursementDetails->settlementCurrencyIsoCode);
        $this->assertEquals('1', $disbursementDetails->settlementCurrencyExchangeRate);
        $this->assertEquals(false, $disbursementDetails->fundsHeld);
        $this->assertEquals(true, $disbursementDetails->success);
        $this->assertEquals(new DateTime('2013-04-10'), $disbursementDetails->disbursementDate);
    }

    function testFindExposesDisputes()
    {
        $transaction = Braintree_Transaction::find("disputedtransaction");

        $dispute = $transaction->disputes[0];
        $this->assertEquals('250.00', $dispute->amount);
        $this->assertEquals('USD', $dispute->currencyIsoCode);
        $this->assertEquals(Braintree_Dispute::FRAUD, $dispute->reason);
        $this->assertEquals(Braintree_Dispute::WON, $dispute->status);
        $this->assertEquals(new DateTime('2014-03-01'), $dispute->receivedDate);
        $this->assertEquals(new DateTime('2014-03-21'), $dispute->replyByDate);
        $this->assertEquals("disputedtransaction", $dispute->transactionDetails->id);
        $this->assertEquals("1000.00", $dispute->transactionDetails->amount);
    }

    function testFindExposesThreeDSecureInfo()
    {
        $transaction = Braintree_Transaction::find("threedsecuredtransaction");

        $info = $transaction->threeDSecureInfo;
        $this->assertEquals("Y", $info->enrolled);
        $this->assertEquals("authenticate_successful", $info->status);
        $this->assertTrue($info->liabilityShifted);
        $this->assertTrue($info->liabilityShiftPossible);
    }

    function testFindExposesNullThreeDSecureInfo()
    {
        $transaction = Braintree_Transaction::find("settledtransaction");

        $this->assertNull($transaction->threeDSecureInfo);
    }

    function testFindExposesRetrievals()
    {
        $transaction = Braintree_Transaction::find("retrievaltransaction");

        $dispute = $transaction->disputes[0];
        $this->assertEquals('1000.00', $dispute->amount);
        $this->assertEquals('USD', $dispute->currencyIsoCode);
        $this->assertEquals(Braintree_Dispute::RETRIEVAL, $dispute->reason);
        $this->assertEquals(Braintree_Dispute::OPEN, $dispute->status);
        $this->assertEquals("retrievaltransaction", $dispute->transactionDetails->id);
        $this->assertEquals("1000.00", $dispute->transactionDetails->amount);
    }

    function testFindExposesPayPalDetails()
    {
        $transaction = Braintree_Transaction::find("settledtransaction");
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->assertNotNull($transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->authorizationId);
        $this->assertNotNull($transaction->paypalDetails->payerId);
        $this->assertNotNull($transaction->paypalDetails->payerFirstName);
        $this->assertNotNull($transaction->paypalDetails->payerLastName);
        $this->assertNotNull($transaction->paypalDetails->sellerProtectionStatus);
        $this->assertNotNull($transaction->paypalDetails->captureId);
        $this->assertNotNull($transaction->paypalDetails->refundId);
    }

    function testSale_storeInVault()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'Card Holder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customer' => array(
                'firstName' => 'Dan',
                'lastName' => 'Smith',
                'company' => 'Braintree',
                'email' => 'dan@example.com',
                'phone' => '419-555-1234',
                'fax' => '419-555-1235',
                'website' => 'http://getbraintree.com'
            ),
            'options' => array(
                'storeInVault' => true
            )
        ));
        $this->assertNotNull($transaction->creditCardDetails->token);
        $creditCard = $transaction->vaultCreditCard();
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('Card Holder', $creditCard->cardholderName);
        $customer = $transaction->vaultCustomer();
        $this->assertEquals('Dan', $customer->firstName);
        $this->assertEquals('Smith', $customer->lastName);
        $this->assertEquals('Braintree', $customer->company);
        $this->assertEquals('dan@example.com', $customer->email);
        $this->assertEquals('419-555-1234', $customer->phone);
        $this->assertEquals('419-555-1235', $customer->fax);
        $this->assertEquals('http://getbraintree.com', $customer->website);
    }

    function testSale_storeInVaultOnSuccessWithSuccessfulTransaction()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'cardholderName' => 'Card Holder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customer' => array(
                'firstName' => 'Dan',
                'lastName' => 'Smith',
                'company' => 'Braintree',
                'email' => 'dan@example.com',
                'phone' => '419-555-1234',
                'fax' => '419-555-1235',
                'website' => 'http://getbraintree.com'
            ),
            'options' => array(
                'storeInVaultOnSuccess' => true
            )
        ));
        $this->assertNotNull($transaction->creditCardDetails->token);
        $creditCard = $transaction->vaultCreditCard();
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('Card Holder', $creditCard->cardholderName);
        $customer = $transaction->vaultCustomer();
        $this->assertEquals('Dan', $customer->firstName);
        $this->assertEquals('Smith', $customer->lastName);
        $this->assertEquals('Braintree', $customer->company);
        $this->assertEquals('dan@example.com', $customer->email);
        $this->assertEquals('419-555-1234', $customer->phone);
        $this->assertEquals('419-555-1235', $customer->fax);
        $this->assertEquals('http://getbraintree.com', $customer->website);
    }

    function testSale_storeInVaultOnSuccessWithFailedTransaction()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$decline,
            'creditCard' => array(
                'cardholderName' => 'Card Holder',
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customer' => array(
                'firstName' => 'Dan',
                'lastName' => 'Smith',
                'company' => 'Braintree',
                'email' => 'dan@example.com',
                'phone' => '419-555-1234',
                'fax' => '419-555-1235',
                'website' => 'http://getbraintree.com'
            ),
            'options' => array(
                'storeInVaultOnSuccess' => true
            )
        ));
        $transaction = $result->transaction;
        $this->assertNull($transaction->creditCardDetails->token);
        $this->assertNull($transaction->vaultCreditCard());
        $this->assertNull($transaction->customerDetails->id);
        $this->assertNull($transaction->vaultCustomer());
    }

    function testSale_withFraudParams()
    {
        $result = Braintree_Transaction::sale(array(
            'deviceSessionId' => '123abc',
            'fraudMerchantId' => '456',
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            )
        ));

        $this->assertTrue($result->success);
    }

    function testSale_withDescriptor()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'descriptor' => array(
                'name' => '123*123456789012345678',
                'phone' => '3334445555',
                'url' => 'ebay.com'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('123*123456789012345678', $transaction->descriptor->name);
        $this->assertEquals('3334445555', $transaction->descriptor->phone);
        $this->assertEquals('ebay.com', $transaction->descriptor->url);
    }

    function testSale_withDescriptorValidation()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'descriptor' => array(
                'name' => 'badcompanyname12*badproduct12',
                'phone' => '%bad4445555',
                'url' => '12345678901234'
            )
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $errors = $result->errors->forKey('transaction')->forKey('descriptor')->onAttribute('name');
        $this->assertEquals(Braintree_Error_Codes::DESCRIPTOR_NAME_FORMAT_IS_INVALID, $errors[0]->code);

        $errors = $result->errors->forKey('transaction')->forKey('descriptor')->onAttribute('phone');
        $this->assertEquals(Braintree_Error_Codes::DESCRIPTOR_PHONE_FORMAT_IS_INVALID, $errors[0]->code);

        $errors = $result->errors->forKey('transaction')->forKey('descriptor')->onAttribute('url');
        $this->assertEquals(Braintree_Error_Codes::DESCRIPTOR_URL_FORMAT_IS_INVALID, $errors[0]->code);
    }

    function testSale_withHoldInEscrow()
    {
        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::nonDefaultSubMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'holdInEscrow' => true
            ),
            'serviceFeeAmount' => '1.00'
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::ESCROW_HOLD_PENDING, $transaction->escrowStatus);
    }

    function testSale_withHoldInEscrowFailsForMasterMerchantAccount()
    {
        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'holdInEscrow' => true
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(
            Braintree_Error_Codes::TRANSACTION_CANNOT_HOLD_IN_ESCROW,
            $errors[0]->code
        );
    }

    function testSale_withThreeDSecureOptionRequired()
    {
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number" => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear" => "2099"
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::threeDSecureMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/09'
            ),
            'options' => array(
                'three_d_secure' => array(
                    'required' => true
                )
            )
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::THREE_D_SECURE, $result->transaction->gatewayRejectionReason);
    }

    function testSale_withThreeDSecureToken()
    {
        $threeDSecureToken = Braintree_TestHelper::create3DSVerification(
            Braintree_TestHelper::threeDSecureMerchantAccountId(),
            array(
                'number' => '4111111111111111',
                'expirationMonth' => '05',
                'expirationYear' => '2009'
            )
        );
        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::threeDSecureMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/09'
            ),
            'threeDSecureToken' => $threeDSecureToken
        ));
        $this->assertTrue($result->success);
    }

    function testSale_returnsErrorIfThreeDSecureToken()
    {
        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::threeDSecureMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/09'
            ),
            'threeDSecureToken' => NULL
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('threeDSecureToken');
        $this->assertEquals(
            Braintree_Error_Codes::TRANSACTION_THREE_D_SECURE_TOKEN_IS_INVALID,
            $errors[0]->code
        );
    }

    function testSale_returnsErrorIf3dsLookupDataDoesNotMatchTransactionData()
    {
        $threeDSecureToken = Braintree_TestHelper::create3DSVerification(
            Braintree_TestHelper::threeDSecureMerchantAccountId(),
            array(
                'number' => '4111111111111111',
                'expirationMonth' => '05',
                'expirationYear' => '2009'
            )
        );

        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::threeDSecureMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/09'
            ),
            'threeDSecureToken' => $threeDSecureToken
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('threeDSecureToken');
        $this->assertEquals(
            Braintree_Error_Codes::TRANSACTION_THREE_D_SECURE_TRANSACTION_DATA_DOESNT_MATCH_VERIFY,
            $errors[0]->code
        );
    }

    function testHoldInEscrow_afterSale()
    {
        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::nonDefaultSubMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount' => '1.00'
        ));
        $result = Braintree_Transaction::holdInEscrow($result->transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals(Braintree_Transaction::ESCROW_HOLD_PENDING, $result->transaction->escrowStatus);
    }

    function testHoldInEscrow_afterSaleFailsWithMasterMerchantAccount()
    {
        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $result = Braintree_Transaction::holdInEscrow($result->transaction->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(
            Braintree_Error_Codes::TRANSACTION_CANNOT_HOLD_IN_ESCROW,
            $errors[0]->code
        );
    }

    function testSubmitForRelease_FromEscrow()
    {
        $transaction = $this->createEscrowedTransaction();
        $result = Braintree_Transaction::releaseFromEscrow($transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals(Braintree_Transaction::ESCROW_RELEASE_PENDING, $result->transaction->escrowStatus);
    }

    function testSubmitForRelease_fromEscrowFailsForTransactionsNotHeldInEscrow()
    {
        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $result = Braintree_Transaction::releaseFromEscrow($result->transaction->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(
            Braintree_Error_Codes::TRANSACTION_CANNOT_RELEASE_FROM_ESCROW,
            $errors[0]->code
        );
    }

    function testCancelRelease_fromEscrow()
    {
        $transaction = $this->createEscrowedTransaction();
        $result = Braintree_Transaction::releaseFromEscrow($transaction->id);
        $result = Braintree_Transaction::cancelRelease($transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals(
            Braintree_Transaction::ESCROW_HELD,
            $result->transaction->escrowStatus
        );
    }

    function testCancelRelease_fromEscrowFailsIfTransactionNotSubmittedForRelease()
    {
        $transaction = $this->createEscrowedTransaction();
        $result = Braintree_Transaction::cancelRelease($transaction->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(
            Braintree_Error_Codes::TRANSACTION_CANNOT_CANCEL_RELEASE,
            $errors[0]->code
        );
    }

    function testCreateFromTransparentRedirect()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'customer' => array(
                        'first_name' => 'First'
                    ),
                    'credit_card' => array(
                        'number' => '5105105105105100',
                        'expiration_date' => '05/12'
                    )
                )
            ),
            array(
                'transaction' => array(
                    'type' => Braintree_Transaction::SALE,
                    'amount' => '100.00'
                )
            )
        );
        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        Braintree_TestHelper::assertPrintable($result);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Braintree_Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $result->transaction->status);
        $creditCard = $result->transaction->creditCardDetails;
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('US', $creditCard->customerLocation);
        $this->assertEquals('MasterCard', $creditCard->cardType);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('510510******5100', $creditCard->maskedNumber);
        $customer = $result->transaction->customerDetails;
        $this->assertequals('First', $customer->firstName);
    }

    function testCreateFromTransparentRedirectWithInvalidParams()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'bad_key' => 'bad_value',
                    'customer' => array(
                        'first_name' => 'First'
                    ),
                    'credit_card' => array(
                        'number' => '5105105105105100',
                        'expiration_date' => '05/12'
                    )
                )
            ),
            array(
                'transaction' => array(
                    'type' => Braintree_Transaction::SALE,
                    'amount' => '100.00'
                )
            )
        );
        try {
            $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
            $this->fail();
        } catch (Braintree_Exception_Authorization $e) {
            $this->assertEquals("Invalid params: transaction[bad_key]", $e->getMessage());
        }
    }

    function testCreateFromTransparentRedirect_withParamsInTrData()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
            ),
            array(
                'transaction' => array(
                    'type' => Braintree_Transaction::SALE,
                    'amount' => '100.00',
                    'customer' => array(
                        'firstName' => 'First'
                    ),
                    'creditCard' => array(
                        'number' => '5105105105105100',
                        'expirationDate' => '05/12'
                    )
                )
            )
        );
        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Braintree_Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $result->transaction->status);
        $creditCard = $result->transaction->creditCardDetails;
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('US', $creditCard->customerLocation);
        $this->assertEquals('MasterCard', $creditCard->cardType);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('510510******5100', $creditCard->maskedNumber);
        $customer = $result->transaction->customerDetails;
        $this->assertequals('First', $customer->firstName);
    }

    function testCreateFromTransparentRedirect_withValidationErrors()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'customer' => array(
                        'first_name' => str_repeat('x', 256),
                    ),
                    'credit_card' => array(
                        'number' => 'invalid',
                        'expiration_date' => ''
                    )
                )
            ),
            array(
                'transaction' => array('type' => Braintree_Transaction::SALE)
            )
        );
        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->forKey('customer')->onAttribute('firstName');
        $this->assertEquals(Braintree_Error_Codes::CUSTOMER_FIRST_NAME_IS_TOO_LONG, $errors[0]->code);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('number');
        $this->assertTrue(count($errors) > 0);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_EXPIRATION_DATE_IS_REQUIRED, $errors[0]->code);
    }

    function testRefund()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Braintree_Transaction::refund($transaction->id);
        $this->assertTrue($result->success);
        $refund = $result->transaction;
        $this->assertEquals(Braintree_Transaction::CREDIT, $refund->type);
        $this->assertEquals($transaction->id, $refund->refundedTransactionId);
        $this->assertEquals($refund->id, Braintree_Transaction::find($transaction->id)->refundId);
    }

    function testRefundWithPartialAmount()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Braintree_Transaction::refund($transaction->id, '50.00');
        $this->assertTrue($result->success);
        $this->assertEquals(Braintree_Transaction::CREDIT, $result->transaction->type);
        $this->assertEquals("50.00", $result->transaction->amount);
    }

    function testMultipleRefundsWithPartialAmounts()
    {
        $transaction = $this->createTransactionToRefund();

        $transaction1 = Braintree_Transaction::refund($transaction->id, '50.00')->transaction;
        $this->assertEquals(Braintree_Transaction::CREDIT, $transaction1->type);
        $this->assertEquals("50.00", $transaction1->amount);

        $transaction2 = Braintree_Transaction::refund($transaction->id, '50.00')->transaction;
        $this->assertEquals(Braintree_Transaction::CREDIT, $transaction2->type);
        $this->assertEquals("50.00", $transaction2->amount);

        $transaction = Braintree_Transaction::find($transaction->id);

        $expectedRefundIds = array($transaction1->id, $transaction2->id);
        $refundIds = $transaction->refundIds;
        sort($expectedRefundIds);
        sort($refundIds);

        $this->assertEquals($expectedRefundIds, $refundIds);
    }

    function testRefundWithUnsuccessfulPartialAmount()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Braintree_Transaction::refund($transaction->id, '150.00');
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('amount');
        $this->assertEquals(
            Braintree_Error_Codes::TRANSACTION_REFUND_AMOUNT_IS_TOO_LARGE,
            $errors[0]->code
        );
    }

    function testGatewayRejectionOnAvs()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'billing' => array(
                'streetAddress' => '200 2nd Street'
            ),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        Braintree_TestHelper::assertPrintable($result);
        $transaction = $result->transaction;

        $this->assertEquals(Braintree_Transaction::AVS, $transaction->gatewayRejectionReason);
    }

    function testGatewayRejectionOnAvsAndCvv()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'billing' => array(
                'postalCode' => '20000'
            ),
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '200'
            )
        ));

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(Braintree_Transaction::AVS_AND_CVV, $transaction->gatewayRejectionReason);
    }

    function testGatewayRejectionOnCvv()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv' => '200'
            )
        ));

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(Braintree_Transaction::CVV, $transaction->gatewayRejectionReason);
    }

    function testGatewayRejectionOnFraud()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '4000111111111511',
                'expirationDate' => '05/17',
                'cvv' => '333'
            )
        ));

        $this->assertFalse($result->success);
        $this->assertEquals(Braintree_Transaction::FRAUD, $result->transaction->gatewayRejectionReason);
    }

    function testSnapshotPlanIdAddOnsAndDiscountsFromSubscription()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $plan = Braintree_SubscriptionTestHelper::triallessPlan();
        $result = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $plan['id'],
            'addOns' => array(
                'add' => array(
                    array(
                        'amount' => '11.00',
                        'inheritedFromId' => 'increase_10',
                        'quantity' => 2,
                        'numberOfBillingCycles' => 5
                    ),
                    array(
                        'amount' => '21.00',
                        'inheritedFromId' => 'increase_20',
                        'quantity' => 3,
                        'numberOfBillingCycles' => 6
                    )
                ),
            ),
            'discounts' => array(
                'add' => array(
                    array(
                        'amount' => '7.50',
                        'inheritedFromId' => 'discount_7',
                        'quantity' => 2,
                        'neverExpires' => true
                    )
                )
            )
        ));

        $transaction = $result->subscription->transactions[0];

        $this->assertEquals($transaction->planId, $plan['id']);

        $addOns = $transaction->addOns;
        Braintree_SubscriptionTestHelper::sortModificationsById($addOns);

        $this->assertEquals($addOns[0]->amount, "11.00");
        $this->assertEquals($addOns[0]->id, "increase_10");
        $this->assertEquals($addOns[0]->quantity, 2);
        $this->assertEquals($addOns[0]->numberOfBillingCycles, 5);
        $this->assertFalse($addOns[0]->neverExpires);

        $this->assertEquals($addOns[1]->amount, "21.00");
        $this->assertEquals($addOns[1]->id, "increase_20");
        $this->assertEquals($addOns[1]->quantity, 3);
        $this->assertEquals($addOns[1]->numberOfBillingCycles, 6);
        $this->assertFalse($addOns[1]->neverExpires);

        $discounts = $transaction->discounts;
        $this->assertEquals($discounts[0]->amount, "7.50");
        $this->assertEquals($discounts[0]->id, "discount_7");
        $this->assertEquals($discounts[0]->quantity, 2);
        $this->assertEquals($discounts[0]->numberOfBillingCycles, null);
        $this->assertTrue($discounts[0]->neverExpires);
    }

    function createTransactionViaTr($regularParams, $trParams)
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $trData = Braintree_TransparentRedirect::transactionData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return Braintree_TestHelper::submitTrRequest(
            Braintree_Transaction::createTransactionUrl(),
            $regularParams,
            $trData
        );
    }

    function createTransactionToRefund()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options' => array('submitForSettlement' => true)
        ));
        Braintree_TestHelper::settle($transaction->id);
        return $transaction;
    }

    function createEscrowedTransaction()
    {
        $result = Braintree_Transaction::sale(array(
            'merchantAccountId' => Braintree_TestHelper::nonDefaultSubMerchantAccountId(),
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'holdInEscrow' => true
            ),
            'serviceFeeAmount' => '1.00'
        ));
        Braintree_TestHelper::escrow($result->transaction->id);
        return $result->transaction;
    }

    function testCardTypeIndicators()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => Braintree_CreditCardNumbers_CardTypeIndicators::PREPAID,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(Braintree_CreditCard::PREPAID_YES, $transaction->creditCardDetails->prepaid);

        $prepaid_card_transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => Braintree_CreditCardNumbers_CardTypeIndicators::COMMERCIAL,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(Braintree_CreditCard::COMMERCIAL_YES, $prepaid_card_transaction->creditCardDetails->commercial);

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => Braintree_CreditCardNumbers_CardTypeIndicators::PAYROLL,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(Braintree_CreditCard::PAYROLL_YES, $transaction->creditCardDetails->payroll);

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => Braintree_CreditCardNumbers_CardTypeIndicators::HEALTHCARE,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(Braintree_CreditCard::HEALTHCARE_YES, $transaction->creditCardDetails->healthcare);

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => Braintree_CreditCardNumbers_CardTypeIndicators::DURBIN_REGULATED,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(Braintree_CreditCard::DURBIN_REGULATED_YES, $transaction->creditCardDetails->durbinRegulated);

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => Braintree_CreditCardNumbers_CardTypeIndicators::DEBIT,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(Braintree_CreditCard::DEBIT_YES, $transaction->creditCardDetails->debit);

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => Braintree_CreditCardNumbers_CardTypeIndicators::ISSUING_BANK,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals("NETWORK ONLY", $transaction->creditCardDetails->issuingBank);

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => Braintree_CreditCardNumbers_CardTypeIndicators::COUNTRY_OF_ISSUANCE,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals("USA", $transaction->creditCardDetails->countryOfIssuance);
    }


    function testCreate_withVaultedPayPal()
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
        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodToken' => $paymentMethodToken,
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
    }

    function testCreate_withFuturePayPal()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayeeEmail()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'paypalAccount' => array(
                'payeeEmail' => 'payee@example.com'
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->assertNotNull($transaction->paypalDetails->payeeEmail);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayeeEmailInOptions()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'paypalAccount' => array(),
            'options' => array(
                'payeeEmail' => 'payee@example.com'
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->assertNotNull($transaction->paypalDetails->payeeEmail);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayeeEmailInOptionsPayPal()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'paypalAccount' => array(),
            'options' => array(
                'paypal' => array(
                    'payeeEmail' => 'payee@example.com'
                )
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->assertNotNull($transaction->paypalDetails->payeeEmail);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayPalCustomField()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'paypalAccount' => array(),
            'options' => array(
                'paypal' => array(
                    'customField' => 'custom field stuff'
                )
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('custom field stuff', $transaction->paypalDetails->customField);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayPalReturnsPaymentInstrumentType()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_PaymentInstrumentType::PAYPAL_ACCOUNT, $transaction->paymentInstrumentType);
        $this->assertNotNull($transaction->paypalDetails->debugId);
    }

    function testCreate_withFuturePayPalAndVault()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'storeInVault' => true
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $foundPayPalAccount = Braintree_PaymentMethod::find($paymentMethodToken);
        $this->assertEquals($paymentMethodToken, $foundPayPalAccount->token);
    }

    function testCreate_withOnetimePayPal()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withOnetimePayPalAndDoesNotVault()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token' => $paymentMethodToken
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'storeInVault' => true
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->setExpectedException('Braintree_Exception_NotFound');
        Braintree_PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayPalAndSubmitForSettlement()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;
        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree_Transaction::SETTLING, $transaction->status);
    }

    function testCreate_withPayPalHandlesBadUnvalidatedNonces()
    {
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'consent_code' => 'PAYPAL_CONSENT_CODE'
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->forKey('paypalAccount')->errors;
        $this->assertEquals(Braintree_Error_Codes::PAYPAL_ACCOUNT_CANNOT_HAVE_BOTH_ACCESS_TOKEN_AND_CONSENT_CODE, $errors[0]->code);
    }

    function testCreate_withPayPalHandlesNonExistentNonces()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => 'NON_EXISTENT_NONCE',
            'options' => array(
                'submitForSettlement' => true
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->errors;
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_PAYMENT_METHOD_NONCE_UNKNOWN, $errors[0]->code);
    }

    function testVoid_withPayPal()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $voided_transaction = Braintree_Transaction::voidNoValidate($result->transaction->id);
        $this->assertEquals(Braintree_Transaction::VOIDED, $voided_transaction->status);
    }

    function testVoid_failsOnDeclinedPayPal()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$decline,
            'paymentMethodNonce' => $nonce
        ));
        $this->setExpectedException('Braintree_Exception_ValidationsFailed');
        Braintree_Transaction::voidNoValidate($result->transaction->id);
    }

    function testRefund_withPayPal()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $transactionResult = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        Braintree_TestHelper::settle($transactionResult->transaction->id);

        $result = Braintree_Transaction::refund($transactionResult->transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals($result->transaction->type, Braintree_Transaction::CREDIT);
    }

    function testRefund_withPayPalAssignsRefundId()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $transactionResult = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        $originalTransaction = $transactionResult->transaction;
        Braintree_TestHelper::settle($transactionResult->transaction->id);

        $result = Braintree_Transaction::refund($transactionResult->transaction->id);
        $this->assertTrue($result->success);
        $refundTransaction = $result->transaction;
        $updatedOriginalTransaction = Braintree_Transaction::find($originalTransaction->id);
        $this->assertEquals($refundTransaction->id, $updatedOriginalTransaction->refundId);
    }

    function testRefund_withPayPalAssignsRefundedTransactionId()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $transactionResult = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        $originalTransaction = $transactionResult->transaction;
        Braintree_TestHelper::settle($transactionResult->transaction->id);

        $result = Braintree_Transaction::refund($transactionResult->transaction->id);
        $this->assertTrue($result->success);
        $refundTransaction = $result->transaction;
        $this->assertEquals($refundTransaction->refundedTransactionId, $originalTransaction->id);
    }

    function testRefund_withPayPalFailsifAlreadyRefunded()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $transactionResult = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        Braintree_TestHelper::settle($transactionResult->transaction->id);

        $firstRefund = Braintree_Transaction::refund($transactionResult->transaction->id);
        $this->assertTrue($firstRefund->success);
        $secondRefund = Braintree_Transaction::refund($transactionResult->transaction->id);
        $this->assertFalse($secondRefund->success);
        $errors = $secondRefund->errors->forKey('transaction')->errors;
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_HAS_ALREADY_BEEN_REFUNDED, $errors[0]->code);
    }

    function testRefund_withPayPalFailsIfNotSettled()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $transactionResult = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($transactionResult->success);

        $result = Braintree_Transaction::refund($transactionResult->transaction->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->errors;
        $this->assertEquals(Braintree_Error_Codes::TRANSACTION_CANNOT_REFUND_UNLESS_SETTLED, $errors[0]->code);
    }

    function testRefund_partialWithPayPal()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $transactionResult = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        Braintree_TestHelper::settle($transactionResult->transaction->id);

        $result = Braintree_Transaction::refund(
            $transactionResult->transaction->id,
            $transactionResult->transaction->amount / 2
        );

        $this->assertTrue($result->success);
        $this->assertEquals($result->transaction->type, Braintree_Transaction::CREDIT);
        $this->assertEquals($result->transaction->amount, $transactionResult->transaction->amount / 2);
    }

    function testRefund_multiplePartialWithPayPal()
    {
        $nonce = Braintree_Test_Nonces::$paypalOneTimePayment;

        $transactionResult = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        $originalTransaction = $transactionResult->transaction;
        Braintree_TestHelper::settle($originalTransaction->id);

        $firstRefund = Braintree_Transaction::refund(
            $transactionResult->transaction->id,
            $transactionResult->transaction->amount / 2
        );
        $this->assertTrue($firstRefund->success);
        $firstRefundTransaction = $firstRefund->transaction;

        $secondRefund = Braintree_Transaction::refund(
            $transactionResult->transaction->id,
            $transactionResult->transaction->amount / 2
        );
        $this->assertTrue($secondRefund->success);
        $secondRefundTransaction = $secondRefund->transaction;


        $updatedOriginalTransaction = Braintree_Transaction::find($originalTransaction->id);
        $expectedRefundIds = array($secondRefundTransaction->id, $firstRefundTransaction->id);

        $updatedRefundIds = $updatedOriginalTransaction->refundIds;

        $this->assertTrue(in_array($expectedRefundIds[0],$updatedRefundIds));
        $this->assertTrue(in_array($expectedRefundIds[1],$updatedRefundIds));
    }

    function testIncludeProcessorSettlementResponseForSettlementDeclinedTransaction()
    {
        $result = Braintree_Transaction::sale(array(
            "paymentMethodNonce" => Braintree_Test_Nonces::$paypalFuturePayment,
            "amount" => "100",
            "options" => array(
                "submitForSettlement" => true
            )
        ));

        $this->assertTrue($result->success);

        $transaction = $result->transaction;
        Braintree_TestHelper::settlementDecline($transaction->id);

        $inline_transaction = Braintree_Transaction::find($transaction->id);
        $this->assertEquals($inline_transaction->status, Braintree_Transaction::SETTLEMENT_DECLINED);
        $this->assertEquals($inline_transaction->processorSettlementResponseCode, "4001");
        $this->assertEquals($inline_transaction->processorSettlementResponseText, "Settlement Declined");
    }

    function testIncludeProcessorSettlementResponseForSettlementPendingTransaction()
    {
        $result = Braintree_Transaction::sale(array(
            "paymentMethodNonce" => Braintree_Test_Nonces::$paypalFuturePayment,
            "amount" => "100",
            "options" => array(
                "submitForSettlement" => true
            )
        ));

        $this->assertTrue($result->success);

        $transaction = $result->transaction;
        Braintree_TestHelper::settlementPending($transaction->id);

        $inline_transaction = Braintree_Transaction::find($transaction->id);
        $this->assertEquals($inline_transaction->status, Braintree_Transaction::SETTLEMENT_PENDING);
        $this->assertEquals($inline_transaction->processorSettlementResponseCode, "4002");
        $this->assertEquals($inline_transaction->processorSettlementResponseText, "Settlement Pending");
    }

    function testSale_withLodgingIndustryData()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'industry' => array(
                'industryType' => Braintree_Transaction::LODGING_INDUSTRY,
                'data' => array(
                    'folioNumber' => 'aaa',
                    'checkInDate' => '2014-07-07',
                    'checkOutDate' => '2014-07-09',
                    'roomRate' => '239.00'
                )
            )
        ));
        $this->assertTrue($result->success);
    }

    function testSale_withLodgingIndustryDataValidation()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'industry' => array(
                'industryType' => Braintree_Transaction::LODGING_INDUSTRY,
                'data' => array(
                    'folioNumber' => 'aaa',
                    'checkInDate' => '2014-07-07',
                    'checkOutDate' => '2014-06-09',
                    'roomRate' => '239.00'
                )
            )
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $errors = $result->errors->forKey('transaction')->forKey('industry')->onAttribute('checkOutDate');
        $this->assertEquals(Braintree_Error_Codes::INDUSTRY_DATA_LODGING_CHECK_OUT_DATE_MUST_FOLLOW_CHECK_IN_DATE, $errors[0]->code);
    }

    function testSale_withTravelCruiseIndustryData()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'industry' => array(
                'industryType' => Braintree_Transaction::TRAVEL_AND_CRUISE_INDUSTRY,
                'data' => array(
                    'travelPackage' => 'flight',
                    'departureDate' => '2014-07-07',
                    'lodgingCheckInDate' => '2014-07-09',
                    'lodgingCheckOutDate' => '2014-07-10',
                    'lodgingName' => 'Disney',
                )
            )
        ));
        $this->assertTrue($result->success);
    }

    function testSale_withTravelCruiseIndustryDataValidation()
    {
        $result = Braintree_Transaction::sale(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'industry' => array(
                'industryType' => Braintree_Transaction::TRAVEL_AND_CRUISE_INDUSTRY,
                'data' => array(
                    'travelPackage' => 'invalid',
                    'departureDate' => '2014-07-07',
                    'lodgingCheckInDate' => '2014-07-09',
                    'lodgingCheckOutDate' => '2014-07-10',
                    'lodgingName' => 'Disney',
                )
            )
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $errors = $result->errors->forKey('transaction')->forKey('industry')->onAttribute('travelPackage');
        $this->assertEquals(Braintree_Error_Codes::INDUSTRY_DATA_TRAVEL_CRUISE_TRAVEL_PACKAGE_IS_INVALID, $errors[0]->code);
    }
}
