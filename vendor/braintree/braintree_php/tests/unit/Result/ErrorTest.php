<?php
require_once realpath(dirname(__FILE__)) . '/../../TestHelper.php';

class Braintree_Result_ErrorTest extends PHPUnit_Framework_TestCase
{
    function testCallingNonExsitingFieldReturnsNull()
    {
        $result = new Braintree_Result_Error(array('errors' => array(), 'params' => array(), 'message' => 'briefly describe'));
        $this->assertNull($result->transaction);
    }
}
