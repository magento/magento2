<?php
/**
 * Raised when a suspected forged query string is present
 * Raised from methods that confirm transparent redirect requests
 * when the given query string cannot be verified. This may indicate
 * an attempted hack on the merchant's transparent redirect
 * confirmation URL.
 *
 * @package    Braintree
 * @subpackage Exception
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Exception_ForgedQueryString extends Braintree_Exception
{

}
