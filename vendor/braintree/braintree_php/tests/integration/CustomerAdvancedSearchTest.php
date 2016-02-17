<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class Braintree_CustomerAdvancedSearchTest extends PHPUnit_Framework_TestCase
{
    function test_noMatches()
    {
        $collection = Braintree_Customer::search(array(
            Braintree_CustomerSearch::company()->is('badname')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_noRequestsWhenIterating()
    {
        $resultsReturned = false;
        $collection = Braintree_Customer::search(array(
            Braintree_CustomerSearch::firstName()->is('badname')
        ));

        foreach($collection as $customer) {
            $resultsReturned = true;
            break;
        }

        $this->assertSame(0, $collection->maximumCount());
        $this->assertEquals(false, $resultsReturned);
    }

    function test_findDuplicateCardsGivenPaymentMethodToken()
    {
        $creditCardRequest = array('number' => '63049580000009', 'expirationDate' => '05/2012');

        $jim = Braintree_Customer::create(array('firstName' => 'Jim', 'creditCard' => $creditCardRequest))->customer;
        $joe = Braintree_Customer::create(array('firstName' => 'Joe', 'creditCard' => $creditCardRequest))->customer;

        $query = array(Braintree_CustomerSearch::paymentMethodTokenWithDuplicates()->is($jim->creditCards[0]->token));
        $collection = Braintree_Customer::search($query);

        $customerIds = array();
        foreach($collection as $customer)
        {
            $customerIds[] = $customer->id;
        }

        $this->assertTrue(in_array($jim->id, $customerIds));
        $this->assertTrue(in_array($joe->id, $customerIds));
    }

    function test_searchOnTextFields()
    {
        $token  = 'cctoken' . rand();

        $search_criteria = array(
            'firstName' => 'Timmy',
            'lastName' => 'OToole',
            'company' => 'OToole and Son(s)' . rand(),
            'email' => 'timmy@example.com',
            'website' => 'http://example.com',
            'phone' => '3145551234',
            'fax' => '3145551235',
            'cardholderName' => 'Tim Toole',
            'creditCardExpirationDate' => '05/2010',
            'creditCardNumber' => '4111111111111111',
            'paymentMethodToken' => $token,
            'addressFirstName' => 'Thomas',
            'addressLastName' => 'Otool',
            'addressStreetAddress' => '1 E Main St',
            'addressExtendedAddress' => 'Suite 3',
            'addressLocality' => 'Chicago',
            'addressRegion' => 'Illinois',
            'addressPostalCode' => '60622',
            'addressCountryName' => 'United States of America'
        );

        $customer = Braintree_Customer::createNoValidate(array(
            'firstName' => $search_criteria['firstName'],
            'lastName' => $search_criteria['lastName'],
            'company' => $search_criteria['company'],
            'email' => $search_criteria['email'],
            'fax' => $search_criteria['fax'],
            'phone' => $search_criteria['phone'],
            'website' => $search_criteria['website'],
            'creditCard' => array(
                'cardholderName' => 'Tim Toole',
                'number' => '4111111111111111',
                'expirationDate' => $search_criteria['creditCardExpirationDate'],
                'token' => $token,
                'billingAddress' => array(
                    'firstName' => $search_criteria['addressFirstName'],
                    'lastName' => $search_criteria['addressLastName'],
                    'streetAddress' => $search_criteria['addressStreetAddress'],
                    'extendedAddress' => $search_criteria['addressExtendedAddress'],
                    'locality' => $search_criteria['addressLocality'],
                    'region' => $search_criteria['addressRegion'],
                    'postalCode' => $search_criteria['addressPostalCode'],
                    'countryName' => 'United States of America'
                )
            )
        ));

        $query = array(Braintree_CustomerSearch::id()->is($customer->id));
        foreach ($search_criteria AS $criterion => $value) {
            $query[] = Braintree_CustomerSearch::$criterion()->is($value);
        }

        $collection = Braintree_Customer::search($query);

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($customer->id, $collection->firstItem()->id);

        foreach ($search_criteria AS $criterion => $value) {
            $collection = Braintree_Customer::search(array(
                Braintree_CustomerSearch::$criterion()->is($value),
                Braintree_CustomerSearch::id()->is($customer->id)
            ));
            $this->assertEquals(1, $collection->maximumCount());
            $this->assertEquals($customer->id, $collection->firstItem()->id);

            $collection = Braintree_Customer::search(array(
                Braintree_CustomerSearch::$criterion()->is('invalid_attribute'),
                Braintree_CustomerSearch::id()->is($customer->id)
            ));
            $this->assertEquals(0, $collection->maximumCount());
        }
    }

    function test_createdAt()
    {
        $customer = Braintree_Customer::createNoValidate();

        $past = clone $customer->createdAt;
        $past->modify("-1 hour");
        $future = clone $customer->createdAt;
        $future->modify("+1 hour");

        $collection = Braintree_Customer::search(array(
            Braintree_CustomerSearch::id()->is($customer->id),
            Braintree_CustomerSearch::createdAt()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($customer->id, $collection->firstItem()->id);

        $collection = Braintree_Customer::search(array(
            Braintree_CustomerSearch::id()->is($customer->id),
            Braintree_CustomerSearch::createdAt()->lessThanOrEqualTo($future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($customer->id, $collection->firstItem()->id);

        $collection = Braintree_Customer::search(array(
            Braintree_CustomerSearch::id()->is($customer->id),
            Braintree_CustomerSearch::createdAt()->greaterThanOrEqualTo($past)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($customer->id, $collection->firstItem()->id);
    }

    function test_paypalAccountEmail()
    {
        $http = new Braintree_HttpClientApi(Braintree_Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
            )
        ));

        $customerId = 'UNIQUE_CUSTOMER_ID-' . strval(rand());
        $customerResult = Braintree_Customer::create(array(
            'paymentMethodNonce' => $nonce,
            'id' => $customerId
        ));

        $this->assertTrue($customerResult->success);

        $customer = $customerResult->customer;

        $collection = Braintree_Customer::search(array(
            Braintree_CustomerSearch::id()->is($customer->id),
            Braintree_CustomerSearch::paypalAccountEmail()->is('jane.doe@example.com')
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($customer->id, $collection->firstItem()->id);
    }

    function test_throwsIfNoOperatorNodeGiven()
    {
        $this->setExpectedException('InvalidArgumentException', 'Operator must be provided');
        Braintree_Customer::search(array(
            Braintree_CustomerSearch::creditCardExpirationDate()
        ));
    }
}
