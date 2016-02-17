<?php
/**
 * Braintree AddressGateway module
 * PHP Version 5
 * Creates and manages Braintree Addresses
 *
 * An Address belongs to a Customer. It can be associated to a
 * CreditCard as the billing address. It can also be used
 * as the shipping address when creating a Transaction.
 *
 * @package   Braintree
 * @copyright 2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_AddressGateway
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


    /* public class methods */
    /**
     *
     * @access public
     * @param  array  $attribs
     * @return object Result, either Successful or Error
     */
    public function create($attribs)
    {
        Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        $customerId = isset($attribs['customerId']) ?
            $attribs['customerId'] :
            null;

        $this->_validateCustomerId($customerId);
        unset($attribs['customerId']);
        return $this->_doCreate(
            '/customers/' . $customerId . '/addresses',
            array('address' => $attribs)
        );
    }

    /**
     * attempts the create operation assuming all data will validate
     * returns a Braintree_Address object instead of a Result
     *
     * @access public
     * @param  array $attribs
     * @return object
     * @throws Braintree_Exception_ValidationError
     */
    public function createNoValidate($attribs)
    {
        $result = $this->create($attribs);
        return Braintree_Util::returnObjectOrThrowException(__CLASS__, $result);

    }

    /**
     * delete an address by id
     *
     * @param mixed $customerOrId
     * @param string $addressId
     */
    public function delete($customerOrId = null, $addressId = null)
    {
        $this->_validateId($addressId);
        $customerId = $this->_determineCustomerId($customerOrId);
        $path = $this->_config->merchantPath() . '/customers/' . $customerId . '/addresses/' . $addressId;
        $this->_http->delete($path);
        return new Braintree_Result_Successful();
    }

    /**
     * find an address by id
     *
     * Finds the address with the given <b>addressId</b> that is associated
     * to the given <b>customerOrId</b>.
     * If the address cannot be found, a NotFound exception will be thrown.
     *
     *
     * @access public
     * @param mixed $customerOrId
     * @param string $addressId
     * @return object Braintree_Address
     * @throws Braintree_Exception_NotFound
     */
    public function find($customerOrId, $addressId)
    {

        $customerId = $this->_determineCustomerId($customerOrId);
        $this->_validateId($addressId);

        try {
            $path = $this->_config->merchantPath() . '/customers/' . $customerId . '/addresses/' . $addressId;
            $response = $this->_http->get($path);
            return Braintree_Address::factory($response['address']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
            'address for customer ' . $customerId .
                ' with id ' . $addressId . ' not found.'
            );
        }

    }

    /**
     * updates the address record
     *
     * if calling this method in context,
     * customerOrId is the 2nd attribute, addressId 3rd.
     * customerOrId & addressId are not sent in object context.
     *
     *
     * @access public
     * @param array $attributes
     * @param mixed $customerOrId (only used in call)
     * @param string $addressId (only used in call)
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     */
    public function update($customerOrId, $addressId, $attributes)
    {
        $this->_validateId($addressId);
        $customerId = $this->_determineCustomerId($customerOrId);
        Braintree_Util::verifyKeys(self::updateSignature(), $attributes);

        $path = $this->_config->merchantPath() . '/customers/' . $customerId . '/addresses/' . $addressId;
        $response = $this->_http->put($path, array('address' => $attributes));

        return $this->_verifyGatewayResponse($response);

    }

    /**
     * update an address record, assuming validations will pass
     *
     * if calling this method in context,
     * customerOrId is the 2nd attribute, addressId 3rd.
     * customerOrId & addressId are not sent in object context.
     *
     * @access public
     * @param array $transactionAttribs
     * @param string $customerId
     * @return object Braintree_Transaction
     * @throws Braintree_Exception_ValidationsFailed
     * @see Braintree_Address::update()
     */
    public function updateNoValidate($customerOrId, $addressId, $attributes)
    {
        $result = $this->update($customerOrId, $addressId, $attributes);
        return Braintree_Util::returnObjectOrThrowException(__CLASS__, $result);
    }

    /**
     * creates a full array signature of a valid create request
     * @return array gateway create request format
     */
    public static function createSignature()
    {
        return array(
            'company', 'countryCodeAlpha2', 'countryCodeAlpha3', 'countryCodeNumeric',
            'countryName', 'customerId', 'extendedAddress', 'firstName',
            'lastName', 'locality', 'postalCode', 'region', 'streetAddress'
        );
    }

    /**
     * creates a full array signature of a valid update request
     * @return array gateway update request format
     */
    public static function updateSignature()
    {
        // TODO: remove customerId from update signature
        return self::createSignature();

    }

    /**
     * verifies that a valid address id is being used
     * @ignore
     * @param string $id address id
     * @throws InvalidArgumentException
     */
    private function _validateId($id = null)
    {
        if (empty($id) || trim($id) == "") {
            throw new InvalidArgumentException(
            'expected address id to be set'
            );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException(
            $id . ' is an invalid address id.'
            );
        }
    }

    /**
     * verifies that a valid customer id is being used
     * @ignore
     * @param string $id customer id
     * @throws InvalidArgumentException
     */
    private function _validateCustomerId($id = null)
    {
        if (empty($id) || trim($id) == "") {
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

    /**
     * determines if a string id or Customer object was passed
     * @ignore
     * @param mixed $customerOrId
     * @return string customerId
     */
    private function _determineCustomerId($customerOrId)
    {
        $customerId = ($customerOrId instanceof Braintree_Customer) ? $customerOrId->id : $customerOrId;
        $this->_validateCustomerId($customerId);
        return $customerId;

    }

    /* private class methods */
    /**
     * sends the create request to the gateway
     * @ignore
     * @param string $subPath
     * @param array $params
     * @return mixed
     */
    private function _doCreate($subPath, $params)
    {
        $fullPath = $this->_config->merchantPath() . $subPath;
        $response = $this->_http->post($fullPath, $params);

        return $this->_verifyGatewayResponse($response);

    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Braintree_Address object and encapsulates
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
        if (isset($response['address'])) {
            // return a populated instance of Braintree_Address
            return new Braintree_Result_Successful(
                Braintree_Address::factory($response['address'])
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
