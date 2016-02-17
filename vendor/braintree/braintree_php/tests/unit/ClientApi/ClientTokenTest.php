<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class ClientTokenTest extends PHPUnit_Framework_TestCase
{
    function testErrorsWhenCreditCardOptionsGivenWithoutCustomerId()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: options[makeDefault]');
        Braintree_ClientToken::generate(array("options" => array("makeDefault" => true)));
    }

    function testErrorsWhenInvalidArgumentIsSupplied()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: customrId');
        Braintree_ClientToken::generate(array("customrId" => "1234"));
    }
}
