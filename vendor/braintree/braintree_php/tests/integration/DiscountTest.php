<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_DiscountTest extends PHPUnit_Framework_TestCase
{
    function testAll_returnsAllDiscounts()
    {
        $newId = strval(rand());

        $discountParams = array (
            "amount" => "100.00",
            "description" => "some description",
            "id" => $newId,
            "kind" => "discount",
            "name" => "php_discount",
            "neverExpires" => "false",
            "numberOfBillingCycles" => "1"
        );

        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . "/modifications/create_modification_for_tests";
        $http->post($path, array("modification" => $discountParams));

        $discounts = Braintree_Discount::all();

        foreach ($discounts as $discount)
        {
            if ($discount->id == $newId)
            {
                $actualDiscount = $discount;
            }
        }

        $this->assertNotNull($actualDiscount);
        $this->assertEquals($discountParams["amount"], $actualDiscount->amount);
        $this->assertEquals($discountParams["description"], $actualDiscount->description);
        $this->assertEquals($discountParams["id"], $actualDiscount->id);
        $this->assertEquals($discountParams["kind"], $actualDiscount->kind);
        $this->assertEquals($discountParams["name"], $actualDiscount->name);
        $this->assertFalse($actualDiscount->neverExpires);
        $this->assertEquals($discountParams["numberOfBillingCycles"], $actualDiscount->numberOfBillingCycles);
    }

    function testGatewayAll_returnsAllDiscounts()
    {
        $newId = strval(rand());

        $discountParams = array (
            "amount" => "100.00",
            "description" => "some description",
            "id" => $newId,
            "kind" => "discount",
            "name" => "php_discount",
            "neverExpires" => "false",
            "numberOfBillingCycles" => "1"
        );

        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . "/modifications/create_modification_for_tests";
        $http->post($path, array("modification" => $discountParams));

        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $discounts = $gateway->discount()->all();

        foreach ($discounts as $discount)
        {
            if ($discount->id == $newId)
            {
                $actualDiscount = $discount;
            }
        }

        $this->assertNotNull($actualDiscount);
        $this->assertEquals($discountParams["amount"], $actualDiscount->amount);
        $this->assertEquals($discountParams["id"], $actualDiscount->id);
        $this->assertEquals($discountParams["kind"], $actualDiscount->kind);
    }
}
