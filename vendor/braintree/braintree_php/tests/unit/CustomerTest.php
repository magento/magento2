<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_CustomerTest extends PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_Customer: foo');
        $c = Braintree_Customer::factory(array());
        $c->foo;
    }

    function testUpdateSignature_doesNotAlterOptionsInCreditCardUpdateSignature()
    {
        Braintree_CustomerGateway::updateSignature();
        foreach(Braintree_CreditCardGateway::updateSignature() AS $key => $value) {
            if(is_array($value) and array_key_exists('options', $value)) {
                $this->assertEquals(array(
                    'makeDefault',
                    'verificationMerchantAccountId',
                    'verifyCard',
                    'verificationAmount',
                    'venmoSdkSession'
                ), $value['options']);
            }
        }
    }

    function testCreateSignature_doesNotIncludeCustomerIdOnCreditCard()
    {
        $signature = Braintree_CustomerGateway::createSignature();
        $creditCardSignatures = array_filter($signature, 'Braintree_CustomerTest::findCreditCardArray');
        $creditCardSignature = array_shift($creditCardSignatures)['creditCard'];

        $this->assertNotContains('customerId', $creditCardSignature);
    }

    function findCreditCardArray($el)
    {
        return is_array($el) && array_key_exists('creditCard', $el);
    }

    function testFindErrorsOnBlankId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Customer::find('');
    }

    function testFindErrorsOnWhitespaceId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Customer::find('\t');
    }
}
