<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TransparentRedirectTest extends PHPUnit_Framework_TestCase
{
    function testData_specifiesArgSeparatorAsAmpersand()
    {
        $originalSeparator = ini_get("arg_separator.output");
        ini_set("arg_separator.output", "&amp;");
        $trData = Braintree_TransparentRedirect::createCustomerData(array('redirectUrl' => 'http://www.example.com'));
        ini_set("arg_separator.output", $originalSeparator);
        $this->assertFalse(strpos($trData, "&amp;"));
    }

    function testData_doesNotClobberDefaultTimezone()
    {
        $originalZone = date_default_timezone_get();
        date_default_timezone_set('Europe/London');

        $trData = Braintree_TransparentRedirect::createCustomerData(array('redirectUrl' => 'http://www.example.com'));
        $zoneAfterCall = date_default_timezone_get();
        date_default_timezone_set($originalZone);

        $this->assertEquals('Europe/London', $zoneAfterCall);
    }
}
