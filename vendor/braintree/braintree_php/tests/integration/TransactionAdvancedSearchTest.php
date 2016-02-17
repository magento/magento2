<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_TransactionAdvancedSearchTest extends PHPUnit_Framework_TestCase
{
    function testNoMatches()
    {
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::billingFirstName()->is('thisnameisnotreal')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_noRequestsWhenIterating()
    {
        $resultsReturned = false;
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::billingFirstName()->is('thisnameisnotreal')
        ));

        foreach($collection as $transaction) {
            $resultsReturned = true;
            break;
        }

        $this->assertSame(0, $collection->maximumCount());
        $this->assertEquals(false, $resultsReturned);
    }

    function testSearchOnTextFields()
    {
        $firstName  = 'Tim' . rand();
        $token      = 'creditcard' . rand();
        $customerId = 'customer' . rand();

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'Tom Smith',
                'token'          => $token,
            ),
            'billing' => array(
                'company'         => 'Braintree',
                'countryName'     => 'United States of America',
                'extendedAddress' => 'Suite 123',
                'firstName'       => $firstName,
                'lastName'        => 'Smith',
                'locality'        => 'Chicago',
                'postalCode'      => '12345',
                'region'          => 'IL',
                'streetAddress'   => '123 Main St'
            ),
            'customer' => array(
                'company'   => 'Braintree',
                'email'     => 'smith@example.com',
                'fax'       => '5551231234',
                'firstName' => 'Tom',
                'id'        => $customerId,
                'lastName'  => 'Smith',
                'phone'     => '5551231234',
                'website'   => 'http://example.com',
            ),
            'options' => array(
                'storeInVault' => true
            ),
            'orderId' => 'myorder',
            'shipping' => array(
                'company'         => 'Braintree P.S.',
                'countryName'     => 'Mexico',
                'extendedAddress' => 'Apt 456',
                'firstName'       => 'Thomas',
                'lastName'        => 'Smithy',
                'locality'        => 'Braintree',
                'postalCode'      => '54321',
                'region'          => 'MA',
                'streetAddress'   => '456 Road'
            ),
        ));

        $search_criteria = array(
          'billingCompany' => "Braintree",
          'billingCountryName' => "United States of America",
          'billingExtendedAddress' => "Suite 123",
          'billingFirstName' => $firstName,
          'billingLastName' => "Smith",
          'billingLocality' => "Chicago",
          'billingPostalCode' => "12345",
          'billingRegion' => "IL",
          'billingStreetAddress' => "123 Main St",
          'creditCardCardholderName' => "Tom Smith",
          'creditCardExpirationDate' => "05/2012",
          'creditCardNumber' => Braintree_Test_CreditCardNumbers::$visa,
          'customerCompany' => "Braintree",
          'customerEmail' => "smith@example.com",
          'customerFax' => "5551231234",
          'customerFirstName' => "Tom",
          'customerId' => $customerId,
          'customerLastName' => "Smith",
          'customerPhone' => "5551231234",
          'customerWebsite' => "http://example.com",
          'orderId' => "myorder",
          'paymentMethodToken' => $token,
          'processorAuthorizationCode' => $transaction->processorAuthorizationCode,
          'shippingCompany' => "Braintree P.S.",
          'shippingCountryName' => "Mexico",
          'shippingExtendedAddress' => "Apt 456",
          'shippingFirstName' => "Thomas",
          'shippingLastName' => "Smithy",
          'shippingLocality' => "Braintree",
          'shippingPostalCode' => "54321",
          'shippingRegion' => "MA",
          'shippingStreetAddress' => "456 Road"
        );

        $query = array(Braintree_TransactionSearch::id()->is($transaction->id));
        foreach ($search_criteria AS $criterion => $value) {
            $query[] = Braintree_TransactionSearch::$criterion()->is($value);
        }

        $collection = Braintree_Transaction::search($query);

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        foreach ($search_criteria AS $criterion => $value) {
            $collection = Braintree_Transaction::search(array(
                Braintree_TransactionSearch::$criterion()->is($value),
                Braintree_TransactionSearch::id()->is($transaction->id)
            ));
            $this->assertEquals(1, $collection->maximumCount());
            $this->assertEquals($transaction->id, $collection->firstItem()->id);

            $collection = Braintree_Transaction::search(array(
                Braintree_TransactionSearch::$criterion()->is('invalid_attribute'),
                Braintree_TransactionSearch::id()->is($transaction->id)
            ));
            $this->assertEquals(0, $collection->maximumCount());
        }
    }

    function testIs()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->is('tom smith')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->is('somebody else')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function testIsNot()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->isNot('somebody else')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->isNot('tom smith')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function testEndsWith()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->endsWith('m smith')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->endsWith('tom s')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function testStartsWith()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->startsWith('tom s')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->startsWith('m smith')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function testContains()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->contains('m sm')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->contains('something else')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_createdUsing()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdUsing()->is(Braintree_Transaction::FULL_INFORMATION)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdUsing()->in(
                array(Braintree_Transaction::FULL_INFORMATION, Braintree_Transaction::TOKEN)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdUsing()->in(array(Braintree_Transaction::TOKEN))
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_createdUsing_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for created_using: noSuchCreatedUsing');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::createdUsing()->is('noSuchCreatedUsing')
        ));
    }

    function test_multipleValueNode_creditCardCustomerLocation()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCustomerLocation()->is(Braintree_CreditCard::US)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCustomerLocation()->in(
                array(Braintree_CreditCard::US, Braintree_CreditCard::INTERNATIONAL)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCustomerLocation()->in(array(Braintree_CreditCard::INTERNATIONAL))
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_creditCardCustomerLocation_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for credit_card_customer_location: noSuchLocation');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCustomerLocation()->is('noSuchLocation')
        ));
    }

    function test_multipleValueNode_merchantAccountId()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::merchantAccountId()->is($transaction->merchantAccountId)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::merchantAccountId()->in(
                array($transaction->merchantAccountId, "bogus_merchant_account_id")
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::merchantAccountId()->is("bogus_merchant_account_id")
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_creditCardType()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardType()->is($transaction->creditCardDetails->cardType)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardType()->in(
                array($transaction->creditCardDetails->cardType, Braintree_CreditCard::CHINA_UNION_PAY)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardType()->is(Braintree_CreditCard::CHINA_UNION_PAY)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_creditCardType_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for credit_card_card_type: noSuchCardType');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardType()->is('noSuchCardType')
        ));
    }

    function test_multipleValueNode_status()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::status()->is($transaction->status)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::status()->in(
                array($transaction->status, Braintree_Transaction::SETTLED)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::status()->is(Braintree_Transaction::SETTLED)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_status_authorizationExpired()
    {
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::status()->is(Braintree_Transaction::AUTHORIZATION_EXPIRED)
        ));
        $this->assertGreaterThan(0, $collection->maximumCount());
        $this->assertEquals(Braintree_Transaction::AUTHORIZATION_EXPIRED, $collection->firstItem()->status);
    }

    function test_multipleValueNode_status_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for status: noSuchStatus');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::status()->is('noSuchStatus')
        ));
    }

    function test_multipleValueNode_source()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::source()->is(Braintree_Transaction::API)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::source()->in(
                array(Braintree_Transaction::API, Braintree_Transaction::RECURRING)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::source()->is(Braintree_Transaction::RECURRING)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_source_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for source: noSuchSource');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::source()->is('noSuchSource')
        ));
    }

    function test_multipleValueNode_type()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Joe Everyman' . rand(),
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $sale = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodToken' => $creditCard->token,
            'options' => array('submitForSettlement' => true)
        ));
        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/transactions/' . $sale->id . '/settle';
        $http->put($path);
        $refund = Braintree_Transaction::refund($sale->id)->transaction;

        $credit = Braintree_Transaction::creditNoValidate(array(
            'amount' => '100.00',
            'paymentMethodToken' => $creditCard->token
        ));


        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->is($sale->type)
        ));
        $this->assertEquals(1, $collection->maximumCount());


        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->in(
                array($sale->type, $credit->type)
            )
        ));
        $this->assertEquals(3, $collection->maximumCount());


        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->is($credit->type)
        ));
        $this->assertEquals(2, $collection->maximumCount());
    }

    function test_multipleValueNode_type_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for type: noSuchType');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::type()->is('noSuchType')
        ));
    }

    function test_multipleValueNode_type_withRefund()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Joe Everyman' . rand(),
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $sale = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodToken' => $creditCard->token,
            'options' => array('submitForSettlement' => true)
        ));
        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/transactions/' . $sale->id . '/settle';
        $http->put($path);
        $refund = Braintree_Transaction::refund($sale->id)->transaction;

        $credit = Braintree_Transaction::creditNoValidate(array(
            'amount' => '100.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->is($credit->type),
            Braintree_TransactionSearch::refund()->is(True)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($refund->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->is($credit->type),
            Braintree_TransactionSearch::refund()->is(False)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($credit->id, $collection->firstItem()->id);
    }

    function test_rangeNode_amount()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Jane Everywoman' . rand(),
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $t_1000 = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $t_1500 = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1500.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $t_1800 = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1800.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::amount()->greaterThanOrEqualTo('1700')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($t_1800->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::amount()->lessThanOrEqualTo('1250')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($t_1000->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::amount()->between('1100', '1600')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($t_1500->id, $collection->firstItem()->id);
    }

    private function runDisbursementDateSearchTests($disbursementDateString, $comparison)
    {
        $knownDepositId = "deposittransaction";
        $now = new DateTime($disbursementDateString);
        $past = clone $now;
        $past->modify("-1 hour");
        $future = clone $now;
        $future->modify("+1 hour");

        $collections = array(
            'future' => Braintree_Transaction::search(array(
                Braintree_TransactionSearch::id()->is($knownDepositId),
                $comparison($future)
            )),
            'now' => Braintree_Transaction::search(array(
                Braintree_TransactionSearch::id()->is($knownDepositId),
                $comparison($now)
            )),
            'past' => Braintree_Transaction::search(array(
                Braintree_TransactionSearch::id()->is($knownDepositId),
                $comparison($past)
            ))
        );
        return $collections;
    }

    function test_rangeNode_disbursementDate_lessThanOrEqualTo()
    {
        $compareLessThan = function($time) {
            return Braintree_TransactionSearch::disbursementDate()->lessThanOrEqualTo($time);
        };
        $collection = $this->runDisbursementDateSearchTests("2013-04-10", $compareLessThan);

        $this->assertEquals(0, $collection['past']->maximumCount());
        $this->assertEquals(1, $collection['now']->maximumCount());
        $this->assertEquals(1, $collection['future']->maximumCount());
    }

    function test_rangeNode_disbursementDate_GreaterThanOrEqualTo()
    {
        $comparison = function($time) {
            return Braintree_TransactionSearch::disbursementDate()->GreaterThanOrEqualTo($time);
        };
        $collection = $this->runDisbursementDateSearchTests("2013-04-11", $comparison);

        $this->assertEquals(1, $collection['past']->maximumCount());
        $this->assertEquals(0, $collection['now']->maximumCount());
        $this->assertEquals(0, $collection['future']->maximumCount());
    }

    function test_rangeNode_disbursementDate_between()
    {
        $knownId = "deposittransaction";

        $now = new DateTime("2013-04-10");
        $past = clone $now;
        $past->modify("-1 day");
        $future = clone $now;
        $future->modify("+1 day");
        $future2 = clone $now;
        $future2->modify("+2 days");

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disbursementDate()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disbursementDate()->between($now, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disbursementDate()->between($past, $now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disbursementDate()->between($future, $future2)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_disbursementDate_is()
    {
        $knownId = "deposittransaction";

        $now = new DateTime("2013-04-10");
        $past = clone $now;
        $past->modify("-1 day");
        $future = clone $now;
        $future->modify("+1 day");
        $future2 = clone $now;
        $future2->modify("+2 days");

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disbursementDate()->is($past)
        ));
        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disbursementDate()->is($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disbursementDate()->is($future)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    private function rundisputeDateSearchTests($disputeDateString, $comparison)
    {
        $knowndisputedId = "disputedtransaction";
        $now = new DateTime($disputeDateString);
        $past = clone $now;
        $past->modify("-1 hour");
        $future = clone $now;
        $future->modify("+1 hour");

        $collections = array(
            'future' => Braintree_Transaction::search(array(
                Braintree_TransactionSearch::id()->is($knowndisputedId),
                $comparison($future)
            )),
            'now' => Braintree_Transaction::search(array(
                Braintree_TransactionSearch::id()->is($knowndisputedId),
                $comparison($now)
            )),
            'past' => Braintree_Transaction::search(array(
                Braintree_TransactionSearch::id()->is($knowndisputedId),
                $comparison($past)
            ))
        );
        return $collections;
    }

    function test_rangeNode_disputeDate_lessThanOrEqualTo()
    {
        $compareLessThan = function($time) {
            return Braintree_TransactionSearch::disputeDate()->lessThanOrEqualTo($time);
        };
        $collection = $this->rundisputeDateSearchTests("2014-03-01", $compareLessThan);

        $this->assertEquals(0, $collection['past']->maximumCount());
        $this->assertEquals(1, $collection['now']->maximumCount());
        $this->assertEquals(1, $collection['future']->maximumCount());
    }

    function test_rangeNode_disputeDate_GreaterThanOrEqualTo()
    {
        $comparison = function($time) {
            return Braintree_TransactionSearch::disputeDate()->GreaterThanOrEqualTo($time);
        };
        $collection = $this->rundisputeDateSearchTests("2014-03-01", $comparison);

        $this->assertEquals(1, $collection['past']->maximumCount());
        $this->assertEquals(1, $collection['now']->maximumCount());
        $this->assertEquals(1, $collection['future']->maximumCount());
    }

    function test_rangeNode_disputeDate_between()
    {
        $knownId = "disputedtransaction";

        $now = new DateTime("2014-03-01");
        $past = clone $now;
        $past->modify("-1 day");
        $future = clone $now;
        $future->modify("+1 day");
        $future2 = clone $now;
        $future2->modify("+2 days");

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disputeDate()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disputeDate()->between($now, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disputeDate()->between($past, $now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disputeDate()->between($future, $future2)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_disputeDate_is()
    {
        $knownId = "disputedtransaction";

        $now = new DateTime("2014-03-01");
        $past = clone $now;
        $past->modify("-1 day");
        $future = clone $now;
        $future->modify("+1 day");
        $future2 = clone $now;
        $future2->modify("+2 days");

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disputeDate()->is($past)
        ));
        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disputeDate()->is($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($knownId),
            Braintree_TransactionSearch::disputeDate()->is($future)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_createdAt_lessThanOrEqualTo()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everywoman' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = clone $transaction->createdAt;
        $past->modify("-1 hour");
        $now = $transaction->createdAt;
        $future = clone $transaction->createdAt;
        $future->modify("+1 hour");

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->lessThanOrEqualTo($future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->lessThanOrEqualTo($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->lessThanOrEqualTo($past)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_createdAt_GreaterThanOrEqualTo()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everyman' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = clone $transaction->createdAt;
        $past->modify("-1 hour");
        $now = $transaction->createdAt;
        $future = clone $transaction->createdAt;
        $future->modify("+1 hour");

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->GreaterThanOrEqualTo($future)
        ));
        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->GreaterThanOrEqualTo($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->GreaterThanOrEqualTo($past)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }



    function test_rangeNode_createdAt_between()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everyman' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = clone $transaction->createdAt;
        $past->modify("-1 hour");
        $now = $transaction->createdAt;
        $future = clone $transaction->createdAt;
        $future->modify("+1 hour");
        $future2 = clone $transaction->createdAt;
        $future2->modify("+1 day");

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->between($now, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->between($past, $now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->between($future, $future2)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_createdAt_is()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everyman' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = clone $transaction->createdAt;
        $past->modify("-1 hour");
        $now = $transaction->createdAt;
        $future = clone $transaction->createdAt;
        $future->modify("+1 hour");

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->is($future)
        ));
        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->is($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->is($past)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_createdAt_convertLocalToUTC()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Pingu Penguin' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("US/Pacific"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("US/Pacific"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_createdAt_handlesUTCDateTimes()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Pingu Penguin' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_authorizationExpiredAt()
    {
        $two_days_ago = date_create("now -2 days", new DateTimeZone("UTC"));
        $yesterday = date_create("now -1 day", new DateTimeZone("UTC"));
        $tomorrow = date_create("now +1 day", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::authorizationExpiredAt()->between($two_days_ago, $yesterday)
        ));

        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::authorizationExpiredAt()->between($yesterday, $tomorrow)
        ));

        $this->assertGreaterThan(0, $collection->maximumCount());
        $this->assertEquals(Braintree_Transaction::AUTHORIZATION_EXPIRED, $collection->firstItem()->status);
    }

    function test_rangeNode_authorizedAt()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/12'
            )
        ));

        $twenty_min_ago = date_create("now -20 minutes", new DateTimeZone("UTC"));
        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::authorizedAt()->between($twenty_min_ago, $ten_min_ago)
        ));

        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::authorizedAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_failedAt()
    {
        $transaction = Braintree_Transaction::sale(array(
            'amount' => '3000.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/12'
            )
        ))->transaction;

        $twenty_min_ago = date_create("now -20 minutes", new DateTimeZone("UTC"));
        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::failedAt()->between($twenty_min_ago, $ten_min_ago)
        ));

        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::failedAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_gatewayRejectedAt()
    {
        $old_merchant_id = Braintree_Configuration::merchantId();
        $old_public_key = Braintree_Configuration::publicKey();
        $old_private_key = Braintree_Configuration::privateKey();

        Braintree_Configuration::merchantId('processing_rules_merchant_id');
        Braintree_Configuration::publicKey('processing_rules_public_key');
        Braintree_Configuration::privateKey('processing_rules_private_key');

        $transaction = Braintree_Transaction::sale(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/12',
                'cvv' => '200'
            )
        ))->transaction;

        $twenty_min_ago = date_create("now -20 minutes", new DateTimeZone("UTC"));
        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::gatewayRejectedAt()->between($twenty_min_ago, $ten_min_ago)
        ));

        $firstCount = $collection->maximumCount();

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::gatewayRejectedAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $secondCount = $collection->maximumCount();
        $firstId = $collection->firstItem()->id;

        Braintree_Configuration::merchantId($old_merchant_id);
        Braintree_Configuration::publicKey($old_public_key);
        Braintree_Configuration::privateKey($old_private_key);

        $this->assertEquals(0, $firstCount);
        $this->assertEquals(1, $secondCount);
        $this->assertEquals($transaction->id, $firstId);
    }

    function test_rangeNode_processorDeclinedAt()
    {
        $transaction = Braintree_Transaction::sale(array(
            'amount' => '2000.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/12'
            )
        ))->transaction;

        $twenty_min_ago = date_create("now -20 minutes", new DateTimeZone("UTC"));
        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::processorDeclinedAt()->between($twenty_min_ago, $ten_min_ago)
        ));

        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::processorDeclinedAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_settledAt()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'submitForSettlement' => true
            )
        ));

        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/transactions/' . $transaction->id . '/settle';
        $http->put($path);
        $transaction = Braintree_Transaction::find($transaction->id);

        $twenty_min_ago = date_create("now -20 minutes", new DateTimeZone("UTC"));
        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::settledAt()->between($twenty_min_ago, $ten_min_ago)
        ));

        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::settledAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_submittedForSettlementAt()
    {
        $transaction = Braintree_Transaction::sale(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'submitForSettlement' => true
            )
        ))->transaction;

        $twenty_min_ago = date_create("now -20 minutes", new DateTimeZone("UTC"));
        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::submittedForSettlementAt()->between($twenty_min_ago, $ten_min_ago)
        ));

        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::submittedForSettlementAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_voidedAt()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/12'
            )
        ));

        $transaction = Braintree_Transaction::void($transaction->id)->transaction;

        $twenty_min_ago = date_create("now -20 minutes", new DateTimeZone("UTC"));
        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::voidedAt()->between($twenty_min_ago, $ten_min_ago)
        ));

        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::voidedAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_canSearchOnMultipleStatuses()
    {
        $transaction = Braintree_Transaction::sale(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'number' => '4111111111111111',
                'expirationDate' => '05/12'
            ),
            'options' => array(
                'submitForSettlement' => true
            )
        ))->transaction;

        $twenty_min_ago = date_create("now -20 minutes", new DateTimeZone("UTC"));
        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::authorizedAt()->between($twenty_min_ago, $ten_min_ago),
            Braintree_TransactionSearch::submittedForSettlementAt()->between($twenty_min_ago, $ten_min_ago)
        ));

        $this->assertEquals(0, $collection->maximumCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::authorizedAt()->between($ten_min_ago, $ten_min_from_now),
            Braintree_TransactionSearch::submittedForSettlementAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_advancedSearchGivesIterableResult()
    {
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardNumber()->startsWith("411111")
        ));
        $this->assertTrue($collection->maximumCount() > 100);

        $arr = array();
        foreach($collection as $transaction) {
            array_push($arr, $transaction->id);
        }
        $unique_transaction_ids = array_unique(array_values($arr));
        $this->assertEquals($collection->maximumCount(), count($unique_transaction_ids));
    }

    function test_handles_search_timeout()
    {
        $this->setExpectedException('Braintree_Exception_DownForMaintenance');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::amount()->is('-5')
        ));
    }

    function testHandlesPayPalAccounts()
    {
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN'
            )
        ));

        $result = Braintree_Transaction::sale(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($result->success);
        $paypalDetails = $result->transaction->paypalDetails;

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::paypalPaymentId()->is($paypalDetails->paymentId),
            Braintree_TransactionSearch::paypalAuthorizationId()->is($paypalDetails->authorizationId),
            Braintree_TransactionSearch::paypalPayerEmail()->is($paypalDetails->payerEmail)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($result->transaction->id, $collection->firstItem()->id);

    }
}
