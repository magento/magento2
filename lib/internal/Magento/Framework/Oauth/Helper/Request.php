<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth\Helper;

use Magento\Framework\App\RequestInterface;

/**
 * Class \Magento\Framework\Oauth\Helper\Request
 *
 */
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
     * Process HTTP request object and prepare for token validation
     *
     * @param RequestInterface $httpRequest
     * @return array
     */
    public function prepareRequest($httpRequest)
    {
        $oauthParams = $this->_processRequest(
            $httpRequest->getHeader('Authorization'),
            $httpRequest->getHeader(\Zend_Http_Client::CONTENT_TYPE),
            $httpRequest->getContent(),
            $this->getRequestUrl($httpRequest)
        );
        return $oauthParams;
    }

    /**
     * Compute the request Url from the Http request
     *
     * @param RequestInterface $httpRequest
     * @return string
     */
    public function getRequestUrl($httpRequest)
    {
        return $httpRequest->getScheme() . '://' . $httpRequest->getHttpHost(false) . $httpRequest->getRequestUri();
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
        $protocolParams = [];

        if (!$this->_processHeader($authHeaderValue, $protocolParams)) {
            return [];
        }

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
     * @return bool true if parameters from oauth headers are processed correctly
     */
    protected function _processHeader($authHeaderValue, &$protocolParams)
    {
        $oauthValuePosition = stripos(($authHeaderValue ? $authHeaderValue : ''), 'oauth ');
        if ($authHeaderValue && $oauthValuePosition !== false) {
            // Ignore anything before and including 'OAuth ' (trailing values validated later)
            $authHeaderValue = substr($authHeaderValue, $oauthValuePosition + 6);
            foreach (explode(',', $authHeaderValue) as $paramStr) {
                $nameAndValue = explode('=', trim($paramStr), 2);

                if (count($nameAndValue) < 2) {
                    continue;
                }
                if ($this->_isProtocolParameter($nameAndValue[0])) {
                    $protocolParams[rawurldecode($nameAndValue[0])] = rawurldecode(trim($nameAndValue[1], '"'));
                }
            }
            return true;
        }
        return false;
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
     * @param \Magento\Framework\HTTP\PhpEnvironment\Response $response OPTIONAL If NULL - will use internal getter
     * @return array
     */
    public function prepareErrorResponse(
        \Exception $exception,
        \Magento\Framework\HTTP\PhpEnvironment\Response $response = null
    ) {
        $errorMsg = $exception->getMessage();

        if ($exception instanceof \Magento\Framework\Oauth\Exception) {
            $responseCode = self::HTTP_UNAUTHORIZED;
        } elseif ($exception instanceof \Magento\Framework\Oauth\OauthInputException) {
            $responseCode = self::HTTP_BAD_REQUEST;
            if ($errorMsg == 'One or more input exceptions have occurred.') {
                $errorMsg = $exception->getAggregatedErrorMessage();
            }
        } else {
            $errorMsg = 'internal_error&message=' . ($errorMsg ? $errorMsg : 'empty_message');
            $responseCode = self::HTTP_INTERNAL_ERROR;
        }

        $response->setHttpResponseCode($responseCode);
        return ['oauth_problem' => $errorMsg];
    }
}
