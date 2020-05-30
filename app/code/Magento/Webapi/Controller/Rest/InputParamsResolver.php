<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Router\Route;

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
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param ParamsOverrider $paramsOverrider
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param Router $router
     * @param RequestValidator $requestValidator
     */
    public function __construct(
        RestRequest $request,
        ParamsOverrider $paramsOverrider,
        ServiceInputProcessor $serviceInputProcessor,
        Router $router,
        RequestValidator $requestValidator
    ) {
        $this->request = $request;
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->router = $router;
        $this->requestValidator = $requestValidator;
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
        $inputData = $this->getInputData();
        return $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);
    }

    /**
     * Get API input data
     *
     * @return array
     */
    public function getInputData()
    {
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

        return $this->paramsOverrider->override($inputData, $route->getParameters());
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
