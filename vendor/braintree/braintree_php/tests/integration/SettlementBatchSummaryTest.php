<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_SettlementBatchSummaryTest extends PHPUnit_Framework_TestCase
{
    function isMasterCard($record)
    {
        return $record['cardType'] == Braintree_CreditCard::MASTER_CARD;
    }

    function testGenerate_returnsAnEmptyCollectionWhenThereIsNoData()
    {
        $result = Braintree_SettlementBatchSummary::generate('2000-01-01');

        $this->assertTrue($result->success);
        $this->assertEquals(0, count($result->settlementBatchSummary->records));
    }

    function testGatewayGenerate_returnsAnEmptyCollectionWhenThereIsNoData()
    {
        $gateway = new Braintree_Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ));
        $result = $gateway->settlementBatchSummary()->generate('2000-01-01');

        $this->assertTrue($result->success);
        $this->assertEquals(0, count($result->settlementBatchSummary->records));
    }

    function testGenerate_returnsAnErrorIfTheDateCanNotBeParsed()
    {
        $result = Braintree_SettlementBatchSummary::generate('OMG NOT A DATE');

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('settlementBatchSummary')->onAttribute('settlementDate');
        $this->assertEquals(Braintree_Error_Codes::SETTLEMENT_BATCH_SUMMARY_SETTLEMENT_DATE_IS_INVALID, $errors[0]->code);
    }

    function testGenerate_returnsTransactionsSettledOnAGivenDay()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options' => array('submitForSettlement' => true)
        ));
        Braintree_TestHelper::settle($transaction->id);

        $today = new Datetime;
        $result = Braintree_SettlementBatchSummary::generate(Braintree_TestHelper::nowInEastern());

        $this->assertTrue($result->success);
        $masterCardRecords = array_filter($result->settlementBatchSummary->records, 'self::isMasterCard');
        $masterCardKeys = array_keys($masterCardRecords);
        $masterCardIndex = $masterCardKeys[0];
        $this->assertTrue(count($masterCardRecords) > 0);
        $this->assertEquals(Braintree_CreditCard::MASTER_CARD, $masterCardRecords[$masterCardIndex]['cardType']);
    }

    function testGenerate_canBeGroupedByACustomField()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '100.00',
            'creditCard' => array(
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'store_me' => 'custom value'
            ),
            'options' => array('submitForSettlement' => true)
        ));

        Braintree_TestHelper::settle($transaction->id);

        $today = new Datetime;
        $result = Braintree_SettlementBatchSummary::generate(Braintree_TestHelper::nowInEastern(), 'store_me');

        $this->assertTrue($result->success);
        $this->assertTrue(count($result->settlementBatchSummary->records) > 0);
        $this->assertArrayHasKey('store_me', $result->settlementBatchSummary->records[0]);
    }
}
