<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_DiscountTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $discount = \Braintree_Discount::factory(array());

        $this->assertInstanceOf('Braintree_Discount', $discount);
    }
}
