<?php
/**
 * Web API Oauth Service.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Class \Magento\Oauth\Service\OauthV1
 */
namespace Magento\Oauth\Service;

class OauthV1 implements \Magento\Oauth\Service\OauthV1Interface
{
    /**
     * Possible time deviation for timestamp validation in sec.
     */
    const TIME_DEVIATION = 600;

    /**
     * Consumer xpath settings
     */
    const XML_PATH_CONSUMER_EXPIRATION_PERIOD = 'oauth/consumer/expiration_period';

    /**
     * Consumer expiration period in seconds
     */
    const CONSUMER_EXPIRATION_PERIOD_DEFAULT = 300;

    /**
     * Consumer HTTP POST maxredirects xpath
     */
    const XML_PATH_CONSUMER_POST_MAXREDIRECTS = 'oauth/consumer/post_maxredirects';

    /**
     * Consumer HTTPS POST maxredirects default
     */
    const CONSUMER_POST_MAXREDIRECTS = 0;

    /**
     * Consumer HTTP TIMEOUT xpath
     */
    const XML_PATH_CONSUMER_POST_TIMEOUT = 'oauth/consumer/post_timeout';

    /**
     * Consumer HTTP TIMEOUT default
     */
    const CONSUMER_POST_TIMEOUT = 5;

    /** @var  \Magento\Oauth\Model\Consumer\Factory */
    private $_consumerFactory;

    /** @var  \Magento\Oauth\Model\Nonce\Factory */
    private $_nonceFactory;

    /** @var  \Magento\Oauth\Model\Token\Factory */
    private $_tokenFactory;

    /** @var  \Magento\Core\Model\StoreManagerInterface */
    protected $_storeManager;

    /** @var  \Magento\HTTP\ZendClient */
    protected $_httpClient;

    /** @var  \Zend_Oauth_Http_Utility */
    protected $_httpUtility;

    /** @var \Magento\Core\Model\Date */
    protected $_date;

    /**
     * @param \Magento\Oauth\Model\Consumer\Factory $consumerFactory
     * @param \Magento\Oauth\Model\Nonce\Factory $nonceFactory
     * @param \Magento\Oauth\Model\Token\Factory $tokenFactory
     * @param \Magento\Core\Model\StoreManagerInterface
     * @param \Magento\HTTP\ZendClient
     * @param \Zend_Oauth_Http_Utility $httpUtility
     * @param \Magento\Core\Model\Date $date
     */
    public function __construct(
        \Magento\Oauth\Model\Consumer\Factory $consumerFactory,
        \Magento\Oauth\Model\Nonce\Factory $nonceFactory,
        \Magento\Oauth\Model\Token\Factory $tokenFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\HTTP\ZendClient $httpClient,
        \Zend_Oauth_Http_Utility $httpUtility,
        \Magento\Core\Model\Date $date
    ) {
        $this->_consumerFactory = $consumerFactory;
        $this->_nonceFactory = $nonceFactory;
        $this->_tokenFactory = $tokenFactory;
        $this->_storeManager = $storeManager;
        $this->_httpClient = $httpClient;
        $this->_httpUtility = $httpUtility;
        $this->_date = $date;
    }

    /**
     * Retrieve array of supported signature methods.
     *
     * @return array - Supported HMAC-SHA1 and HMAC-SHA256 signature methods.
     */
    public static function getSupportedSignatureMethods()
    {
        return array(self::SIGNATURE_SHA1, self::SIGNATURE_SHA256);
    }

    /**
     * {@inheritdoc}
     */
    public function createConsumer($consumerData)
    {
        try {
            $consumer = $this->_consumerFactory->create($consumerData);
            $consumer->save();
            return $consumer->getData();
        } catch (\Magento\Core\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Oauth\Exception(__('Unexpected error. Unable to create OAuth Consumer account.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postToConsumer($request)
    {
        try {
            $consumerData = $this->_getConsumer($request['consumer_id'])->getData();
            $storeBaseUrl = $this->_storeManager->getStore()->getBaseUrl();
            $verifier = $this->_tokenFactory->create()->createVerifierToken($request['consumer_id']);
            $this->_httpClient->setUri($consumerData['http_post_url']);
            $this->_httpClient->setParameterPost(
                array(
                    'oauth_consumer_key' => $consumerData['key'],
                    'oauth_consumer_secret' => $consumerData['secret'],
                    'store_base_url' => $storeBaseUrl,
                    'oauth_verifier' => $verifier->getVerifier()
                )
            );
            $maxredirects = $this->_getConfigValue(
                self::XML_PATH_CONSUMER_POST_MAXREDIRECTS,
                self::CONSUMER_POST_MAXREDIRECTS
            );
            $timeout = $this->_getConfigValue(
                self::XML_PATH_CONSUMER_POST_TIMEOUT,
                self::CONSUMER_POST_TIMEOUT
            );
            $this->_httpClient->setConfig(array('maxredirects' => $maxredirects, 'timeout' => $timeout));
            $this->_httpClient->request(\Magento\HTTP\ZendClient::POST);
            return array('oauth_verifier' => $verifier->getVerifier());
        } catch (\Magento\Core\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Oauth\Exception(__('Unexpected error. Unable to post data to consumer.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestToken($signedRequest)
    {
        $this->_validateVersionParam($signedRequest['oauth_version']);
        $consumer = $this->_getConsumerByKey($signedRequest['oauth_consumer_key']);
        // must use consumer within expiration period
        $consumerTS = strtotime($consumer->getCreatedAt());
        $expiry = $this->_getConfigValue(
            self::XML_PATH_CONSUMER_EXPIRATION_PERIOD,
            self::CONSUMER_EXPIRATION_PERIOD_DEFAULT
        );
        if ($this->_date->timestamp() - $consumerTS > $expiry) {
            throw new \Magento\Oauth\Exception('', self::ERR_CONSUMER_KEY_INVALID);
        }
        $this->_validateNonce($signedRequest['oauth_nonce'], $consumer->getId(), $signedRequest['oauth_timestamp']);
        $token = $this->_getTokenByConsumer($consumer->getId());
        if ($token->getType() != \Magento\Oauth\Model\Token::TYPE_VERIFIER) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }
        $this->_validateSignature(
            $signedRequest,
            $consumer->getSecret(),
            $signedRequest['http_method'],
            $signedRequest['request_url']
        );
        $requestToken = $token->createRequestToken($token->getId(), $consumer->getCallBackUrl());
        return array('oauth_token' => $requestToken->getToken(), 'oauth_token_secret' => $requestToken->getSecret());
    }

    /**
     * TODO: log the request token in dev mode since its not persisted
     *
     * {@inheritdoc}
     */
    public function getAccessToken($request)
    {
        $required = array(
            'oauth_consumer_key',
            'oauth_signature',
            'oauth_signature_method',
            'oauth_nonce',
            'oauth_timestamp',
            'oauth_token',
            'oauth_verifier',
            'request_url',
            'http_method',
        );

        // Make generic validation of request parameters
        $this->_validateProtocolParams($request, $required);

        $oauthToken = $request['oauth_token'];
        $requestUrl = $request['request_url'];
        $httpMethod = $request['http_method'];
        $consumerKeyParam = $request['oauth_consumer_key'];

        $consumer = $this->_getConsumerByKey($consumerKeyParam);
        $token = $this->_getToken($oauthToken);

        if (!$this->_isTokenAssociatedToConsumer($token, $consumer)) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }

        // The pre-auth token has a value of "request" in the type when it is requested and created initially.
        // In this flow (token flow) the token has to be of type "request" else its marked as reused.
        if (\Magento\Oauth\Model\Token::TYPE_REQUEST != $token->getType()) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_USED);
        }

        $this->_validateVerifierParam($request['oauth_verifier'], $token->getVerifier());

        $this->_validateSignature(
            $request,
            $consumer->getSecret(),
            $httpMethod,
            $requestUrl,
            $token->getSecret()
        );

        $accessToken = $token->convertToAccess();
        return array('oauth_token' => $accessToken->getToken(), 'oauth_token_secret' => $accessToken->getSecret());
    }

    /**
     * {@inheritdoc}
     */
    public function validateAccessTokenRequest($request)
    {
        $required = array(
            'oauth_consumer_key',
            'oauth_signature',
            'oauth_signature_method',
            'oauth_nonce',
            'oauth_timestamp',
            'oauth_token',
            'http_method',
            'request_url',
        );

        // make generic validation of request parameters
        $this->_validateProtocolParams($request, $required);

        $oauthToken = $request['oauth_token'];
        $requestUrl = $request['request_url'];
        $httpMethod = $request['http_method'];
        $consumerKey = $request['oauth_consumer_key'];

        $consumer = $this->_getConsumerByKey($consumerKey);
        $token = $this->_getToken($oauthToken);

        if (!$this->_isTokenAssociatedToConsumer($token, $consumer)) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }

        if (\Magento\Oauth\Model\Token::TYPE_ACCESS != $token->getType()) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }
        if ($token->getRevoked()) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REVOKED);
        }

        $this->_validateSignature(
            $request,
            $consumer->getSecret(),
            $httpMethod,
            $requestUrl,
            $token->getSecret()
        );

        // If no exceptions were raised return as a valid token
        return array('isValid' => true);
    }

    /**
     * {@inheritdoc}
     */
    public function validateAccessToken($request)
    {
        $token = $this->_getToken($request['token']);

        //Make sure a consumer is associated with the token
        $this->_getConsumer($token->getConsumerId());

        if (\Magento\Oauth\Model\Token::TYPE_ACCESS != $token->getType()) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }
        if ($token->getRevoked()) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REVOKED);
        }

        return array('isValid' => true);
    }


    /**
     * Validate (oauth_nonce) Nonce string.
     *
     * @param string $nonce - Nonce string
     * @param int $consumerId - Consumer Id (Entity Id)
     * @param string|int $timestamp - Unix timestamp
     * @throws \Magento\Oauth\Exception
     */
    protected function _validateNonce($nonce, $consumerId, $timestamp)
    {
        try {
            $timestamp = (int)$timestamp;
            if ($timestamp <= 0 || $timestamp > (time() + self::TIME_DEVIATION)) {
                throw new \Magento\Oauth\Exception(
                    __('Incorrect timestamp value in the oauth_timestamp parameter.'),
                    self::ERR_TIMESTAMP_REFUSED
                );
            }

            $nonceObj = $this->_getNonce($nonce, $consumerId);

            if ($nonceObj->getConsumerId()) {
                throw new \Magento\Oauth\Exception(
                    __('The nonce is already being used by the consumer with id %1.', $consumerId),
                    self::ERR_NONCE_USED
                );
            }

            $consumer = $this->_getConsumer($consumerId);

            if ($nonceObj->getTimestamp() == $timestamp) {
                throw new \Magento\Oauth\Exception(
                    __('The nonce/timestamp combination has already been used.'),
                    self::ERR_NONCE_USED);
            }

            $nonceObj->setNonce($nonce)
                ->setConsumerId($consumer->getId())
                ->setTimestamp($timestamp)
                ->save();
        } catch (\Magento\Oauth\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Oauth\Exception(__('An error occurred validating the nonce.'));
        }
    }

    /**
     * Validate 'oauth_verifier' parameter
     *
     * @param string $verifier
     * @param string $verifierFromToken
     * @throws \Magento\Oauth\Exception
     */
    protected function _validateVerifierParam($verifier, $verifierFromToken)
    {
        if (!is_string($verifier)) {
            throw new \Magento\Oauth\Exception('', self::ERR_VERIFIER_INVALID);
        }
        if (strlen($verifier) != \Magento\Oauth\Model\Token::LENGTH_VERIFIER) {
            throw new \Magento\Oauth\Exception('', self::ERR_VERIFIER_INVALID);
        }
        if ($verifierFromToken != $verifier) {
            throw new \Magento\Oauth\Exception('', self::ERR_VERIFIER_INVALID);
        }
    }

    /**
     * Validate signature based on the signature method used
     *
     * @param array $params
     * @param string $consumerSecret
     * @param string $httpMethod
     * @param string $requestUrl
     * @param string $tokenSecret
     * @throws \Magento\Oauth\Exception
     */
    protected function _validateSignature($params, $consumerSecret, $httpMethod, $requestUrl, $tokenSecret = null)
    {
        if (!in_array($params['oauth_signature_method'], self::getSupportedSignatureMethods())) {
            throw new \Magento\Oauth\Exception('', self::ERR_SIGNATURE_METHOD_REJECTED);
        }

        $allowedSignParams = $params;
        //unset unused signature parameters
        unset($allowedSignParams['oauth_signature']);
        unset($allowedSignParams['http_method']);
        unset($allowedSignParams['request_url']);

        $calculatedSign = $this->_httpUtility->sign(
            $allowedSignParams,
            $params['oauth_signature_method'],
            $consumerSecret,
            $tokenSecret,
            $httpMethod,
            $requestUrl
        );

        if ($calculatedSign != $params['oauth_signature']) {
            throw new \Magento\Oauth\Exception(
                'Invalid signature.', self::ERR_SIGNATURE_INVALID);
        }
    }

    /**
     * Validate oauth version
     *
     * @param string $version
     * @throws \Magento\Oauth\Exception
     */
    protected function _validateVersionParam($version)
    {
        // validate version if specified
        if ('1.0' != $version) {
            throw new \Magento\Oauth\Exception('', self::ERR_VERSION_REJECTED);
        }
    }

    /**
     * Validate request and header parameters
     *
     * @param $protocolParams
     * @param $requiredParams
     * @throws \Magento\Oauth\Exception
     */
    protected function _validateProtocolParams($protocolParams, $requiredParams)
    {
        // validate version if specified
        if (isset($protocolParams['oauth_version']) && '1.0' != $protocolParams['oauth_version']) {
            throw new \Magento\Oauth\Exception('', self::ERR_VERSION_REJECTED);
        }
        // required parameters validation. Default to minimum required params if not provided
        if (empty($requiredParams)) {
            $requiredParams = array(
                "oauth_consumer_key",
                "oauth_signature",
                "oauth_signature_method",
                "oauth_nonce",
                "oauth_timestamp"
            );
        }
        $this->_checkRequiredParams($protocolParams, $requiredParams);

        if (isset($protocolParams['oauth_token']) && strlen(
                $protocolParams['oauth_token']
            ) != \Magento\Oauth\Model\Token::LENGTH_TOKEN
        ) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }

        // validate signature method
        if (!in_array($protocolParams['oauth_signature_method'], self::getSupportedSignatureMethods())) {
            throw new \Magento\Oauth\Exception('', self::ERR_SIGNATURE_METHOD_REJECTED);
        }

        $consumer = $this->_getConsumerByKey($protocolParams['oauth_consumer_key']);

        $this->_validateNonce($protocolParams['oauth_nonce'], $consumer->getId(), $protocolParams['oauth_timestamp']);
    }

    /**
     * Get consumer by consumer_id
     *
     * @param $consumerId
     * @return \Magento\Oauth\Model\Consumer
     * @throws \Magento\Oauth\Exception
     */
    protected function _getConsumer($consumerId)
    {
        $consumer = $this->_consumerFactory->create()->load($consumerId);

        if (!$consumer->getId()) {
            throw new \Magento\Oauth\Exception('', self::ERR_PARAMETER_REJECTED);
        }

        return $consumer;
    }

    /**
     * Get a consumer from its key
     *
     * @param string $consumerKey to load
     * @return \Magento\Oauth\Model\Consumer
     * @throws \Magento\Oauth\Exception
     */
    protected function _getConsumerByKey($consumerKey)
    {
        if (strlen($consumerKey) != \Magento\Oauth\Model\Consumer::KEY_LENGTH) {
            throw new \Magento\Oauth\Exception('', self::ERR_CONSUMER_KEY_REJECTED);
        }

        $consumer = $this->_consumerFactory->create()->loadByKey($consumerKey);

        if (!$consumer->getId()) {
            throw new \Magento\Oauth\Exception('', self::ERR_CONSUMER_KEY_REJECTED);
        }

        return $consumer;
    }

    /**
     * Load token object, validate it depending on request type, set access data and save
     *
     * @param string $token
     * @return \Magento\Oauth\Model\Token
     * @throws \Magento\Oauth\Exception
     */
    protected function _getToken($token)
    {
        if (strlen($token) != \Magento\Oauth\Model\Token::LENGTH_TOKEN) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }

        $tokenObj = $this->_tokenFactory->create()->load($token, 'token');

        if (!$tokenObj->getId()) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }

        return $tokenObj;
    }

    /**
     * Load token object given a consumer id
     *
     * @param int $consumerId - The id of the consumer
     * @return \Magento\Oauth\Model\Token
     * @throws \Magento\Oauth\Exception
     */
    protected function _getTokenByConsumer($consumerId)
    {
        $token = $this->_tokenFactory->create()->load($consumerId, 'consumer_id');

        if (!$token->getId()) {
            throw new \Magento\Oauth\Exception('', self::ERR_TOKEN_REJECTED);
        }

        return $token;
    }

    /**
     * Fetch nonce based on a composite key consisting of the nonce string and a consumer id.
     *
     * @param string $nonce - The nonce string
     * @param int $consumerId - A consumer id
     * @return \Magento\Oauth\Model\Nonce
     */
    protected function _getNonce($nonce, $consumerId)
    {
        $nonceObj = $this->_nonceFactory->create()->loadByCompositeKey($nonce, $consumerId);
        return $nonceObj;
    }

    /**
     * Check if token belongs to the same consumer
     *
     * @param $token \Magento\Oauth\Model\Token
     * @param $consumer \Magento\Oauth\Model\Consumer
     * @return boolean
     */
    protected function _isTokenAssociatedToConsumer($token, $consumer)
    {
        return $token->getConsumerId() == $consumer->getId();
    }

    /**
     * Check if mandatory OAuth parameters are present
     *
     * @param $protocolParams
     * @param $requiredParams
     * @return mixed
     * @throws \Magento\Oauth\Exception
     */
    protected function _checkRequiredParams($protocolParams, $requiredParams)
    {
        foreach ($requiredParams as $param) {
            if (!isset($protocolParams[$param])) {
                throw new \Magento\Oauth\Exception($param, self::ERR_PARAMETER_ABSENT);
            }
        }
    }

    /**
     * Get value from store configuration
     *
     * @return int
     */
    protected function _getConfigValue($xpath, $default)
    {
        $value = (int)$this->_storeManager->getStore()->getConfig($xpath);
        return $value > 0 ? $value : $default;
    }
}
