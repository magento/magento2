<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Oauth;

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Laminas\OAuth\Http\Utility;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Phrase;
use Magento\Framework\Oauth\Exception as AuthException;
use Magento\Framework\App\RequestInterface as AppRequest;

/**
 * Authorization service.
 */
class Oauth implements OauthInterface
{
    /**
     * @var  \Magento\Framework\Oauth\Helper\Oauth
     */
    protected $_oauthHelper;

    /**
     * @var  Utility
     */
    protected $_httpUtility;

    /**
     * @var \Magento\Framework\Oauth\NonceGeneratorInterface
     */
    protected $_nonceGenerator;

    /**
     * @var \Magento\Framework\Oauth\TokenProviderInterface
     */
    protected $_tokenProvider;

    /**
     * @var AppRequest
     */
    private AppRequest $request;

    /**
     * @var Oauth1|null
     */
    private ?Oauth1 $oauth1Helper = null;

    /**
     * @param Helper\Oauth $oauthHelper
     * @param NonceGeneratorInterface $nonceGenerator
     * @param TokenProviderInterface $tokenProvider
     * @param AppRequest $request
     * @param Utility|null $httpUtility
     */
    public function __construct(
        Helper\Oauth $oauthHelper,
        NonceGeneratorInterface $nonceGenerator,
        TokenProviderInterface $tokenProvider,
        AppRequest $request,
        Utility $httpUtility = null
    ) {
        $this->_oauthHelper = $oauthHelper;
        $this->_nonceGenerator = $nonceGenerator;
        $this->_tokenProvider = $tokenProvider;
        $this->request = $request;
        // null default to prevent ObjectManagerFactory from injecting, see MAGETWO-30809
        $this->_httpUtility = $httpUtility ?: new Utility();
    }

    /**
     * Retrieve array of supported signature methods.
     *
     * @return string[]
     */
    public static function getSupportedSignatureMethods()
    {
        return [self::SIGNATURE_SHA256];
    }

    /**
     * @inheritdoc
     */
    public function getRequestToken($params, $requestUrl, $httpMethod = 'POST')
    {
        $this->_validateProtocolParams($params);
        $consumer = $this->_tokenProvider->getConsumerByKey($params['oauth_consumer_key']);
        $this->_tokenProvider->validateConsumer($consumer);
        $this->_validateSignature($params, $consumer->getSecret(), $httpMethod, $requestUrl);

        return $this->_tokenProvider->createRequestToken($consumer);
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken($params, $requestUrl, $httpMethod = 'POST')
    {
        $required = [
            'oauth_consumer_key',
            'oauth_signature',
            'oauth_signature_method',
            'oauth_nonce',
            'oauth_timestamp',
            'oauth_token',
            'oauth_verifier',
        ];

        $this->_validateProtocolParams($params, $required);
        $consumer = $this->_tokenProvider->getConsumerByKey($params['oauth_consumer_key']);
        $tokenSecret = $this->_tokenProvider->validateRequestToken(
            $params['oauth_token'],
            $consumer,
            $params['oauth_verifier']
        );

        $this->_validateSignature($params, $consumer->getSecret(), $httpMethod, $requestUrl, $tokenSecret);

        return $this->_tokenProvider->getAccessToken($consumer);
    }

    /**
     * @inheritdoc
     */
    public function validateAccessTokenRequest($params, $requestUrl, $httpMethod = 'POST')
    {
        $required = [
            'oauth_consumer_key',
            'oauth_signature',
            'oauth_signature_method',
            'oauth_nonce',
            'oauth_timestamp',
            'oauth_token',
        ];

        $this->_validateProtocolParams($params, $required);
        $consumer = $this->_tokenProvider->getConsumerByKey($params['oauth_consumer_key']);
        $tokenSecret = $this->_tokenProvider->validateAccessTokenRequest($params['oauth_token'], $consumer);

        $this->_validateSignature($params, $consumer->getSecret(), $httpMethod, $requestUrl, $tokenSecret);

        return $consumer->getId();
    }

    /**
     * @inheritdoc
     */
    public function validateAccessToken($accessToken)
    {
        return $this->_tokenProvider->validateAccessToken($accessToken);
    }

    /**
     * @inheritdoc
     */
    public function buildAuthorizationHeader(
        $params,
        $requestUrl,
        $signatureMethod = self::SIGNATURE_SHA256,
        $httpMethod = 'POST'
    ) {
        $required = ["oauth_consumer_key", "oauth_consumer_secret", "oauth_token", "oauth_token_secret"];
        $this->_checkRequiredParams($params, $required);
        $consumer = $this->_tokenProvider->getConsumerByKey($params['oauth_consumer_key']);
        $headerParameters = [
            'oauth_nonce' => $this->_nonceGenerator->generateNonce($consumer),
            'oauth_timestamp' => $this->_nonceGenerator->generateTimestamp(),
            'oauth_version' => '1.0',
        ];
        $headerParameters = array_merge($headerParameters, $params);
        $headerParameters['oauth_signature'] = $this->_httpUtility->sign(
            $params,
            $signatureMethod,
            $headerParameters['oauth_consumer_secret'],
            $headerParameters['oauth_token_secret'],
            $httpMethod,
            $requestUrl
        );
        $authorizationHeader = $this->_httpUtility->toAuthorizationHeader($headerParameters);
        // toAuthorizationHeader adds an optional realm="" which is not required for now.
        // http://tools.ietf.org/html/rfc2617#section-1.2
        return str_replace('realm="",', '', $authorizationHeader);
    }

    /**
     * Validate signature based on the signature method used.
     *
     * @param array $params
     * @param string $consumerSecret
     * @param string $httpMethod
     * @param string $requestUrl
     * @param string $tokenSecret
     * @return void
     * @throws Exception|OauthInputException
     */
    protected function _validateSignature($params, $consumerSecret, $httpMethod, $requestUrl, $tokenSecret = null)
    {
        if (!in_array($params['oauth_signature_method'], self::getSupportedSignatureMethods())) {
            throw new OauthInputException(
                new Phrase(
                    'Signature method %1 is not supported',
                    [$params['oauth_signature_method']]
                )
            );
        }

        $allowedSignParams = $params;
        unset($allowedSignParams['oauth_signature']);

        $calculatedSign = $this->_httpUtility->sign(
            $allowedSignParams,
            $params['oauth_signature_method'],
            $consumerSecret,
            $tokenSecret,
            $httpMethod,
            $requestUrl
        );

        $calculatedSign2 = $this->getOauthHelper(
            [
                'consumer_key'    => $params['oauth_consumer_key'],
                'consumer_secret' => $consumerSecret,
                'token'           => $params['oauth_token'],
                'token_secret'    => $tokenSecret
            ]
        )->getSignature($this->getRequestFromArray(
            [
                'method' => $httpMethod,
                'uri' => $requestUrl,
                'headers' => $this->request->getHeaders()->toArray(),
                'body' => $httpMethod == 'GET' ? null : json_encode($this->request->getParams()) //this does not cover all cases request type
            ]
        ), $params);

        if (!Security::compareStrings($calculatedSign2, $params['oauth_signature'])) {
            throw new AuthException(new Phrase('The signature is invalid. Verify and try again.'));
        }
    }

    /**
     * Validate oauth version.
     *
     * @param string $version
     * @return void
     * @throws OauthInputException
     */
    protected function _validateVersionParam($version)
    {
        // validate version if specified
        if ('1.0' != $version) {
            throw new OauthInputException(new Phrase('The "%1" Oauth version isn\'t supported.', [$version]));
        }
    }

    /**
     * Validate request and header parameters.
     *
     * @param array $protocolParams
     * @param array $requiredParams
     * @return void
     * @throws OauthInputException
     */
    protected function _validateProtocolParams($protocolParams, $requiredParams = [])
    {
        // validate version if specified.
        if (isset($protocolParams['oauth_version'])) {
            $this->_validateVersionParam($protocolParams['oauth_version']);
        }

        // Required parameters validation. Default to minimum required params if not provided.
        if (empty($requiredParams)) {
            $requiredParams = [
                "oauth_consumer_key",
                "oauth_signature",
                "oauth_signature_method",
                "oauth_nonce",
                "oauth_timestamp",
            ];
        }
        $this->_checkRequiredParams($protocolParams, $requiredParams);

        if (isset($protocolParams['oauth_token'])
            && !$this->_tokenProvider->validateOauthToken($protocolParams['oauth_token'])) {
            throw new OauthInputException(new Phrase('The token length is invalid. Check the length and try again.'));
        }

        // Validate signature method.
        if (!in_array($protocolParams['oauth_signature_method'], self::getSupportedSignatureMethods())) {
            throw new OauthInputException(
                new Phrase(
                    'Signature method %1 is not supported',
                    [$protocolParams['oauth_signature_method']]
                )
            );
        }

        $consumer = $this->_tokenProvider->getConsumerByKey($protocolParams['oauth_consumer_key']);
        $this->_nonceGenerator->validateNonce(
            $consumer,
            $protocolParams['oauth_nonce'],
            $protocolParams['oauth_timestamp']
        );
    }

    /**
     * Check if mandatory OAuth parameters are present.
     *
     * @param array $protocolParams
     * @param array $requiredParams
     * @return void
     * @throws OauthInputException
     */
    protected function _checkRequiredParams($protocolParams, $requiredParams)
    {
        $exception = new OauthInputException();
        foreach ($requiredParams as $param) {
            if (!isset($protocolParams[$param])) {
                $exception->addError(
                    new Phrase('"%fieldName" is required. Enter and try again.', ['fieldName' => $param])
                );
            }
        }
        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * @param array $params
     * @return Oauth1
     */
    private function getOauthHelper(array $params): Oauth1
    {
        if (!$this->oauth1Helper) {
            $this->oauth1Helper = new Oauth1($params);
        }

        return $this->oauth1Helper;
    }

    /**
     * @param array $data
     * @return RequestInterface
     */
    private function getRequestFromArray(array $data): RequestInterface
    {
        $method = $data['method'] ?? 'GET';
        $uri = $data['uri'] ?? '';
        $headers = $data['headers'] ?? [];
        $body = $data['body'] ?? '';

        return new Request($method, $uri, $headers, $body);
    }
}
