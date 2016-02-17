<?php
/**
 * Braintree CustomerGateway module
 * Creates and manages Customers
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Customers, see {@link http://www.braintreepayments.com/gateway/customer-api http://www.braintreepaymentsolutions.com/gateway/customer-api}
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_CustomerGateway
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

    public function all()
    {
        $path = $this->_config->merchantPath() . '/customers/advanced_search_ids';
        $response = $this->_http->post($path);
        $pager = array(
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => array(array())
            );

        return new Braintree_ResourceCollection($response, $pager);
    }

    public function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = Braintree_CustomerSearch::ids()->in($ids)->toparam();
        $path = $this->_config->merchantPath() . '/customers/advanced_search';
        $response = $this->_http->post($path, array('search' => $criteria));

        return Braintree_Util::extractattributeasarray(
            $response['customers'],
            'customer'
        );
    }

    /**
     * Creates a customer using the given +attributes+. If <tt>:id</tt> is not passed,
     * the gateway will generate it.
     *
     * <code>
     *   $result = Braintree_Customer::create(array(
     *     'first_name' => 'John',
     *     'last_name' => 'Smith',
     *     'company' => 'Smith Co.',
     *     'email' => 'john@smith.com',
     *     'website' => 'www.smithco.com',
     *     'fax' => '419-555-1234',
     *     'phone' => '614-555-1234'
     *   ));
     *   if($result->success) {
     *     echo 'Created customer ' . $result->customer->id;
     *   } else {
     *     echo 'Could not create customer, see result->errors';
     *   }
     * </code>
     *
     * @access public
     * @param array $attribs
     * @return object Result, either Successful or Error
     */
    public function create($attribs = array())
    {
        Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        return $this->_doCreate('/customers', array('customer' => $attribs));
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a Braintree_Customer object instead of a Result
     *
     * @access public
     * @param array $attribs
     * @return object
     * @throws Braintree_Exception_ValidationError
     */
    public function createNoValidate($attribs = array())
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
                '/customers/all/confirm_transparent_redirect_request',
                array('id' => $params['id'])
        );
    }

    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public function createCustomerUrl()
    {
        trigger_error("DEPRECATED: Please use Braintree_TransparentRedirectRequest::url", E_USER_NOTICE);
        return $this->_config->baseUrl() . $this->_config->merchantPath() .
                '/customers/all/create_via_transparent_redirect_request';
    }


    /**
     * creates a full array signature of a valid create request
     * @return array gateway create request format
     */
    public static function createSignature()
    {

        $creditCardSignature = Braintree_CreditCardGateway::createSignature();
        unset($creditCardSignature[array_search('customerId', $creditCardSignature)]);
        $signature = array(
            'id', 'company', 'email', 'fax', 'firstName',
            'lastName', 'phone', 'website', 'deviceData',
            'deviceSessionId', 'fraudMerchantId', 'paymentMethodNonce',
            array('creditCard' => $creditCardSignature),
            array('customFields' => array('_anyKey_')),
            );
        return $signature;
    }

    /**
     * creates a full array signature of a valid update request
     * @return array update request format
     */
    public static function updateSignature()
    {
        $creditCardSignature = Braintree_CreditCardGateway::updateSignature();

        foreach($creditCardSignature AS $key => $value) {
            if(is_array($value) and array_key_exists('options', $value)) {
                array_push($creditCardSignature[$key]['options'], 'updateExistingToken');
            }
        }

        $signature = array(
            'id', 'company', 'email', 'fax', 'firstName',
            'lastName', 'phone', 'website', 'deviceData',
            'deviceSessionId', 'fraudMerchantId', 'paymentMethodNonce',
            array('creditCard' => $creditCardSignature),
            array('customFields' => array('_anyKey_')),
            );
        return $signature;
    }


    /**
     * find a customer by id
     *
     * @access public
     * @param string id customer Id
     * @return object Braintree_Customer
     * @throws Braintree_Exception_NotFound
     */
    public function find($id)
    {
        $this->_validateId($id);
        try {
            $path = $this->_config->merchantPath() . '/customers/' . $id;
            $response = $this->_http->get($path);
            return Braintree_Customer::factory($response['customer']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
            'customer with id ' . $id . ' not found'
            );
        }

    }

    /**
     * credit a customer for the passed transaction
     *
     * @access public
     * @param array $attribs
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     */
    public function credit($customerId, $transactionAttribs)
    {
        $this->_validateId($customerId);
        return Braintree_Transaction::credit(
                array_merge($transactionAttribs,
                        array('customerId' => $customerId)
                        )
                );
    }

    /**
     * credit a customer, assuming validations will pass
     *
     * returns a Braintree_Transaction object on success
     *
     * @access public
     * @param array $attribs
     * @return object Braintree_Transaction
     * @throws Braintree_Exception_ValidationError
     */
    public function creditNoValidate($customerId, $transactionAttribs)
    {
        $result = $this->credit($customerId, $transactionAttribs);
        return Braintree_Util::returnObjectOrThrowException('Braintree_Transaction', $result);
    }

    /**
     * delete a customer by id
     *
     * @param string $customerId
     */
    public function delete($customerId)
    {
        $this->_validateId($customerId);
        $path = $this->_config->merchantPath() . '/customers/' . $customerId;
        $this->_http->delete($path);
        return new Braintree_Result_Successful();
    }

    /**
     * create a new sale for a customer
     *
     * @param string $customerId
     * @param array $transactionAttribs
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     * @see Braintree_Transaction::sale()
     */
    public function sale($customerId, $transactionAttribs)
    {
        $this->_validateId($customerId);
        return Braintree_Transaction::sale(
                array_merge($transactionAttribs,
                        array('customerId' => $customerId)
                        )
                );
    }

    /**
     * create a new sale for a customer, assuming validations will pass
     *
     * returns a Braintree_Transaction object on success
     * @access public
     * @param string $customerId
     * @param array $transactionAttribs
     * @return object Braintree_Transaction
     * @throws Braintree_Exception_ValidationsFailed
     * @see Braintree_Transaction::sale()
     */
    public function saleNoValidate($customerId, $transactionAttribs)
    {
        $result = $this->sale($customerId, $transactionAttribs);
        return Braintree_Util::returnObjectOrThrowException('Braintree_Transaction', $result);
    }

    /**
     * Returns a ResourceCollection of customers matching the search query.
     *
     * If <b>query</b> is a string, the search will be a basic search.
     * If <b>query</b> is a hash, the search will be an advanced search.
     * For more detailed information and examples, see {@link http://www.braintreepayments.com/gateway/customer-api#searching http://www.braintreepaymentsolutions.com/gateway/customer-api}
     *
     * @param mixed $query search query
     * @param array $options options such as page number
     * @return object Braintree_ResourceCollection
     * @throws InvalidArgumentException
     */
    public function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $result = $term->toparam();
            if(is_null($result) || empty($result)) {
                throw new InvalidArgumentException('Operator must be provided');
            }

            $criteria[$term->name] = $term->toparam();
        }

        $path = $this->_config->merchantPath() . '/customers/advanced_search_ids';
        $response = $this->_http->post($path, array('search' => $criteria));
        $pager = array(
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => array($query)
            );

        return new Braintree_ResourceCollection($response, $pager);
    }

    /**
     * updates the customer record
     *
     * if calling this method in static context, customerId
     * is the 2nd attribute. customerId is not sent in object context.
     *
     * @access public
     * @param array $attributes
     * @param string $customerId (optional)
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     */
    public function update($customerId, $attributes)
    {
        Braintree_Util::verifyKeys(self::updateSignature(), $attributes);
        $this->_validateId($customerId);
        return $this->_doUpdate(
            'put',
            '/customers/' . $customerId,
            array('customer' => $attributes)
        );
    }

    /**
     * update a customer record, assuming validations will pass
     *
     * if calling this method in static context, customerId
     * is the 2nd attribute. customerId is not sent in object context.
     * returns a Braintree_Customer object on success
     *
     * @access public
     * @param array $attributes
     * @param string $customerId
     * @return object Braintree_Customer
     * @throws Braintree_Exception_ValidationsFailed
     */
    public function updateNoValidate($customerId, $attributes)
    {
        $result = $this->update($customerId, $attributes);
        return Braintree_Util::returnObjectOrThrowException(__CLASS__, $result);
    }
    /**
     *
     * @access public
     * @param none
     * @return string
     */
    public function updateCustomerUrl()
    {
        trigger_error("DEPRECATED: Please use Braintree_TransparentRedirectRequest::url", E_USER_NOTICE);
        return $this->_config->baseUrl() . $this->_config->merchantPath() .
                '/customers/all/update_via_transparent_redirect_request';
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
                '/customers/all/confirm_transparent_redirect_request',
                array('id' => $params['id'])
        );
    }

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $customerAttribs array of customer data
     * @return none
     */
    protected function _initialize($customerAttribs)
    {
        // set the attributes
        $this->_attributes = $customerAttribs;

        // map each address into its own object
        $addressArray = array();
        if (isset($customerAttribs['addresses'])) {

            foreach ($customerAttribs['addresses'] AS $address) {
                $addressArray[] = Braintree_Address::factory($address);
            }
        }
        $this->_set('addresses', $addressArray);

        // map each creditCard into its own object
        $creditCardArray = array();
        if (isset($customerAttribs['creditCards'])) {
            foreach ($customerAttribs['creditCards'] AS $creditCard) {
                $creditCardArray[] = Braintree_CreditCard::factory($creditCard);
            }
        }
        $this->_set('creditCards', $creditCardArray);

        // map each paypalAccount into its own object
        $paypalAccountArray = array();
        if (isset($customerAttribs['paypalAccounts'])) {
            foreach ($customerAttribs['paypalAccounts'] AS $paypalAccount) {
                $paypalAccountArray[] = Braintree_PayPalAccount::factory($paypalAccount);
            }
        }
        $this->_set('paypalAccounts', $paypalAccountArray);

        // map each applePayCard into its own object
        $applePayCardArray = array();
        if (isset($customerAttribs['applePayCards'])) {
            foreach ($customerAttribs['applePayCards'] AS $applePayCard) {
                $applePayCardArray[] = Braintree_applePayCard::factory($applePayCard);
            }
        }
        $this->_set('applePayCards', $applePayCardArray);
    }

    /**
     * returns a string representation of the customer
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Braintree_Customer,
     * or is a Braintree_Customer with a different id
     *
     * @param object $otherCust customer to compare against
     * @return boolean
     */
    public function isEqual($otherCust)
    {
        return !($otherCust instanceof Braintree_Customer) ? false : $this->id === $otherCust->id;
    }

    /**
     * returns an array containt all of the customer's payment methods
     *
     * @return array
     */
    public function paymentMethods()
    {
        return array_merge($this->creditCards, $this->paypalAccounts, $this->applePayCards);
    }

    /**
     * returns the customer's default payment method
     *
     * @return object Braintree_CreditCard or Braintree_PayPalAccount
     */
    public function defaultPaymentMethod()
    {
        $defaultPaymentMethods = array_filter($this->paymentMethods(), 'Braintree_Customer::_defaultPaymentMethodFilter');
        return current($defaultPaymentMethods);
    }

    public static function _defaultPaymentMethodFilter($paymentMethod)
    {
        return $paymentMethod->isDefault();
    }

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of customer data
     */
    protected $_attributes = array(
        'addresses'   => '',
        'company'     => '',
        'creditCards' => '',
        'email'       => '',
        'fax'         => '',
        'firstName'   => '',
        'id'          => '',
        'lastName'    => '',
        'phone'       => '',
        'createdAt'   => '',
        'updatedAt'   => '',
        'website'     => '',
        );

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
     * verifies that a valid customer id is being used
     * @ignore
     * @param string customer id
     * @throws InvalidArgumentException
     */
    private function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected customer id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid customer id.'
                    );
        }
    }


    /* private class methods */

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
     * creates a new Braintree_Customer object and encapsulates
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
        if (isset($response['customer'])) {
            // return a populated instance of Braintree_Customer
            return new Braintree_Result_Successful(
                    Braintree_Customer::factory($response['customer'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
            "Expected customer or apiErrorResponse"
            );
        }
    }
}
