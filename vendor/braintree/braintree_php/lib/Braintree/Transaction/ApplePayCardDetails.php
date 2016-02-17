<?php
/**
 * Apple Pay card details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of ApplePayCardDetails
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $cardType
 * @property-read string $paymentInstrumentName
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $cardholderName
 * @uses Braintree_Instance inherits methods
 */
class Braintree_Transaction_ApplePayCardDetails extends Braintree_Instance
{
    protected $_attributes = array();

    /**
     * @ignore
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }
}
