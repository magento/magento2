<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_MerchantAccountTest extends PHPUnit_Framework_TestCase
{
    function testCreateMerchantAccountWithAllParams()
    {
        $params = array(
            "id" => "sub_merchant_account",
            "status" => "active",
            "masterMerchantAccount" => array(
                "id" => "master_merchant_account",
                "status" => "active"
            ),
            "individual" => array(
                "firstName" => "John",
                "lastName" => "Doe",
                "email" => "john.doe@example.com",
                "dateOfBirth" => "1970-01-01",
                "phone" => "3125551234",
                "ssnLast4" => "6789",
                "address" => array(
                    "streetAddress" => "123 Fake St",
                    "locality" => "Chicago",
                    "region" => "IL",
                    "postalCode" => "60622",
                )
            ),
            "business" => array(
                "dbaName" => "James's Bloggs",
                "taxId" => "123456789",
            ),
            "funding" => array(
                "accountNumberLast4" => "8798",
                "routingNumber" => "071000013",
                "descriptor" => "Joes Bloggs MI",
            )
        );
        $merchantAccount = Braintree_MerchantAccount::factory($params);


        $this->assertEquals($merchantAccount->status, "active");
        $this->assertEquals($merchantAccount->id, "sub_merchant_account");
        $this->assertEquals($merchantAccount->masterMerchantAccount->id, "master_merchant_account");
        $this->assertEquals($merchantAccount->masterMerchantAccount->status, "active");
        $this->assertEquals($merchantAccount->individualDetails->firstName, "John");
        $this->assertEquals($merchantAccount->individualDetails->lastName, "Doe");
        $this->assertEquals($merchantAccount->individualDetails->email, "john.doe@example.com");
        $this->assertEquals($merchantAccount->individualDetails->dateOfBirth, "1970-01-01");
        $this->assertEquals($merchantAccount->individualDetails->phone, "3125551234");
        $this->assertEquals($merchantAccount->individualDetails->ssnLast4, "6789");
        $this->assertEquals($merchantAccount->individualDetails->addressDetails->streetAddress, "123 Fake St");
        $this->assertEquals($merchantAccount->individualDetails->addressDetails->locality, "Chicago");
        $this->assertEquals($merchantAccount->individualDetails->addressDetails->region, "IL");
        $this->assertEquals($merchantAccount->individualDetails->addressDetails->postalCode, "60622");
        $this->assertEquals($merchantAccount->businessDetails->dbaName, "James's Bloggs");
        $this->assertEquals($merchantAccount->businessDetails->taxId, "123456789");
        $this->assertEquals($merchantAccount->fundingDetails->accountNumberLast4, "8798");
        $this->assertEquals($merchantAccount->fundingDetails->routingNumber, "071000013");
        $this->assertEquals($merchantAccount->fundingDetails->descriptor, "Joes Bloggs MI");
    }
}
?>
