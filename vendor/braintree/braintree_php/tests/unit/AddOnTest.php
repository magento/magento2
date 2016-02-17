<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_AddOnTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $addOn = \Braintree_AddOn::factory(array());

        $this->assertInstanceOf('Braintree_AddOn', $addOn);
    }
}
