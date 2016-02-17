<?php
/**
 * Coinbase details from a transaction
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of Coinbase
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $token
 * @property-read string $userId
 * @property-read string $userName
 * @property-read string $userEmail
 * @property-read string $imageUrl
 * @uses Braintree_Instance inherits methods
 */
class Braintree_Transaction_CoinbaseDetails extends Braintree_Instance
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
