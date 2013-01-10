<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * oAuth Server
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Model_Server
{
    /**#@+
     * OAuth result statuses
     */
    const ERR_OK                        = 0;
    const ERR_VERSION_REJECTED          = 1;
    const ERR_PARAMETER_ABSENT          = 2;
    const ERR_PARAMETER_REJECTED        = 3;
    const ERR_TIMESTAMP_REFUSED         = 4;
    const ERR_NONCE_USED                = 5;
    const ERR_SIGNATURE_METHOD_REJECTED = 6;
    const ERR_SIGNATURE_INVALID         = 7;
    const ERR_CONSUMER_KEY_REJECTED     = 8;
    const ERR_TOKEN_USED                = 9;
    const ERR_TOKEN_EXPIRED             = 10;
    const ERR_TOKEN_REVOKED             = 11;
    const ERR_TOKEN_REJECTED            = 12;
    const ERR_VERIFIER_INVALID          = 13;
    const ERR_PERMISSION_UNKNOWN        = 14;
    const ERR_PERMISSION_DENIED         = 15;
    /**#@-*/

    /**#@+
     * Signature Methods
     */
    const SIGNATURE_HMAC  = 'HMAC-SHA1';
    const SIGNATURE_RSA   = 'RSA-SHA1';
    const SIGNATURE_PLAIN = 'PLAINTEXT';
    /**#@-*/

    /**#@+
     * Request Types
     */
    const REQUEST_INITIATE  = 'initiate';  // ask for temporary credentials
    const REQUEST_AUTHORIZE = 'authorize'; // display authorize form
    const REQUEST_TOKEN     = 'token';     // ask for permanent credentials
    const REQUEST_RESOURCE  = 'resource';  // ask for protected resource using permanent credentials
    /**#@-*/

    /**#@+
     * HTTP Response Codes
     */
    const HTTP_OK             = 200;
    const HTTP_BAD_REQUEST    = 400;
    const HTTP_UNAUTHORIZED   = 401;
    const HTTP_INTERNAL_ERROR = 500;
    /**#@-*/

    /**
     * Possible time deviation for timestamp validation in sec.
     */
    const TIME_DEVIATION = 600;

    /**
     * Value of callback URL when it is established or if the client is unable to receive callbacks
     *
     * @link http://tools.ietf.org/html/rfc5849#section-2.1     Requirement in RFC-5849
     */
    const CALLBACK_ESTABLISHED = 'oob';

    /**
     * Consumer object
     *
     * @var Mage_Oauth_Model_ConsumerInterface
     */
    protected $_consumer;

    /**
     * Error code to error messages pairs
     *
     * @var array
     */
    protected $_errors = array(
        self::ERR_VERSION_REJECTED          => 'version_rejected',
        self::ERR_PARAMETER_ABSENT          => 'parameter_absent',
        self::ERR_PARAMETER_REJECTED        => 'parameter_rejected',
        self::ERR_TIMESTAMP_REFUSED         => 'timestamp_refused',
        self::ERR_NONCE_USED                => 'nonce_used',
        self::ERR_SIGNATURE_METHOD_REJECTED => 'signature_method_rejected',
        self::ERR_SIGNATURE_INVALID         => 'signature_invalid',
        self::ERR_CONSUMER_KEY_REJECTED     => 'consumer_key_rejected',
        self::ERR_TOKEN_USED                => 'token_used',
        self::ERR_TOKEN_EXPIRED             => 'token_expired',
        self::ERR_TOKEN_REVOKED             => 'token_revoked',
        self::ERR_TOKEN_REJECTED            => 'token_rejected',
        self::ERR_VERIFIER_INVALID          => 'verifier_invalid',
        self::ERR_PERMISSION_UNKNOWN        => 'permission_unknown',
        self::ERR_PERMISSION_DENIED         => 'permission_denied'
    );

    /**
     * Error code to HTTP error code
     *
     * @var array
     */
    protected $_errorsToHttpCode = array(
        self::ERR_VERSION_REJECTED          => self::HTTP_BAD_REQUEST,
        self::ERR_PARAMETER_ABSENT          => self::HTTP_BAD_REQUEST,
        self::ERR_PARAMETER_REJECTED        => self::HTTP_BAD_REQUEST,
        self::ERR_TIMESTAMP_REFUSED         => self::HTTP_BAD_REQUEST,
        self::ERR_NONCE_USED                => self::HTTP_UNAUTHORIZED,
        self::ERR_SIGNATURE_METHOD_REJECTED => self::HTTP_BAD_REQUEST,
        self::ERR_SIGNATURE_INVALID         => self::HTTP_UNAUTHORIZED,
        self::ERR_CONSUMER_KEY_REJECTED     => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_USED                => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_EXPIRED             => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_REVOKED             => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_REJECTED            => self::HTTP_UNAUTHORIZED,
        self::ERR_VERIFIER_INVALID          => self::HTTP_UNAUTHORIZED,
        self::ERR_PERMISSION_UNKNOWN        => self::HTTP_UNAUTHORIZED,
        self::ERR_PERMISSION_DENIED         => self::HTTP_UNAUTHORIZED
    );

    /**
     * Request parameters
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Protocol parameters
     *
     * @var array
     */
    protected $_protocolParams = array();

    /**
     * Request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Request type: initiate, permanent token request or authorized one
     *
     * @var string
     */
    protected $_requestType;

    /**
     * Response object
     *
     * @var Zend_Controller_Response_Http
     */
    protected $_response = null;

    /**
     * Token object
     *
     * @var Mage_Oauth_Model_Token
     */
    protected $_token;

    /** @var Mage_Oauth_Model_Token_Factory */
    protected $_tokenFactory;

    /** @var Mage_Oauth_Model_Consumer_Factory */
    protected $_consumerFactory;

    /** @var Mage_Oauth_Model_Nonce_Factory */
    protected $_nonceFactory;

    /**
     * Internal constructor not depended on params
     *
     * @param Zend_Controller_Request_Http $request OPTIONAL Request object (If not specified - use singleton)
     * @param Mage_Oauth_Model_Token_Factory $tokenFactory
     * @param Mage_Oauth_Model_Consumer_Factory $consumerFactory
     * @param Mage_Oauth_Model_Nonce_Factory $nonceFactory
     * @throws Exception
     */
    public function __construct(
        Zend_Controller_Request_Http $request,
        Mage_Oauth_Model_Token_Factory $tokenFactory,
        Mage_Oauth_Model_Consumer_Factory $consumerFactory,
        Mage_Oauth_Model_Nonce_Factory $nonceFactory
    ) {
        $this->_request = $request;
        $this->_tokenFactory = $tokenFactory;
        $this->_consumerFactory = $consumerFactory;
        $this->_nonceFactory = $nonceFactory;
    }

    /**
     * Retrieve protocol and request parameters from request object
     *
     * @param string $authHeaderValue
     * @link http://tools.ietf.org/html/rfc5849#section-3.5
     * @return Mage_Oauth_Model_Server
     */
    protected function _fetchParams($authHeaderValue = null)
    {
        if (is_null($authHeaderValue)) {
            $authHeaderValue = $this->_request->getHeader('Authorization');
        }

        if ($authHeaderValue && 'oauth' === strtolower(substr($authHeaderValue, 0, 5))) {
            $authHeaderValue = substr($authHeaderValue, 6); // ignore 'OAuth ' at the beginning

            foreach (explode(',', $authHeaderValue) as $paramStr) {
                $nameAndValue = explode('=', trim($paramStr), 2);

                if (count($nameAndValue) < 2) {
                    continue;
                }
                if ($this->_isProtocolParameter($nameAndValue[0])) {
                    $this->_protocolParams[rawurldecode($nameAndValue[0])] = rawurldecode(trim($nameAndValue[1], '"'));
                }
            }
        }
        $contentTypeHeader = $this->_request->getHeader(Zend_Http_Client::CONTENT_TYPE);

        if ($contentTypeHeader && 0 === strpos($contentTypeHeader, Zend_Http_Client::ENC_URLENCODED)) {
            $protocolParamsNotSet = !$this->_protocolParams;

            parse_str($this->_request->getRawBody(), $bodyParams);

            foreach ($bodyParams as $bodyParamName => $bodyParamValue) {
                if (!$this->_isProtocolParameter($bodyParamName)) {
                    $this->_params[$bodyParamName] = $bodyParamValue;
                } elseif ($protocolParamsNotSet) {
                    $this->_protocolParams[$bodyParamName] = $bodyParamValue;
                }
            }
        }
        $protocolParamsNotSet = !$this->_protocolParams;

        $url = $this->_request->getScheme() . '://' . $this->_request->getHttpHost() . $this->_request->getRequestUri();

        if (($queryString = Zend_Uri_Http::fromString($url)->getQuery())) {
            foreach (explode('&', $queryString) as $paramToValue) {
                $paramData = explode('=', $paramToValue);

                if (2 === count($paramData) && !$this->_isProtocolParameter($paramData[0])) {
                    $this->_params[rawurldecode($paramData[0])] = rawurldecode($paramData[1]);
                }
            }
        }
        if ($protocolParamsNotSet) {
            $this->_fetchProtocolParamsFromQuery();
        }
        return $this;
    }

    /**
     * Retrieve protocol parameters from query string
     *
     * @return Mage_Oauth_Model_Server
     */
    protected function _fetchProtocolParamsFromQuery()
    {
        foreach ($this->_request->getQuery() as $queryParamName => $queryParamValue) {
            if ($this->_isProtocolParameter($queryParamName)) {
                $this->_protocolParams[$queryParamName] = $queryParamValue;
            }
        }
        return $this;
    }

    /**
     * Retrieve response object
     *
     * @return Zend_Controller_Response_Http
     */
    protected function _getResponse()
    {
        if (null === $this->_response) {
            $this->setResponse(Mage::app()->getResponse());
        }
        return $this->_response;
    }

    /**
     * Initialize consumer
     *
     * @throws Mage_Oauth_Exception
     */
    protected function _initConsumer()
    {
        $this->_consumer = $this->_consumerFactory->create();
        $this->_consumer->loadByKey($this->_protocolParams['oauth_consumer_key']);

        if (!$this->_consumer->getId()) {
            $this->_throwException('', self::ERR_CONSUMER_KEY_REJECTED);
        }
    }

    /**
     * Load token object, validate it depending on request type, set access data and save
     *
     * @return Mage_Oauth_Model_Server
     * @throws Mage_Oauth_Exception
     */
    protected function _initToken()
    {
        $this->_token = $this->_tokenFactory->create();

        if (self::REQUEST_INITIATE != $this->_requestType) {
            $this->_validateTokenParam();

            $this->_token->load($this->_protocolParams['oauth_token'], 'token');

            if (!$this->_token->getId()) {
                $this->_throwException('', self::ERR_TOKEN_REJECTED);
            }
            if (self::REQUEST_TOKEN == $this->_requestType) {
                $this->_validateVerifierParam();

                if ($this->_token->getVerifier() != $this->_protocolParams['oauth_verifier']) {
                    $this->_throwException('', self::ERR_VERIFIER_INVALID);
                }
                if ($this->_token->getConsumerId() != $this->_consumer->getId()) {
                    $this->_throwException('', self::ERR_TOKEN_REJECTED);
                }
                if (Mage_Oauth_Model_Token::TYPE_REQUEST != $this->_token->getType()) {
                    $this->_throwException('', self::ERR_TOKEN_USED);
                }
            } elseif (self::REQUEST_AUTHORIZE == $this->_requestType) {
                if ($this->_token->getAuthorized()) {
                    $this->_throwException('', self::ERR_TOKEN_USED);
                }
            } elseif (self::REQUEST_RESOURCE == $this->_requestType) {
                if (Mage_Oauth_Model_Token::TYPE_ACCESS != $this->_token->getType()) {
                    $this->_throwException('', self::ERR_TOKEN_REJECTED);
                }
                if ($this->_token->getRevoked()) {
                    $this->_throwException('', self::ERR_TOKEN_REVOKED);
                }
                //TODO: Implement check for expiration (after it implemented in token model)
            }
        } else {
            $this->_validateCallbackUrlParam();
        }
        return $this;
    }

    /**
     * Is attribute is referred to oAuth protocol?
     *
     * @param string $attrName
     * @return bool
     */
    protected function _isProtocolParameter($attrName)
    {
        return (bool) preg_match('/oauth_[a-z_-]+/', $attrName);
    }

    /**
     * Extract parameters from sources (GET, FormBody, Authorization header), decode them and validate
     *
     * @param string $requestType Request type - one of REQUEST_... class constant
     * @return Mage_Oauth_Model_Server
     * @throws Mage_Core_Exception
     */
    protected function _processRequest($requestType)
    {
        // validate request type to process (AUTHORIZE request is not allowed for method)
        if (self::REQUEST_INITIATE != $requestType
            && self::REQUEST_RESOURCE != $requestType
            && self::REQUEST_TOKEN != $requestType
        ) {
            Mage::throwException('Invalid request type');
        }
        $this->_requestType = $requestType;

        // get parameters from request
        $this->_fetchParams();

        // make generic validation of request parameters
        $this->_validateProtocolParams();

        // initialize consumer
        $this->_initConsumer();

        // initialize token
        $this->_initToken();

        // validate signature
        $this->_validateSignature();

        // save token if signature validation succeed
        $this->_saveToken();

        return $this;
    }

    /**
     * Save token
     */
    protected function _saveToken()
    {
        if (self::REQUEST_INITIATE == $this->_requestType) {
            if (self::CALLBACK_ESTABLISHED == $this->_protocolParams['oauth_callback']
                && $this->_consumer->getCallBackUrl()) {
                $callbackUrl = $this->_consumer->getCallBackUrl();
            } else {
                $callbackUrl = $this->_protocolParams['oauth_callback'];
            }
            $this->_token->createRequestToken($this->_consumer->getId(), $callbackUrl);
        } elseif (self::REQUEST_TOKEN == $this->_requestType) {
            $this->_token->convertToAccess();
        }
    }

    /**
     * Throw OAuth exception
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @throws Mage_Oauth_Exception
     */
    protected function _throwException($message = '', $code = 0)
    {
        throw Mage::exception('Mage_Oauth', $message, $code);
    }

    /**
     * Check for 'oauth_callback' parameter
     */
    protected function _validateCallbackUrlParam()
    {
        if (!isset($this->_protocolParams['oauth_callback'])) {
            $this->_throwException('oauth_callback', self::ERR_PARAMETER_ABSENT);
        }
        if (!is_string($this->_protocolParams['oauth_callback'])) {
            $this->_throwException('oauth_callback', self::ERR_PARAMETER_REJECTED);
        }
        if (self::CALLBACK_ESTABLISHED != $this->_protocolParams['oauth_callback']
            && !Zend_Uri::check($this->_protocolParams['oauth_callback'])
        ) {
            $this->_throwException('oauth_callback', self::ERR_PARAMETER_REJECTED);
        }
    }

    /**
     * Validate nonce request data
     *
     * @param string $nonce Nonce string
     * @param string|int $timestamp UNIX Timestamp
     */
    protected function _validateNonce($nonce, $timestamp)
    {
        $timestamp = (int) $timestamp;

        if ($timestamp <= 0 || $timestamp > (time() + self::TIME_DEVIATION)) {
            $this->_throwException('', self::ERR_TIMESTAMP_REFUSED);
        }
        $nonceObj = $this->_nonceFactory->create();
        $nonceObj->load($nonce, 'nonce');

        if ($nonceObj->getTimestamp() == $timestamp) {
            $this->_throwException('', self::ERR_NONCE_USED);
        }
        $nonceObj->setNonce($nonce)
            ->setTimestamp($timestamp)
            ->save();
    }

    /**
     * Validate protocol parameters
     *
     * @throws Mage_Oauth_Exception
     */
    protected function _validateProtocolParams()
    {
        // validate version if specified
        if (isset($this->_protocolParams['oauth_version']) && '1.0' != $this->_protocolParams['oauth_version']) {
            $this->_throwException('', self::ERR_VERSION_REJECTED);
        }
        // required parameters validation
        foreach (array('oauth_consumer_key', 'oauth_signature_method', 'oauth_signature') as $reqField) {
            if (empty($this->_protocolParams[$reqField])) {
                $this->_throwException($reqField, self::ERR_PARAMETER_ABSENT);
            }
        }
        // validate parameters type
        foreach ($this->_protocolParams as $paramName => $paramValue) {
            if (!is_string($paramValue)) {
                $this->_throwException($paramName, self::ERR_PARAMETER_REJECTED);
            }
        }
        // validate signature method
        if (!in_array($this->_protocolParams['oauth_signature_method'], self::getSupportedSignatureMethods())) {
            $this->_throwException('', self::ERR_SIGNATURE_METHOD_REJECTED);
        }
        // validate nonce data if signature method is not PLAINTEXT
        if (self::SIGNATURE_PLAIN != $this->_protocolParams['oauth_signature_method']) {
            if (empty($this->_protocolParams['oauth_nonce'])) {
                $this->_throwException('oauth_nonce', self::ERR_PARAMETER_ABSENT);
            }
            if (empty($this->_protocolParams['oauth_timestamp'])) {
                $this->_throwException('oauth_timestamp', self::ERR_PARAMETER_ABSENT);
            }
            $this->_validateNonce($this->_protocolParams['oauth_nonce'], $this->_protocolParams['oauth_timestamp']);
        }
    }

    /**
     * Validate signature
     *
     * @throws Mage_Oauth_Exception
     */
    protected function _validateSignature()
    {
        $util = new Zend_Oauth_Http_Utility();

        $calculatedSign = $util->sign(
            array_merge($this->_params, $this->_protocolParams),
            $this->_protocolParams['oauth_signature_method'],
            $this->_consumer->getSecret(),
            !is_null($this->_token) ? $this->_token->getSecret() : null,
            $this->_request->getMethod(),
            $this->_request->getScheme() . '://' . $this->_request->getHttpHost() . $this->_request->getRequestUri()
        );

        if ($calculatedSign != $this->_protocolParams['oauth_signature']) {
            $this->_throwException('Invalid signature.', self::ERR_SIGNATURE_INVALID);
        }
    }

    /**
     * Check for 'oauth_token' parameter
     */
    protected function _validateTokenParam()
    {
        if (empty($this->_protocolParams['oauth_token'])) {
            $this->_throwException('oauth_token', self::ERR_PARAMETER_ABSENT);
        }
        if (!is_string($this->_protocolParams['oauth_token'])) {
            $this->_throwException('', self::ERR_TOKEN_REJECTED);
        }
        if (strlen($this->_protocolParams['oauth_token']) != Mage_Oauth_Model_Token::LENGTH_TOKEN) {
            $this->_throwException('', self::ERR_TOKEN_REJECTED);
        }
    }

    /**
     * Check for 'oauth_verifier' parameter
     */
    protected function _validateVerifierParam()
    {
        if (empty($this->_protocolParams['oauth_verifier'])) {
            $this->_throwException('oauth_verifier', self::ERR_PARAMETER_ABSENT);
        }
        if (!is_string($this->_protocolParams['oauth_verifier'])) {
            $this->_throwException('', self::ERR_VERIFIER_INVALID);
        }
        if (strlen($this->_protocolParams['oauth_verifier']) != Mage_Oauth_Model_Token::LENGTH_VERIFIER) {
            $this->_throwException('', self::ERR_VERIFIER_INVALID);
        }
    }

    /**
     * Process request for permanent access token
     */
    public function accessToken()
    {
        try {
            $this->_processRequest(self::REQUEST_TOKEN);

            $response = $this->_token->toString();
        } catch (Exception $e) {
            $response = $this->reportProblem($e);
        }
        $this->_getResponse()->setBody($response);
    }

    /**
     * Validate request, authorize token and return it
     *
     * @param int $userId Authorization user identifier
     * @param string $userType Authorization user type
     * @return Mage_Oauth_Model_Token
     */
    public function authorizeToken($userId, $userType)
    {
        $token = $this->checkAuthorizeRequest();

        $token->authorize($userId, $userType);

        return $token;
    }

    /**
     * Validate request with access token for specified URL
     *
     * @return Mage_Oauth_Model_Token
     */
    public function checkAccessRequest()
    {
        $this->_processRequest(self::REQUEST_RESOURCE);

        return $this->_token;
    }

    /**
     * Check authorize request for validity and return token
     *
     * @return Mage_Oauth_Model_Token
     */
    public function checkAuthorizeRequest()
    {
        if (!$this->_request->isGet()) {
            Mage::throwException('Request is not GET');
        }
        $this->_requestType = self::REQUEST_AUTHORIZE;

        $this->_fetchProtocolParamsFromQuery();
        $this->_initToken();

        return $this->_token;
    }

    /**
     * Retrieve array of supported signature methods
     *
     * @return array
     */
    public static function getSupportedSignatureMethods()
    {
        return array(self::SIGNATURE_RSA, self::SIGNATURE_HMAC, self::SIGNATURE_PLAIN);
    }

    /**
     * Process request for temporary (initiative) token
     */
    public function initiateToken()
    {
        try {
            $this->_processRequest(self::REQUEST_INITIATE);

            $response = $this->_token->toString() . '&oauth_callback_confirmed=true';
        } catch (Exception $e) {
            $response = $this->reportProblem($e);
        }
        $this->_getResponse()->setBody($response);
    }

    /**
     * Create response string for problem during request and set HTTP error code
     *
     * @param Exception $e
     * @param Zend_Controller_Response_Http $response OPTIONAL If NULL - will use internal getter
     * @return string
     */
    public function reportProblem(Exception $e, Zend_Controller_Response_Http $response = null)
    {
        $eMsg = $e->getMessage();

        if ($e instanceof Mage_Oauth_Exception) {
            $eCode = $e->getCode();

            if (isset($this->_errors[$eCode])) {
                $errorMsg = $this->_errors[$eCode];
                $responseCode = $this->_errorsToHttpCode[$eCode];
            } else {
                $errorMsg = 'unknown_problem&code=' . $eCode;
                $responseCode = self::HTTP_INTERNAL_ERROR;
            }
            if (self::ERR_PARAMETER_ABSENT == $eCode) {
                $errorMsg .= '&oauth_parameters_absent=' . $eMsg;
            } elseif ($eMsg) {
                $errorMsg .= '&message=' . $eMsg;
            }
        } else {
            $errorMsg = 'internal_error&message=' . ($eMsg ? $eMsg : 'empty_message');
            $responseCode = self::HTTP_INTERNAL_ERROR;
        }
        if (!$response) {
            $response = $this->_getResponse();
        }
        $response->setHttpResponseCode($responseCode);

        return 'oauth_problem=' . $errorMsg;
    }

    /**
     * Set response object
     *
     * @param Zend_Controller_Response_Http $response
     * @return Mage_Oauth_Model_Server
     */
    public function setResponse(Zend_Controller_Response_Http $response)
    {
        $this->_response = $response;

        $this->_response->setHeader(Zend_Http_Client::CONTENT_TYPE, Zend_Http_Client::ENC_URLENCODED, true);
        $this->_response->setHttpResponseCode(self::HTTP_OK);

        return $this;
    }
}
