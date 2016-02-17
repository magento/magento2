<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_InstanceTest extends PHPUnit_Framework_TestCase
{
    function test__isset()
    {
      $transaction = Braintree_Transaction::factory(array(
        'creditCard' => array(
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100',
          'cardType' => 'MasterCard'
        ),
      ));
      $this->assertEquals('MasterCard', $transaction->creditCardDetails->cardType);
      $this->assertFalse(empty($transaction->creditCardDetails->cardType));
      $this->assertTrue(isset($transaction->creditCardDetails->cardType));

      $transaction = Braintree_Transaction::factory(array(
        'creditCard' => array(
          'expirationMonth' => '05',
          'expirationYear' => '2010',
          'bin' => '510510',
          'last4' => '5100'
        ),
      ));
      $this->assertTrue(empty($transaction->creditCardDetails->cardType));
      $this->assertFalse(isset($transaction->creditCardDetails->cardType));
    }
}
