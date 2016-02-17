<?php
/**
 * Braintree ApplePayCard module
 * Creates and manages Braintree Apple Pay cards
 *
 * <b>== More information ==</b>
 *
 * See {@link https://developers.braintreepayments.com/javascript+php}<br />
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $cardType
 * @property-read string $createdAt
 * @property-read string $expirationDate
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $imageUrl
 * @property-read string $last4
 * @property-read string $token
 * @property-read string $paymentInstrumentName
 * @property-read string $updatedAt
 */
class Braintree_ApplePayCard extends Braintree
{
    // Card Type
    const AMEX = 'Apple Pay - American Express';
    const MASTER_CARD = 'Apple Pay - MasterCard';
    const VISA = 'Apple Pay - Visa';

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
     * checks whether the card is expired based on the current date
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expired;
    }

    /**
     *  factory method: returns an instance of Braintree_ApplePayCard
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Braintree_ApplePayCard
     */
    public static function factory($attributes)
    {
        $defaultAttributes = array(
            'expirationMonth'    => '',
            'expirationYear'    => '',
            'last4'  => '',
        );

        $instance = new self();
        $instance->_initialize(array_merge($defaultAttributes, $attributes));
        return $instance;
    }

    /**
     * sets instance properties from an array of values
     *
     * @access protected
     * @param array $applePayCardAttribs array of Apple Pay card properties
     * @return none
     */
    protected function _initialize($applePayCardAttribs)
    {
        // set the attributes
        $this->_attributes = $applePayCardAttribs;

        $subscriptionArray = array();
        if (isset($applePayCardAttribs['subscriptions'])) {
            foreach ($applePayCardAttribs['subscriptions'] AS $subscription) {
                $subscriptionArray[] = Braintree_Subscription::factory($subscription);
            }
        }

        $this->_set('subscriptions', $subscriptionArray);
        $this->_set('expirationDate', $this->expirationMonth . '/' . $this->expirationYear);
    }
}
