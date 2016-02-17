<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class Braintree_Result_SuccessfulTest extends PHPUnit_Framework_TestCase
{
     /**
     * @expectedException        PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage Undefined property on Braintree_Result_Successful: notAProperty
     */
    function testCallingNonExsitingFieldReturnsNull()
    {
        $result = new Braintree_Result_Successful(1, "transaction");
        $this->assertNotNull($result->transaction);
        $this->assertNull($result->notAProperty);
    }
}
