<?php
/**
 * Braintree CreditCardGateway module
 * Creates and manages Braintree CreditCards
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on CreditCards, see {@link http://www.braintreepayments.com/gateway/credit-card-api http://www.braintreepaymentsolutions.com/gateway/credit-card-api}<br />
 * For more detailed information on CreditCard verifications, see {@link http://www.braintreepayments.com/gateway/credit-card-verification-api http://www.braintreepaymentsolutions.com/gateway/credit-card-verification-api}
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_CreditCardGateway
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

    public function create($attribs)
    {
        Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        return $this->_doCreate('/payment_methods', array('credit_card' => $attribs));
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a Braintree_CreditCard object instead of a Result
     *
     * @access public
     * @param array $attribs
     * @return object
     * @throws Braintree_Exception_ValidationError
     */
    public function createNoValidate($attribs)
    {
        $result = $this->create($attribs);
        return Braintree_Util::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     * create a customer from a TransparentRedirect operation
     *
     * @access public
     * @param array $attribs
     * @return object
     */
    public function createFromTransparentRedirect($queryString)
    {
        trigger_error("DEPRECATED: Please use Braintree_TransparentRedirectRequest::confirm", E_USER_NOTICE);
        $params = Braintree_TransparentRedirect::parseAndValidateQueryString(
            $queryString
        );
        return $this->_doCreate(
            '/payment_methods/all/confirm_transparent_redirect_request',
            array('id' => $params['id'])
        );
    }

    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public function createCreditCardUrl()
    {
        trigger_error("DEPRECATED: Please use Braintree_TransparentRedirectRequest::url", E_USER_NOTICE);
        return $this->_config->baseUrl() . $this->_config->merchantPath() .
                '/payment_methods/all/create_via_transparent_redirect_request';
    }

    /**
     * returns a ResourceCollection of expired credit cards
     * @return object ResourceCollection
     */
    public function expired()
    {
        $path = $this->_config->merchantPath() . '/payment_methods/all/expired_ids';
        $response = $this->_http->post($path);
        $pager = array(
            'object' => $this,
            'method' => 'fetchExpired',
            'methodArgs' => array()
        );

        return new Braintree_ResourceCollection($response, $pager);
    }

    public function fetchExpired($ids)
    {
        $path = $this->_config->merchantPath() . "/payment_methods/all/expired";
        $response = $this->_http->post($path, array('search' => array('ids' => $ids)));

        return Braintree_Util::extractattributeasarray(
            $response['paymentMethods'],
            'creditCard'
        );
    }
    /**
     * returns a ResourceCollection of credit cards expiring between start/end
     *
     * @return object ResourceCollection
     */
    public function expiringBetween($startDate, $endDate)
    {
        $queryPath = $this->_config->merchantPath() . '/payment_methods/all/expiring_ids?start=' . date('mY', $startDate) . '&end=' . date('mY', $endDate);
        $response = $this->_http->post($queryPath);
        $pager = array(
            'object' => $this,
            'method' => 'fetchExpiring',
            'methodArgs' => array($startDate, $endDate)
        );

        return new Braintree_ResourceCollection($response, $pager);
    }

    public function fetchExpiring($startDate, $endDate, $ids)
    {
        $queryPath = $this->_config->merchantPath() . '/payment_methods/all/expiring?start=' . date('mY', $startDate) . '&end=' . date('mY', $endDate);
        $response = $this->_http->post($queryPath, array('search' => array('ids' => $ids)));

        return Braintree_Util::extractAttributeAsArray(
            $response['paymentMethods'],
            'creditCard'
        );
    }

    /**
     * find a creditcard by token
     *
     * @access public
     * @param string $token credit card unique id
     * @return object Braintree_CreditCard
     * @throws Braintree_Exception_NotFound
     */
    public function find($token)
    {
        $this->_validateId($token);
        try {
            $path = $this->_config->merchantPath() . '/payment_methods/credit_card/' . $token;
            $response = $this->_http->get($path);
            return Braintree_CreditCard::factory($response['creditCard']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
                'credit card with token ' . $token . ' not found'
            );
        }

    }

    /**
     * Convert a payment method nonce to a credit card
     *
     * @access public
     * @param string $nonce payment method nonce
     * @return object Braintree_CreditCard
     * @throws Braintree_Exception_NotFound
     */
    public function fromNonce($nonce)
    {
        $this->_validateId($nonce, "nonce");
        try {
            $path = $this->_config->merchantPath() . '/payment_methods/from_nonce/' . $nonce;
            $response = $this->_http->get($path);
            return Braintree_CreditCard::factory($response['creditCard']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
                'credit card with nonce ' . $nonce . ' locked, consumed or not found'
            );
        }

    }

   /**
     * create a credit on the card for the passed transaction
     *
     * @access public
     * @param array $attribs
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     */
    public function credit($token, $transactionAttribs)
    {
        $this->_validateId($token);
        return Braintree_Transaction::credit(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    /**
     * create a credit on this card, assuming validations will pass
     *
     * returns a Braintree_Transaction object on success
     *
     * @access public
     * @param array $attribs
     * @return object Braintree_Transaction
     * @throws Braintree_Exception_ValidationError
     */
    public function creditNoValidate($token, $transactionAttribs)
    {
        $result = $this->credit($token, $transactionAttribs);
        return Braintree_Util::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * create a new sale for the current card
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

    /**
     * create a new sale using this card, assuming validations will pass
     *
     * returns a Braintree_Transaction object on success
     *
     * @access public
     * @param array $transactionAttribs
     * @param string $token
     * @return object Braintree_Transaction
     * @throws Braintree_Exception_ValidationsFailed
     * @see Braintree_Transaction::sale()
     */
    public function saleNoValidate($token, $transactionAttribs)
    {
        $result = $this->sale($token, $transactionAttribs);
        return Braintree_Util::returnObjectOrThrowException('Transaction', $result);
    }

    /**
     * updates the creditcard record
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
        return $this->_doUpdate('put', '/payment_methods/credit_card/' . $token, array('creditCard' => $attributes));
    }

    /**
     * update a creditcard record, assuming validations will pass
     *
     * if calling this method in context, $token
     * is the 2nd attribute. $token is not sent in object context.
     * returns a Braintree_CreditCard object on success
     *
     * @access public
     * @param array $attributes
     * @param string $token
     * @return object Braintree_CreditCard
     * @throws Braintree_Exception_ValidationsFailed
     */
    public function updateNoValidate($token, $attributes)
    {
        $result = $this->update($token, $attributes);
        return Braintree_Util::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public function updateCreditCardUrl()
    {
        trigger_error("DEPRECATED: Please use Braintree_TransparentRedirectRequest::url", E_USER_NOTICE);
        return $this->_config->baseUrl() . $this->_config->merchantPath() .
                '/payment_methods/all/update_via_transparent_redirect_request';
    }

    /**
     * update a customer from a TransparentRedirect operation
     *
     * @access public
     * @param array $attribs
     * @return object
     */
    public function updateFromTransparentRedirect($queryString)
    {
        trigger_error("DEPRECATED: Please use Braintree_TransparentRedirectRequest::confirm", E_USER_NOTICE);
        $params = Braintree_TransparentRedirect::parseAndValidateQueryString(
            $queryString
        );
        return $this->_doUpdate(
            'post',
            '/payment_methods/all/confirm_transparent_redirect_request',
            array('id' => $params['id'])
        );
    }

    public function delete($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath() . '/payment_methods/credit_card/' . $token;
        $this->_http->delete($path);
        return new Braintree_Result_Successful();
    }

    private static function baseOptions()
    {
        return array('makeDefault', 'verificationMerchantAccountId', 'verifyCard', 'verificationAmount', 'venmoSdkSession');
    }

    private static function baseSignature($options)
    {
         return array(
             'billingAddressId', 'cardholderName', 'cvv', 'number', 'deviceSessionId',
             'expirationDate', 'expirationMonth', 'expirationYear', 'token', 'venmoSdkPaymentMethodCode',
             'deviceData', 'fraudMerchantId', 'paymentMethodNonce',
             array('options' => $options),
             array(
                 'billingAddress' => array(
                     'firstName',
                     'lastName',
                     'company',
                     'countryCodeAlpha2',
                     'countryCodeAlpha3',
                     'countryCodeNumeric',
                     'countryName',
                     'extendedAddress',
                     'locality',
                     'region',
                     'postalCode',
                     'streetAddress'
                 ),
             ),
         );
    }

    public static function createSignature()
    {
        $options = self::baseOptions();
        $options[] = "failOnDuplicatePaymentMethod";
        $signature = self::baseSignature($options);
        $signature[] = 'customerId';
        return $signature;
    }

    public static function updateSignature()
    {
         $signature = self::baseSignature(self::baseOptions());

         $updateExistingBillingSignature = array(
             array(
                 'options' => array(
                     'updateExisting'
                 )
             )
         );

         foreach($signature AS $key => $value) {
             if(is_array($value) and array_key_exists('billingAddress', $value)) {
                 $signature[$key]['billingAddress'] = array_merge_recursive($value['billingAddress'], $updateExistingBillingSignature);
             }
         }

         return $signature;
    }

    /**
     * sends the create request to the gateway
     *
     * @ignore
     * @param string $subPath
     * @param array $params
     * @return mixed
     */
    public function _doCreate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);
    }

    /**
     * verifies that a valid credit card identifier is being used
     * @ignore
     * @param string $identifier
     * @param Optional $string $identifierType type of identifier supplied, default "token"
     * @throws InvalidArgumentException
     */
    private function _validateId($identifier = null, $identifierType = "token")
    {
        if (empty($identifier)) {
           throw new InvalidArgumentException(
                   'expected credit card id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $identifier)) {
            throw new InvalidArgumentException(
                    $identifier . ' is an invalid credit card ' . $identifierType . '.'
                    );
        }
    }

    /**
     * sends the update request to the gateway
     *
     * @ignore
     * @param string $url
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
     * creates a new Braintree_CreditCard object and encapsulates
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
        if (isset($response['creditCard'])) {
            // return a populated instance of Braintree_Address
            return new Braintree_Result_Successful(
                    Braintree_CreditCard::factory($response['creditCard'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
            "Expected address or apiErrorResponse"
            );
        }
    }
}
