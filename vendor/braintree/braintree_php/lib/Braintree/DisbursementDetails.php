<?php
/**
 * Disbursement details from a transaction
 * Creates an instance of DisbursementDetails as returned from a transaction
 *
 *
 * @package    Braintree
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $settlementAmount
 * @property-read string $settlementCurrencyIsoCode
 * @property-read string $settlementCurrencyExchangeRate
 * @property-read string $fundsHeld
 * @property-read string $success
 * @property-read string $disbursementDate
 * @uses Braintree_Instance inherits methods
 */
class Braintree_DisbursementDetails extends Braintree_Instance
{
    protected $_attributes = array();

    function isValid() {
        return !is_null($this->disbursementDate);
    }
}
