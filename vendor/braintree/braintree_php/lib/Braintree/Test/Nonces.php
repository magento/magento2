<?php
/**
 * Nonces used for testing purposes
 *
 * @package    Braintree
 * @subpackage Test
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Nonces used for testing purposes
 *
 * The constants in this class can be used to perform nonce operations
 * with the desired status in the sandbox environment.
 *
 * @package    Braintree
 * @subpackage Test
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Test_Nonces
{
   public static $transactable = "fake-valid-nonce";
   public static $consumed = "fake-consumed-nonce";
   public static $paypalOneTimePayment = "fake-paypal-one-time-nonce";
   public static $paypalFuturePayment = "fake-paypal-future-nonce";
   public static $applePayVisa = "fake-apple-pay-visa-nonce";
   public static $applePayMasterCard = "fake-apple-pay-visa-nonce";
   public static $applePayAmEx = "fake-apple-pay-amex-nonce";
   public static $abstractTransactable = "fake-abstract-transactable-nonce";
   public static $europe = "fake-europe-bank-account-nonce";
   public static $coinbase = "fake-coinbase-nonce";
}
