<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_AddressTest extends PHPUnit_Framework_TestCase
{
    function testGet_givesErrorIfInvalidProperty()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_Address: foo');
        $a = Braintree_Address::factory(array());
        $a->foo;
    }

    function testIsEqual()
    {
        $first = Braintree_Address::factory(
                array('customerId' => 'c1', 'id' => 'a1')
                );
        $second = Braintree_Address::factory(
                array('customerId' => 'c1', 'id' => 'a1')
                );

        $this->assertTrue($first->isEqual($second));
        $this->assertTrue($second->isEqual($first));

    }
    function testIsNotEqual() {
        $first = Braintree_Address::factory(
                array('customerId' => 'c1', 'id' => 'a1')
                );
        $second = Braintree_Address::factory(
                array('customerId' => 'c1', 'id' => 'not a1')
                );

        $this->assertFalse($first->isEqual($second));
        $this->assertFalse($second->isEqual($first));
    }

    function testCustomerIdNotEqual()
    {
        $first = Braintree_Address::factory(
                array('customerId' => 'c1', 'id' => 'a1')
                );
        $second = Braintree_Address::factory(
                array('customerId' => 'not c1', 'id' => 'a1')
                );

        $this->assertFalse($first->isEqual($second));
        $this->assertFalse($second->isEqual($first));
    }

    function testFindErrorsOnBlankCustomerId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Address::find('', '123');
    }

    function testFindErrorsOnBlankAddressId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Address::find('123', '');
    }

    function testFindErrorsOnWhitespaceOnlyId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Address::find('123', '  ');
    }

    function testFindErrorsOnWhitespaceOnlyCustomerId()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Address::find('  ', '123');
    }
}
