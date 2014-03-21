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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Oauth\Helper;

use Magento\Oauth\OauthInterface;

class Request
{
    /**#@+
     * HTTP Response Codes
     */
    const HTTP_OK = 200;

    const HTTP_BAD_REQUEST = 400;

    const HTTP_UNAUTHORIZED = 401;

    const HTTP_METHOD_NOT_ALLOWED = 405;

    const HTTP_INTERNAL_ERROR = 500;

    /**#@-*/

    /**
     * Error code to error messages pairs
     *
     * @var array
     */
    protected $_errors = array(
        OauthInterface::ERR_VERSION_REJECTED => 'version_rejected',
        OauthInterface::ERR_PARAMETER_ABSENT => 'parameter_absent',
        OauthInterface::ERR_PARAMETER_REJECTED => 'parameter_rejected',
        OauthInterface::ERR_TIMESTAMP_REFUSED => 'timestamp_refused',
        OauthInterface::ERR_NONCE_USED => 'nonce_used',
        OauthInterface::ERR_SIGNATURE_METHOD_REJECTED => 'signature_method_rejected',
        OauthInterface::ERR_SIGNATURE_INVALID => 'signature_invalid',
        OauthInterface::ERR_CONSUMER_KEY_REJECTED => 'consumer_key_rejected',
        OauthInterface::ERR_CONSUMER_KEY_INVALID => 'consumer_key_invalid',
        OauthInterface::ERR_TOKEN_USED => 'token_used',
        OauthInterface::ERR_TOKEN_EXPIRED => 'token_expired',
        OauthInterface::ERR_TOKEN_REVOKED => 'token_revoked',
        OauthInterface::ERR_TOKEN_REJECTED => 'token_rejected',
        OauthInterface::ERR_VERIFIER_INVALID => 'verifier_invalid',
        OauthInterface::ERR_PERMISSION_UNKNOWN => 'permission_unknown',
        OauthInterface::ERR_PERMISSION_DENIED => 'permission_denied',
        OauthInterface::ERR_METHOD_NOT_ALLOWED => 'method_not_allowed'
    );

    /**
     * Error code to HTTP error code
     *
     * @var array
     */
    protected $_errorsToHttpCode = array(
        OauthInterface::ERR_VERSION_REJECTED => self::HTTP_BAD_REQUEST,
        OauthInterface::ERR_PARAMETER_ABSENT => self::HTTP_BAD_REQUEST,
        OauthInterface::ERR_PARAMETER_REJECTED => self::HTTP_BAD_REQUEST,
        OauthInterface::ERR_TIMESTAMP_REFUSED => self::HTTP_BAD_REQUEST,
        OauthInterface::ERR_NONCE_USED => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_SIGNATURE_METHOD_REJECTED => self::HTTP_BAD_REQUEST,
        OauthInterface::ERR_SIGNATURE_INVALID => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_CONSUMER_KEY_REJECTED => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_CONSUMER_KEY_INVALID => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_TOKEN_USED => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_TOKEN_EXPIRED => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_TOKEN_REVOKED => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_TOKEN_REJECTED => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_VERIFIER_INVALID => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_PERMISSION_UNKNOWN => self::HTTP_UNAUTHORIZED,
        OauthInterface::ERR_PERMISSION_DENIED => self::HTTP_UNAUTHORIZED
    );

    /**
     * Process HTTP request object and prepare for token validation
     *
     * @param \Zend_Controller_Request_Http $httpRequest
     * @return array
     */
    public function prepareRequest($httpRequest)
    {
        $oauthParams = $this->_processRequest(
            $httpRequest->getHeader('Authorization'),
            $httpRequest->getHeader(\Zend_Http_Client::CONTENT_TYPE),
            $httpRequest->getRawBody(),
            $this->getRequestUrl($httpRequest)
        );
        return $oauthParams;
    }

    /**
     * Compute the request Url from the Http request
     *
     * @param \Zend_Controller_Request_Http $httpRequest
     * @return string
     */
    public function getRequestUrl($httpRequest)
    {
        // TODO: Fix needed for $this->getRequest()->getHttpHost(). Hosts with port are not covered.
        return $httpRequest->getScheme() . '://' . $httpRequest->getHttpHost() . $httpRequest->getRequestUri();
    }

    /**
     * Process oauth related protocol information and return as an array
     *
     * @param string $authHeaderValue
     * @param string $contentTypeHeader
     * @param string $requestBodyString
     * @param string $requestUrl
     * @return array
     * merged array of oauth protocols and request parameters. eg :
     * <pre>
     * array (
     *         'oauth_version' => '1.0',
     *         'oauth_signature_method' => 'HMAC-SHA1',
     *         'oauth_nonce' => 'rI7PSWxTZRHWU3R',
     *         'oauth_timestamp' => '1377183099',
     *         'oauth_consumer_key' => 'a6aa81cc3e65e2960a4879392445e718',
     *         'oauth_signature' => 'VNg4mhFlXk7%2FvsxMqqUd5DWIj9s%3D'
     * )
     * </pre>
     */
    protected function _processRequest($authHeaderValue, $contentTypeHeader, $requestBodyString, $requestUrl)
    {
        $protocolParams = array();

        $this->_processHeader($authHeaderValue, $protocolParams);

        if ($contentTypeHeader && 0 === strpos($contentTypeHeader, \Zend_Http_Client::ENC_URLENCODED)) {
            $protocolParamsNotSet = !$protocolParams;

            parse_str($requestBodyString, $protocolBodyParams);

            foreach ($protocolBodyParams as $bodyParamName => $bodyParamValue) {
                if (!$this->_isProtocolParameter($bodyParamName)) {
                    $protocolParams[$bodyParamName] = $bodyParamValue;
                } elseif ($protocolParamsNotSet) {
                    $protocolParams[$bodyParamName] = $bodyParamValue;
                }
            }
        }
        $protocolParamsNotSet = !$protocolParams;

        $queryString = \Zend_Uri_Http::fromString($requestUrl)->getQuery();
        $this->_extractQueryStringParams($protocolParams, $queryString);

        if ($protocolParamsNotSet) {
            $this->_fetchProtocolParamsFromQuery($protocolParams, $queryString);
        }

        // Combine request and header parameters
        return $protocolParams;
    }

    /**
     * Retrieve protocol parameters from query string
     *
     * @param array &$protocolParams
     * @param array $queryString
     * @return void
     */
    protected function _fetchProtocolParamsFromQuery(&$protocolParams, $queryString)
    {
        if (is_array($queryString)) {
            foreach ($queryString as $queryParamName => $queryParamValue) {
                if ($this->_isProtocolParameter($queryParamName)) {
                    $protocolParams[$queryParamName] = $queryParamValue;
                }
            }
        }
    }

    /**
     * Check if attribute is oAuth related
     *
     * @param string $attrName
     * @return bool
     */
    protected function _isProtocolParameter($attrName)
    {
        return (bool)preg_match('/oauth_[a-z_-]+/', $attrName);
    }

    /**
     * Process header parameters for Oauth
     *
     * @param string $authHeaderValue
     * @param array &$protocolParams
     * @return void
     */
    protected function _processHeader($authHeaderValue, &$protocolParams)
    {
        if ($authHeaderValue && 'oauth' === strtolower(substr($authHeaderValue, 0, 5))) {
            $authHeaderValue = substr($authHeaderValue, 6);
            // ignore 'OAuth ' at the beginning

            foreach (explode(',', $authHeaderValue) as $paramStr) {
                $nameAndValue = explode('=', trim($paramStr), 2);

                if (count($nameAndValue) < 2) {
                    continue;
                }
                if ($this->_isProtocolParameter($nameAndValue[0])) {
                    $protocolParams[rawurldecode($nameAndValue[0])] = rawurldecode(trim($nameAndValue[1], '"'));
                }
            }
        }
    }

    /**
     * Process query string for Oauth
     *
     * @param array &$protocolParams
     * @param string $queryString
     * @return void
     */
    protected function _extractQueryStringParams(&$protocolParams, $queryString)
    {
        if ($queryString) {
            foreach (explode('&', $queryString) as $paramToValue) {
                $paramData = explode('=', $paramToValue);

                if (2 === count($paramData) && !$this->_isProtocolParameter($paramData[0])) {
                    $protocolParams[rawurldecode($paramData[0])] = rawurldecode($paramData[1]);
                }
            }
        }
    }

    /**
     * Create response string for problem during request and set HTTP error code
     *
     * @param \Exception $exception
     * @param \Zend_Controller_Response_Http $response OPTIONAL If NULL - will use internal getter
     * @return array
     */
    public function prepareErrorResponse(\Exception $exception, \Zend_Controller_Response_Http $response = null)
    {
        $errorMap = $this->_errors;
        $errorsToHttpCode = $this->_errorsToHttpCode;

        $eMsg = $exception->getMessage();

        if ($exception instanceof \Magento\Oauth\Exception) {
            $eCode = $exception->getCode();

            if (isset($errorMap[$eCode])) {
                $errorMsg = $errorMap[$eCode];
                $responseCode = $errorsToHttpCode[$eCode];
            } else {
                $errorMsg = 'unknown_problem&code=' . $eCode;
                $responseCode = self::HTTP_INTERNAL_ERROR;
            }
            if (OauthInterface::ERR_PARAMETER_ABSENT == $eCode) {
                $errorMsg .= '&oauth_parameters_absent=' . $eMsg;
            } elseif ($eMsg) {
                $errorMsg .= '&message=' . $eMsg;
            }
        } else {
            $errorMsg = 'internal_error&message=' . ($eMsg ? $eMsg : 'empty_message');
            $responseCode = self::HTTP_INTERNAL_ERROR;
        }

        $response->setHttpResponseCode($responseCode);
        return array('oauth_problem' => $errorMsg);
    }
}
