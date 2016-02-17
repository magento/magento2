<?php
/**
 * Braintree PayPalAccount module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Manages Braintree PayPalAccounts
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $email
 * @property-read string $token
 * @property-read string $imageUrl
 */
class Braintree_PayPalAccount extends Braintree
{
    /**
     *  factory method: returns an instance of Braintree_PayPalAccount
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Braintree_PayPalAccount
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /* instance methods */

    /**
     * returns false if default is null or false
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $paypalAccountAttribs array of paypalAccount data
     * @return none
     */
    protected function _initialize($paypalAccountAttribs)
    {
        // set the attributes
        $this->_attributes = $paypalAccountAttribs;

        $subscriptionArray = array();
        if (isset($paypalAccountAttribs['subscriptions'])) {
            foreach ($paypalAccountAttribs['subscriptions'] AS $subscription) {
                $subscriptionArray[] = Braintree_Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
    }

    /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($this->_attributes) .']';
    }


    // static methods redirecting to gateway

    public static function find($token)
    {
        return Braintree_Configuration::gateway()->payPalAccount()->find($token);
    }

    public static function update($token, $attributes)
    {
        return Braintree_Configuration::gateway()->payPalAccount()->update($token, $attributes);
    }

    public static function delete($token)
    {
        return Braintree_Configuration::gateway()->payPalAccount()->delete($token);
    }

    public static function sale($token, $transactionAttribs)
    {
        return Braintree_Configuration::gateway()->payPalAccount()->sale($token, $transactionAttribs);
    }
}
