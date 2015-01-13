<?php
/**
 * Front controller for WebAPI SOAP area.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Webapi\Model\PathProcessor;

/**
 * TODO: Consider warnings suppression removal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Soap implements \Magento\Framework\App\FrontControllerInterface
{
    /**#@+
     * Content types used for responses processed by SOAP web API.
     */
    const CONTENT_TYPE_SOAP_CALL = 'application/soap+xml';

    const CONTENT_TYPE_WSDL_REQUEST = 'text/xml';

    /**#@-*/

    /**
     * @var \Magento\Webapi\Model\Soap\Server
     */
    protected $_soapServer;

    /**
     * @var \Magento\Webapi\Model\Soap\Wsdl\Generator
     */
    protected $_wsdlGenerator;

    /**
     * @var \Magento\Webapi\Controller\Soap\Request
     */
    protected $_request;

    /**
     * @var Response
     */
    protected $_response;

    /**
     * @var ErrorProcessor
     */
    protected $_errorProcessor;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var PathProcessor
     */
    protected $_pathProcessor;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $areaList;

    /**
     * @param Soap\Request $request
     * @param Response $response
     * @param \Magento\Webapi\Model\Soap\Wsdl\Generator $wsdlGenerator
     * @param \Magento\Webapi\Model\Soap\Server $soapServer
     * @param ErrorProcessor $errorProcessor
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param PathProcessor $pathProcessor
     * @param \Magento\Framework\App\AreaList $areaList
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Webapi\Controller\Soap\Request $request,
        Response $response,
        \Magento\Webapi\Model\Soap\Wsdl\Generator $wsdlGenerator,
        \Magento\Webapi\Model\Soap\Server $soapServer,
        ErrorProcessor $errorProcessor,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        PathProcessor $pathProcessor,
        \Magento\Framework\App\AreaList $areaList
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_wsdlGenerator = $wsdlGenerator;
        $this->_soapServer = $soapServer;
        $this->_errorProcessor = $errorProcessor;
        $this->_appState = $appState;
        $this->_localeResolver = $localeResolver;
        $this->_layout = $layout;
        $this->_pathProcessor = $pathProcessor;
        $this->areaList = $areaList;
    }

    /**
     * Dispatch SOAP request.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $path = $this->_pathProcessor->process($request->getPathInfo());
        $this->_request->setPathInfo($path);
        $this->areaList->getArea($this->_appState->getAreaCode())
            ->load(\Magento\Framework\App\Area::PART_TRANSLATE);
        try {
            if ($this->_isWsdlRequest()) {
                $responseBody = $this->_wsdlGenerator->generate(
                    $this->_request->getRequestedServices(),
                    $this->_soapServer->generateUri()
                );
                $this->_setResponseContentType(self::CONTENT_TYPE_WSDL_REQUEST);
                $this->_setResponseBody($responseBody);
            } else {
                $this->_soapServer->handle();
            }
        } catch (\Exception $e) {
            $this->_prepareErrorResponse($e);
        }
        return $this->_response;
    }

    /**
     * Check if current request is WSDL request. SOAP operation execution request is another type of requests.
     *
     * @return bool
     */
    protected function _isWsdlRequest()
    {
        return $this->_request->getParam(\Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL) !== null;
    }

    /**
     * Parse the Authorization header and return the access token e.g. Authorization: Bearer <access-token>
     *
     * @return string Access token
     * @throws AuthorizationException
     */
    protected function _getAccessToken()
    {
        $headers = array_change_key_case(getallheaders(), CASE_UPPER);
        if (isset($headers['AUTHORIZATION'])) {
            $token = explode(' ', $headers['AUTHORIZATION']);
            if (isset($token[1]) && is_string($token[1])) {
                return $token[1];
            }
            throw new AuthorizationException('Authentication header format is invalid.');
        }
        throw new AuthorizationException('Authentication header is absent.');
    }

    /**
     * Set body and status code to response using information extracted from provided exception.
     *
     * @param \Exception $exception
     * @return void
     */
    protected function _prepareErrorResponse($exception)
    {
        $maskedException = $this->_errorProcessor->maskException($exception);
        if ($this->_isWsdlRequest()) {
            $httpCode = $maskedException->getHttpCode();
            $contentType = self::CONTENT_TYPE_WSDL_REQUEST;
        } else {
            $httpCode = Response::HTTP_OK;
            $contentType = self::CONTENT_TYPE_SOAP_CALL;
        }
        $this->_setResponseContentType($contentType);
        $this->_response->setHttpResponseCode($httpCode);
        $soapFault = new \Magento\Webapi\Model\Soap\Fault(
            $this->_request,
            $this->_soapServer,
            $maskedException,
            $this->_localeResolver,
            $this->_appState
        );
        // TODO: Generate list of available URLs when invalid WSDL URL specified
        $this->_setResponseBody($soapFault->toXml());
    }

    /**
     * Set content type to response object.
     *
     * @param string $contentType
     * @return $this
     */
    protected function _setResponseContentType($contentType = 'text/xml')
    {
        $this->_response->clearHeaders()->setHeader(
            'Content-Type',
            "{$contentType}; charset={$this->_soapServer->getApiCharset()}"
        );
        return $this;
    }

    /**
     * Replace WSDL xml encoding from config, if present, else default to UTF-8 and set it to the response object.
     *
     * @param string $responseBody
     * @return $this
     */
    protected function _setResponseBody($responseBody)
    {
        $this->_response->setBody(
            preg_replace(
                '/<\?xml version="([^\"]+)"([^\>]+)>/i',
                '<?xml version="$1" encoding="' . $this->_soapServer->getApiCharset() . '"?>',
                $responseBody
            )
        );
        return $this;
    }
}
