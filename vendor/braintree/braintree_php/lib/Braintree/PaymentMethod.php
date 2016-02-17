<?php
/**
 * Braintree PaymentMethod module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Creates and manages Braintree PaymentMethods
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 */
class Braintree_PaymentMethod extends Braintree
{
    // static methods redirecting to gateway

    public static function create($attribs)
    {
        return Braintree_Configuration::gateway()->paymentMethod()->create($attribs);
    }

    public static function find($token)
    {
        return Braintree_Configuration::gateway()->paymentMethod()->find($token);
    }

    public static function update($token, $attribs)
    {
        return Braintree_Configuration::gateway()->paymentMethod()->update($token, $attribs);
    }

    public static function delete($token)
    {
        return Braintree_Configuration::gateway()->paymentMethod()->delete($token);
    }
}
