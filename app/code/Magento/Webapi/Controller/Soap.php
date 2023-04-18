<?php
/**
 * Front controller for WebAPI SOAP area.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Webapi\Response;
use Magento\Framework\Webapi\Rest\Response\RendererFactory;
use Magento\Webapi\Model\Soap\Fault;
use Magento\Webapi\Model\Soap\Server;
use Magento\Webapi\Model\Soap\Wsdl\Generator;

/**
 *
 * SOAP Web API entry point.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Soap implements FrontControllerInterface
{
    /**#@+
     * Content types used for responses processed by SOAP web API.
     */
    const CONTENT_TYPE_SOAP_CALL = 'application/soap+xml';

    const CONTENT_TYPE_WSDL_REQUEST = 'text/xml';

    /**#@-*/

    /**#@-*/
    protected $_soapServer;

    /**
     * @var Generator
     */
    protected $_wsdlGenerator;

    /**
     * @var Request
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
     * @var State
     */
    protected $_appState;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var PathProcessor
     */
    protected $_pathProcessor;

    /**
     * @param Request $request
     * @param Response $response
     * @param Generator $wsdlGenerator
     * @param Server $soapServer
     * @param ErrorProcessor $errorProcessor
     * @param State $appState
     * @param ResolverInterface $localeResolver
     * @param PathProcessor $pathProcessor
     * @param RendererFactory $rendererFactory
     * @param AreaList $areaList
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Request $request,
        Response $response,
        Generator $wsdlGenerator,
        Server $soapServer,
        ErrorProcessor $errorProcessor,
        State $appState,
        ResolverInterface $localeResolver,
        PathProcessor $pathProcessor,
        protected readonly RendererFactory $rendererFactory,
        protected readonly AreaList $areaList
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_wsdlGenerator = $wsdlGenerator;
        $this->_soapServer = $soapServer;
        $this->_errorProcessor = $errorProcessor;
        $this->_appState = $appState;
        $this->_localeResolver = $localeResolver;
        $this->_pathProcessor = $pathProcessor;
    }

    /**
     * Dispatch SOAP request.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $path = $this->_pathProcessor->process($request->getPathInfo());
        $this->_request->setPathInfo($path);
        $this->areaList->getArea($this->_appState->getAreaCode())->load(Area::PART_TRANSLATE);
        try {
            if ($this->_isWsdlRequest()) {
                $this->validateWsdlRequest();
                $responseBody = $this->_wsdlGenerator->generate(
                    $this->_request->getRequestedServices(),
                    $this->_request->getScheme(),
                    $this->_request->getHttpHost(),
                    $this->_soapServer->generateUri()
                );
                $this->_setResponseContentType(self::CONTENT_TYPE_WSDL_REQUEST);
                $this->_setResponseBody($responseBody);
            } elseif ($this->_isWsdlListRequest()) {
                $servicesList = [];
                foreach ($this->_wsdlGenerator->getListOfServices() as $serviceName) {
                    $servicesList[$serviceName]['wsdl_endpoint'] = $this->_soapServer->getEndpointUri()
                        . '?' . Server::REQUEST_PARAM_WSDL . '&services=' . $serviceName;
                }
                $renderer = $this->rendererFactory->get();
                $this->_setResponseContentType($renderer->getMimeType());
                $this->_setResponseBody($renderer->render($servicesList));
            } else {
                $this->_soapServer->handle();
            }
        } catch (Exception $e) {
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
        return $this->_request->getParam(Server::REQUEST_PARAM_WSDL) !== null;
    }

    /**
     * Check if current request is WSDL request. SOAP operation execution request is another type of requests.
     *
     * @return bool
     */
    protected function _isWsdlListRequest()
    {
        return $this->_request->getParam(Server::REQUEST_PARAM_LIST_WSDL) !== null;
    }

    /**
     * Set body and status code to response using information extracted from provided exception.
     *
     * @param Exception $exception
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
        $soapFault = new Fault(
            $this->_request,
            $this->_soapServer,
            $maskedException,
            $this->_localeResolver,
            $this->_appState
        );
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

    /**
     * Validate wsdl request
     *
     * @return void
     * @throws WebapiException
     */
    protected function validateWsdlRequest()
    {
        $wsdlParam = Server::REQUEST_PARAM_WSDL;
        $servicesParam = Request::REQUEST_PARAM_SERVICES;
        $requestParams = array_keys($this->_request->getParams());
        $allowedParams = [$wsdlParam, $servicesParam];
        $notAllowedParameters = array_diff($requestParams, $allowedParams);
        if (count($notAllowedParameters)) {
            $notAllowed = implode(', ', $notAllowedParameters);
            $message = __(
                'Not allowed parameters: %1. Please use only %2 and %3.',
                $notAllowed,
                $wsdlParam,
                $servicesParam
            );
            throw new WebapiException($message);
        }
    }
}
