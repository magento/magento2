<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class Braintree_Error_ErrorCollectionTest extends PHPUnit_Framework_TestCase
{
    function testDeepSize_withNestedErrors()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => 'invalid',
                'expirationDate' => 'invalid',
                'billingAddress' => array(
                    'countryName' => 'invaild'
                )
            )
        ));
        $this->assertEquals(false, $result->success);
        $this->assertEquals(4, $result->errors->deepSize());
    }

    function testOnHtmlField()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => 'invalid',
                'expirationDate' => 'invalid',
                'billingAddress' => array(
                    'countryName' => 'invaild'
                )
            )
        ));
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->onHtmlField('customer[email]');
        $this->assertEquals(Braintree_Error_Codes::CUSTOMER_EMAIL_IS_INVALID, $errors[0]->code);
        $errors = $result->errors->onHtmlField('customer[credit_card][number]');
        $this->assertEquals(Braintree_Error_Codes::CREDIT_CARD_NUMBER_INVALID_LENGTH, $errors[0]->code);
        $errors = $result->errors->onHtmlField('customer[credit_card][billing_address][country_name]');
        $this->assertEquals(Braintree_Error_Codes::ADDRESS_COUNTRY_NAME_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function testOnHtmlField_returnsEmptyArrayIfNone()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
                'billingAddress' => array(
                    'streetAddress' => '1 E Main St'
                )
            )
        ));
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->onHtmlField('customer[email]');
        $this->assertEquals(Braintree_Error_Codes::CUSTOMER_EMAIL_IS_INVALID, $errors[0]->code);
        $this->assertEquals(array(), $result->errors->onHtmlField('customer[credit_card][number]'));
        $this->assertEquals(array(), $result->errors->onHtmlField('customer[credit_card][billing_address][country_name]'));
    }

    function testOnHtmlField_returnsEmptyForCustomFieldsIfNoErrors()
    {
        $result = Braintree_Customer::create(array(
            'email' => 'invalid',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customFields' => array('storeMe' => 'value')
        ));
        $this->assertEquals(false, $result->success);
        $this->assertEquals(array(), $result->errors->onHtmlField('customer[custom_fields][store_me]'));
    }
}
