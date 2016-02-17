<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_DisbursementTest extends PHPUnit_Framework_TestCase
{
    function testToString()
    {
        $disbursement = Braintree_Disbursement::factory(array(
            "id" => "123456",
            "merchantAccount" => array(
                "id" => "sandbox_sub_merchant_account",
                "masterMerchantAccount" => array(
                    "id" => "sandbox_master_merchant_account",
                    "status" => "active"
                    ),
                "status" => "active"
                ),
            "transactionIds" => array("sub_merchant_transaction"),
            "exceptionMessage" => "invalid_account_number",
            "amount" => "100.00",
            "disbursementDate" => new DateTime("2013-04-10"),
            "followUpAction" => "update",
            "retry" => false,
            "success" => false
        ));

       $this->assertEquals((string) $disbursement, 'Braintree_Disbursement[id=123456, merchantAccountDetails=id=sandbox_sub_merchant_account, masterMerchantAccount=id=sandbox_master_merchant_account, status=active, status=active, exceptionMessage=invalid_account_number, amount=100.00, disbursementDate=Wednesday, 10-Apr-13 00:00:00 UTC, followUpAction=update, retry=, success=, transactionIds=0=sub_merchant_transaction]');
    }
}
