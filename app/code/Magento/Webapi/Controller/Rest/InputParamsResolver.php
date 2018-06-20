<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\UrlDecoder;

/**
 * This class is responsible for retrieving resolved input data
 */
class InputParamsResolver
{
    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @var ParamsOverrider
     */
    private $paramsOverrider;

    /**
     * @var ServiceInputProcessor
     */
    private $serviceInputProcessor;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Route
     */
    private $route;

    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var UrlDecoder
     */
    private $urlDecoder;

    /**
     * @param RestRequest $request
     * @param ParamsOverrider $paramsOverrider
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param Router $router
     * @param RequestValidator $requestValidator
     * @param UrlDecoder $urlDecoder
     */
    public function __construct(
        RestRequest $request,
        ParamsOverrider $paramsOverrider,
        ServiceInputProcessor $serviceInputProcessor,
        Router $router,
        RequestValidator $requestValidator,
        UrlDecoder $urlDecoder = null
    ) {
        $this->request = $request;
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->router = $router;
        $this->requestValidator = $requestValidator;
        $this->urlDecoder = $urlDecoder ?: \Magento\Framework\App\ObjectManager::getInstance()->get(UrlDecoder::class);
    }

    /**
     * Process and resolve input parameters
     *
     * @return array
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function resolve()
    {
        $this->requestValidator->validate();
        $route = $this->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();

        /*
         * Valid only for updates using PUT when passing id value both in URL and body
         */
        if ($this->request->getHttpMethod() == RestRequest::HTTP_METHOD_PUT) {
            $inputData = $this->paramsOverrider->overrideRequestBodyIdWithPathParam(
                $this->request->getParams(),
                $this->request->getBodyParams(),
                $serviceClassName,
                $serviceMethodName
            );
            $inputData = array_merge($inputData, $this->request->getParams());
        } else {
            $inputData = $this->request->getRequestData();
        }

        $inputData = $this->urlDecoder->decodeParams($inputData);
        $inputData = $this->paramsOverrider->override($inputData, $route->getParameters());
        $inputParams = $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);
        return $inputParams;
    }

    /**
     * Retrieve current route.
     *
     * @return Route
     */
    public function getRoute()
    {
        if (!$this->route) {
            $this->route = $this->router->match($this->request);
        }
        return $this->route;
    }
}
