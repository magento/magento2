<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class Braintree_Result_ErrorTest extends PHPUnit_Framework_TestCase
{
    function testValueForHtmlField()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid-email',
            'creditCard' => array(
                'number' => 'invalid-number',
                'expirationDate' => 'invalid-exp',
                'billingAddress' => array(
                    'countryName' => 'invalid-country'
                )
            ),
            'customFields' => array(
                'store_me' => 'some custom value'
            )
        ));
        $this->assertEquals(false, $result->success);
        $this->assertEquals('invalid-email', $result->valueForHtmlField('customer[email]'));
        $this->assertEquals('', $result->valueForHtmlField('customer[credit_card][number]'));
        $this->assertEquals('invalid-exp', $result->valueForHtmlField('customer[credit_card][expiration_date]'));
        $this->assertEquals('invalid-country', $result->valueForHtmlField('customer[credit_card][billing_address][country_name]'));
        $this->assertEquals('some custom value', $result->valueForHtmlField('customer[custom_fields][store_me]'));
    }
}
