<?php
/**
 * Braintree Transparent Redirect Gateway module
 * Static class providing methods to build Transparent Redirect urls
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_TransparentRedirectGateway
{
    private $_gateway;
    private $_config;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
    }

    /**
     *
     * @ignore
     */
    private static $_transparentRedirectKeys = 'redirectUrl';
    private static $_createCustomerSignature;
    private static $_updateCustomerSignature;
    private static $_transactionSignature;
    private static $_createCreditCardSignature;
    private static $_updateCreditCardSignature;

    /**
     * create signatures for different call types
     * @ignore
     */
    public static function init()
    {

        self::$_createCustomerSignature = array(
            self::$_transparentRedirectKeys,
            array('customer' => Braintree_CustomerGateway::createSignature()),
            );
        self::$_updateCustomerSignature = array(
            self::$_transparentRedirectKeys,
            'customerId',
            array('customer' => Braintree_CustomerGateway::updateSignature()),
            );
        self::$_transactionSignature = array(
            self::$_transparentRedirectKeys,
            array('transaction' => Braintree_TransactionGateway::createSignature()),
            );
        self::$_createCreditCardSignature = array(
            self::$_transparentRedirectKeys,
            array('creditCard' => Braintree_CreditCardGateway::createSignature()),
            );
        self::$_updateCreditCardSignature = array(
            self::$_transparentRedirectKeys,
            'paymentMethodToken',
            array('creditCard' => Braintree_CreditCardGateway::updateSignature()),
            );
    }

    public function confirm($queryString)
    {
        $params = Braintree_TransparentRedirect::parseAndValidateQueryString(
                $queryString
        );
        $confirmationKlasses = array(
            Braintree_TransparentRedirect::CREATE_TRANSACTION => 'Braintree_TransactionGateway',
            Braintree_TransparentRedirect::CREATE_CUSTOMER => 'Braintree_CustomerGateway',
            Braintree_TransparentRedirect::UPDATE_CUSTOMER => 'Braintree_CustomerGateway',
            Braintree_TransparentRedirect::CREATE_PAYMENT_METHOD => 'Braintree_CreditCardGateway',
            Braintree_TransparentRedirect::UPDATE_PAYMENT_METHOD => 'Braintree_CreditCardGateway'
        );
        $confirmationGateway = new $confirmationKlasses[$params["kind"]]($this->_gateway);
        return $confirmationGateway->_doCreate('/transparent_redirect_requests/' . $params['id'] . '/confirm', array());
    }

    /**
     * returns the trData string for creating a credit card,
     * @param array $params
     * @return string
     */
    public function createCreditCardData($params)
    {
        Braintree_Util::verifyKeys(
                self::$_createCreditCardSignature,
                $params
                );
        $params["kind"] = Braintree_TransparentRedirect::CREATE_PAYMENT_METHOD;
        return $this->_data($params);
    }

    /**
     * returns the trData string for creating a customer.
     * @param array $params
     * @return string
     */
    public function createCustomerData($params)
    {
        Braintree_Util::verifyKeys(
                self::$_createCustomerSignature,
                $params
                );
        $params["kind"] = Braintree_TransparentRedirect::CREATE_CUSTOMER;
        return $this->_data($params);

    }

    public function url()
    {
        return $this->_config->baseUrl() . $this->_config->merchantPath() . "/transparent_redirect_requests";
    }

    /**
     * returns the trData string for creating a transaction
     * @param array $params
     * @return string
     */
    public function transactionData($params)
    {
        Braintree_Util::verifyKeys(
                self::$_transactionSignature,
                $params
                );
        $params["kind"] = Braintree_TransparentRedirect::CREATE_TRANSACTION;
        $transactionType = isset($params['transaction']['type']) ?
            $params['transaction']['type'] :
            null;
        if ($transactionType != Braintree_Transaction::SALE && $transactionType != Braintree_Transaction::CREDIT) {
           throw new InvalidArgumentException(
                   'expected transaction[type] of sale or credit, was: ' .
                   $transactionType
                   );
        }

        return $this->_data($params);
    }

    /**
     * Returns the trData string for updating a credit card.
     *
     *  The paymentMethodToken of the credit card to update is required.
     *
     * <code>
     * $trData = Braintree_TransparentRedirect::updateCreditCardData(array(
     *     'redirectUrl' => 'http://example.com/redirect_here',
     *     'paymentMethodToken' => 'token123',
     *   ));
     * </code>
     *
     * @param array $params
     * @return string
     */
    public function updateCreditCardData($params)
    {
        Braintree_Util::verifyKeys(
                self::$_updateCreditCardSignature,
                $params
                );
        if (!isset($params['paymentMethodToken'])) {
            throw new InvalidArgumentException(
                   'expected params to contain paymentMethodToken.'
                   );
        }
        $params["kind"] = Braintree_TransparentRedirect::UPDATE_PAYMENT_METHOD;
        return $this->_data($params);
    }

    /**
     * Returns the trData string for updating a customer.
     *
     *  The customerId of the customer to update is required.
     *
     * <code>
     * $trData = Braintree_TransparentRedirect::updateCustomerData(array(
     *     'redirectUrl' => 'http://example.com/redirect_here',
     *     'customerId' => 'customer123',
     *   ));
     * </code>
     *
     * @param array $params
     * @return string
     */
    public function updateCustomerData($params)
    {
        Braintree_Util::verifyKeys(
                self::$_updateCustomerSignature,
                $params
                );
        if (!isset($params['customerId'])) {
            throw new InvalidArgumentException(
                   'expected params to contain customerId of customer to update'
                   );
        }
        $params["kind"] = Braintree_TransparentRedirect::UPDATE_CUSTOMER;
        return $this->_data($params);
    }

    public function parseAndValidateQueryString($queryString)
    {
        // parse the params into an array
        parse_str($queryString, $params);
        // remove the hash
        $queryStringWithoutHash = null;
        if(preg_match('/^(.*)&hash=[a-f0-9]+$/', $queryString, $match)) {
            $queryStringWithoutHash = $match[1];
        }

        if($params['http_status'] != '200') {
            $message = null;
            if(array_key_exists('bt_message', $params)) {
                $message = $params['bt_message'];
            }
            Braintree_Util::throwStatusCodeException($params['http_status'], $message);
        }

        // recreate the hash and compare it
        if($this->_hash($queryStringWithoutHash) == $params['hash']) {
            return $params;
        } else {
            throw new Braintree_Exception_ForgedQueryString();
        }
    }


    /**
     *
     * @ignore
     */
    private function _data($params)
    {
        if (!isset($params['redirectUrl'])) {
            throw new InvalidArgumentException(
                    'expected params to contain redirectUrl'
                    );
        }
        $params = $this->_underscoreKeys($params);
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $trDataParams = array_merge($params,
            array(
                'api_version' => Braintree_Configuration::API_VERSION,
                'public_key'  => $this->_config->publicKey(),
                'time'        => $now->format('YmdHis'),
            )
        );
        ksort($trDataParams);
        $urlEncodedData = http_build_query($trDataParams, null, "&");
        $signatureService = new Braintree_SignatureService(
            $this->_config->privateKey(),
            "Braintree_Digest::hexDigestSha1"
        );
        return $signatureService->sign($urlEncodedData);
    }

    private function _underscoreKeys($array)
    {
        foreach($array as $key=>$value)
        {
            $newKey = Braintree_Util::camelCaseToDelimiter($key, '_');
            unset($array[$key]);
            if (is_array($value))
            {
                $array[$newKey] = $this->_underscoreKeys($value);
            }
            else
            {
                $array[$newKey] = $value;
            }
        }
        return $array;
    }

    /**
     * @ignore
     */
    private function _hash($string)
    {
        return Braintree_Digest::hexDigestSha1($this->_config->privateKey(), $string);
    }
}
Braintree_TransparentRedirectGateway::init();
