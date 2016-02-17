<?php
/**
 * Braintree PaymentMethodNonce module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Creates and manages Braintree PaymentMethodNonces
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 */
class Braintree_PaymentMethodNonce extends Braintree
{
    // static methods redirecting to gateway

    public static function create($token)
    {
        return Braintree_Configuration::gateway()->paymentMethodNonce()->create($token);
    }

    public static function find($nonce)
    {
        return Braintree_Configuration::gateway()->paymentMethodNonce()->find($nonce);
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    protected function _initialize($nonceAttributes)
    {
        $this->_attributes = $nonceAttributes;
        $this->_set('nonce', $nonceAttributes['nonce']);
        $this->_set('type', $nonceAttributes['type']);

        if(isset($nonceAttributes['threeDSecureInfo'])) {
            $this->_set('threeDSecureInfo', Braintree_ThreeDSecureInfo::factory($nonceAttributes['threeDSecureInfo']));
        }
    }
}
