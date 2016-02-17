<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_AddOnTest extends PHPUnit_Framework_TestCase
{
    function testAll_returnsAllAddOns()
    {
        $newId = strval(rand());

        $addOnParams = array (
            "amount" => "100.00",
            "description" => "some description",
            "id" => $newId,
            "kind" => "add_on",
            "name" => "php_add_on",
            "neverExpires" => "false",
            "numberOfBillingCycles" => "1"
        );

        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . "/modifications/create_modification_for_tests";
        $http->post($path, array("modification" => $addOnParams));

        $addOns = Braintree_AddOn::all();

        foreach ($addOns as $addOn)
        {
            if ($addOn->id == $newId)
            {
                $actualAddOn = $addOn;
            }
        }

        $this->assertNotNull($actualAddOn);
        $this->assertEquals($addOnParams["amount"], $actualAddOn->amount);
        $this->assertEquals($addOnParams["description"], $actualAddOn->description);
        $this->assertEquals($addOnParams["id"], $actualAddOn->id);
        $this->assertEquals($addOnParams["kind"], $actualAddOn->kind);
        $this->assertEquals($addOnParams["name"], $actualAddOn->name);
        $this->assertFalse($actualAddOn->neverExpires);
        $this->assertEquals($addOnParams["numberOfBillingCycles"], $actualAddOn->numberOfBillingCycles);
    }

    function testGatewayAll_returnsAllAddOns()
    {
        $newId = strval(rand());

        $addOnParams = array (
            "amount" => "100.00",
            "description" => "some description",
            "id" => $newId,
            "kind" => "add_on",
            "name" => "php_add_on",
            "neverExpires" => "false",
            "numberOfBillingCycles" => "1"
        );

        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . "/modifications/create_modification_for_tests";
        $http->post($path, array("modification" => $addOnParams));

        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $addOns = $gateway->addOn()->all();

        foreach ($addOns as $addOn)
        {
            if ($addOn->id == $newId)
            {
                $actualAddOn = $addOn;
            }
        }

        $this->assertNotNull($actualAddOn);
        $this->assertEquals($addOnParams["amount"], $actualAddOn->amount);
        $this->assertEquals($addOnParams["description"], $actualAddOn->description);
        $this->assertEquals($addOnParams["id"], $actualAddOn->id);
    }
}
