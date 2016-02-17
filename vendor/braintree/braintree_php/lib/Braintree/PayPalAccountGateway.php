<?php
/**
 * Braintree PayPalAccountGateway module
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
 */
class Braintree_PayPalAccountGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Braintree_Http($gateway->config);
    }


    /**
     * find a paypalAccount by token
     *
     * @access public
     * @param string $token paypal accountunique id
     * @return object Braintree_PayPalAccount
     * @throws Braintree_Exception_NotFound
     */
    public function find($token)
    {
        $this->_validateId($token);
        try {
            $path = $this->_config->merchantPath() . '/payment_methods/paypal_account/' . $token;
            $response = $this->_http->get($path);
            return Braintree_PayPalAccount::factory($response['paypalAccount']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
                'paypal account with token ' . $token . ' not found'
            );
        }

    }

    /**
     * updates the paypalAccount record
     *
     * if calling this method in context, $token
     * is the 2nd attribute. $token is not sent in object context.
     *
     * @access public
     * @param array $attributes
     * @param string $token (optional)
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     */
    public function update($token, $attributes)
    {
        Braintree_Util::verifyKeys(self::updateSignature(), $attributes);
        $this->_validateId($token);
        return $this->_doUpdate('put', '/payment_methods/paypal_account/' . $token, array('paypalAccount' => $attributes));
    }

    public function delete($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath() . '/payment_methods/paypal_account/' . $token;
        $this->_http->delete($path);
        return new Braintree_Result_Successful();
    }

    /**
     * create a new sale for the current PayPal account
     *
     * @param string $token
     * @param array $transactionAttribs
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     * @see Braintree_Transaction::sale()
     */
    public function sale($token, $transactionAttribs)
    {
        $this->_validateId($token);
        return Braintree_Transaction::sale(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    public static function updateSignature()
    {
        return array(
            'token',
            array('options' => array('makeDefault'))
        );
    }

    /**
     * sends the update request to the gateway
     *
     * @ignore
     * @param string $subPath
     * @param array $params
     * @return mixed
     */
    private function _doUpdate($httpVerb, $subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->$httpVerb($fullPath, $params);
        return $this->_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Braintree_PayPalAccount object and encapsulates
     * it inside a Braintree_Result_Successful object, or
     * encapsulates a Braintree_Errors object inside a Result_Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     * @param array $response gateway response values
     * @return object Result_Successful or Result_Error
     * @throws Braintree_Exception_Unexpected
     */
    private function _verifyGatewayResponse($response)
    {
        if (isset($response['paypalAccount'])) {
            // return a populated instance of Braintree_PayPalAccount
            return new Braintree_Result_Successful(
                    Braintree_PayPalAccount::factory($response['paypalAccount'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
            'Expected paypal account or apiErrorResponse'
            );
        }
    }

    /**
     * verifies that a valid paypal account identifier is being used
     * @ignore
     * @param string $identifier
     * @param Optional $string $identifierType type of identifier supplied, default 'token'
     * @throws InvalidArgumentException
     */
    private function _validateId($identifier = null, $identifierType = 'token')
    {
        if (empty($identifier)) {
           throw new InvalidArgumentException(
                   'expected paypal account id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $identifier)) {
            throw new InvalidArgumentException(
                    $identifier . ' is an invalid paypal account ' . $identifierType . '.'
                    );
        }
    }
}
