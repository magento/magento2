<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_PaymentMethodTest extends PHPUnit_Framework_TestCase
{
    function testCreate_throwsIfInvalidKey()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: invalidKey');
        Braintree_PaymentMethod::create(array('invalidKey' => 'foo'));
    }

    function testCreateSignature()
    {
        $expected = array(
            'billingAddressId',
            'cardholderName',
            'cvv',
            'deviceData',
            'expirationDate',
            'expirationMonth',
            'expirationYear',
            'number',
            'paymentMethodNonce',
            'token',
            array('options' => array(
                'failOnDuplicatePaymentMethod',
                'makeDefault',
                'verificationMerchantAccountId',
                'verifyCard'
            )),
            array('billingAddress' => Braintree_AddressGateway::createSignature()),
            'customerId'
        );
        $this->assertEquals($expected, Braintree_PaymentMethodGateway::createSignature());
    }

    function testErrorsOnFindWithBlankArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('');
    }

    function testErrorsOnFindWithWhitespaceArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('  ');
    }

    function testErrorsOnFindWithWhitespaceCharacterArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_PaymentMethod::find('\t');
    }
}
