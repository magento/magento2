<?php
/**
 * Creates an instance of AddressDetails as returned from a transaction
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $firstName
 * @property-read string $lastName
 * @property-read string $company
 * @property-read string $streetAddress
 * @property-read string $extendedAddress
 * @property-read string $locality
 * @property-read string $region
 * @property-read string $postalCode
 * @property-read string $countryName
 * @uses Braintree_Instance inherits methods
 */
class Braintree_Transaction_AddressDetails extends Braintree_Instance
{
    protected $_attributes = array();
}
