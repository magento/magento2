<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_UnknownPaymentMethodTest extends PHPUnit_Framework_TestCase
{
    function testHandlesUnknownPaymentMethodResponses()
    {
        $response = array(
            'unkownPaymentMethod' => array(
                'token' => 'SOME_TOKEN',
                'default' => true
            )
        );
        $unknownPaymentMethodObject = Braintree_UnknownPaymentMethod::factory($response);
        $this->assertEquals('SOME_TOKEN', $unknownPaymentMethodObject->token);
        $this->assertTrue($unknownPaymentMethodObject->isDefault());
        $this->assertEquals('https://assets.braintreegateway.com/payment_method_logo/unknown.png', $unknownPaymentMethodObject->imageUrl);
    }
}

