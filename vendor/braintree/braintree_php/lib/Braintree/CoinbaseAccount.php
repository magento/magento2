<?php
/**
 * Braintree CoinbaseAccount module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Manages Braintree CoinbaseAccounts
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $token
 * @property-read string $userId
 * @property-read string $userName
 * @property-read string $userEmail
 */
class Braintree_CoinbaseAccount extends Braintree
{
    /**
     *  factory method: returns an instance of Braintree_CoinbaseAccount
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Braintree_CoinbaseAccount
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
     * @param array $coinbaseAccountAttribs array of coinbaseAccount data
     * @return none
     */
    protected function _initialize($coinbaseAccountAttribs)
    {
        // set the attributes
        $this->_attributes = $coinbaseAccountAttribs;

        $subscriptionArray = array();
        if (isset($coinbaseAccountAttribs['subscriptions'])) {
            foreach ($coinbaseAccountAttribs['subscriptions'] AS $subscription) {
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
        return Braintree_Configuration::gateway()->coinbaseAccount()->find($token);
    }

    public static function update($token, $attributes)
    {
        return Braintree_Configuration::gateway()->coinbaseAccount()->update($token, $attributes);
    }

    public static function delete($token)
    {
        return Braintree_Configuration::gateway()->coinbaseAccount()->delete($token);
    }

    public static function sale($token, $transactionAttribs)
    {
        return Braintree_Configuration::gateway()->coinbaseAccount()->sale($token, $transactionAttribs);
    }
}
