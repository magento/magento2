<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_CreditCardVerificationAdvancedSearchTest extends PHPUnit_Framework_TestCase
{
    function test_searchOnTextFields()
    {
        $search_criteria = array(
            'creditCardCardholderName' => 'Tim Toole',
            'creditCardExpirationDate' => '05/2010',
            'creditCardNumber' => '4000111111111115',
        );

        $result = Braintree_Customer::create(array(
            'creditCard' => array(
                'cardholderName' => $search_criteria['creditCardCardholderName'],
                'number' => $search_criteria['creditCardNumber'],
                'expirationDate' => $search_criteria['creditCardExpirationDate'],
                'options' => array('verifyCard' => true),
            ),
        ));

        $verification = $result->creditCardVerification;
        $query = array(Braintree_CreditCardVerificationSearch::id()->is($verification->id));
        foreach ($search_criteria AS $criterion => $value) {
            $query[] = Braintree_CreditCardVerificationSearch::$criterion()->is($value);
        }

        $collection = Braintree_CreditCardVerification::search($query);

        $this->assertEquals(1, $collection->maximumCount());

        $this->assertEquals($result->creditCardVerification->id, $collection->firstItem()->id);

        foreach ($search_criteria AS $criterion => $value) {
            $collection = Braintree_CreditCardVerification::search(array(
                Braintree_CreditCardVerificationSearch::$criterion()->is($value),
                Braintree_CreditCardVerificationSearch::id()->is($result->creditCardVerification->id)
            ));
            $this->assertEquals(1, $collection->maximumCount());
            $this->assertEquals($result->creditCardVerification->id, $collection->firstItem()->id);

            $collection = Braintree_CreditCardVerification::search(array(
                Braintree_CreditCardVerificationSearch::$criterion()->is('invalid_attribute'),
                Braintree_CreditCardVerificationSearch::id()->is($result->creditCardVerification->id)
            ));
            $this->assertEquals(0, $collection->maximumCount());
        }
    }

    function testGateway_searchEmpty()
    {
        $query = array();
        $query[] = Braintree_CreditCardVerificationSearch::creditCardCardholderName()->is('Not Found');

        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $collection = $gateway->creditCardVerification()->search($query);

        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_createdAt()
    {
        $result = Braintree_Customer::create(array(
            'creditCard' => array(
                'cardholderName' => "Joe Smith",
                'number' => "4000111111111115",
                'expirationDate' => "12/2016",
                'options' => array('verifyCard' => true),
            ),
        ));

        $verification = $result->creditCardVerification;

        $past = clone $verification->createdAt;
        $past->modify("-1 hour");
        $future = clone $verification->createdAt;
        $future->modify("+1 hour");

        $collection = Braintree_CreditCardVerification::search(array(
            Braintree_CreditCardVerificationSearch::id()->is($verification->id),
            Braintree_CreditCardVerificationSearch::createdAt()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);

        $collection = Braintree_CreditCardVerification::search(array(
            Braintree_CreditCardVerificationSearch::id()->is($verification->id),
            Braintree_CreditCardVerificationSearch::createdAt()->lessThanOrEqualTo($future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);

        $collection = Braintree_CreditCardVerification::search(array(
            Braintree_CreditCardVerificationSearch::id()->is($verification->id),
            Braintree_CreditCardVerificationSearch::createdAt()->greaterThanOrEqualTo($past)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);
    }

    function test_multipleValueNode_creditCardType()
    {
        $result = Braintree_Customer::create(array(
            'creditCard' => array(
                'cardholderName' => "Joe Smith",
                'number' => "4000111111111115",
                'expirationDate' => "12/2016",
                'options' => array('verifyCard' => true),
            ),
        ));

        $creditCardVerification = $result->creditCardVerification;

        $collection = Braintree_CreditCardVerification::search(array(
            Braintree_CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree_CreditCardVerificationSearch::creditCardCardType()->is($creditCardVerification->creditCard['cardType'])
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree_CreditCardVerification::search(array(
            Braintree_CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree_CreditCardVerificationSearch::creditCardCardType()->in(
                array($creditCardVerification->creditCard['cardType'], Braintree_CreditCard::CHINA_UNION_PAY)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree_CreditCardVerification::search(array(
            Braintree_CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree_CreditCardVerificationSearch::creditCardCardType()->is(Braintree_CreditCard::CHINA_UNION_PAY)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }
}
