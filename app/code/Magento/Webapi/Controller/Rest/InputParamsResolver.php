<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;

/**
 * This class is responsible for retrieving resolved input data
 * @since 2.1.0
 */
class InputParamsResolver
{
    /**
     * @var RestRequest
     * @since 2.1.0
     */
    private $request;

    /**
     * @var ParamsOverrider
     * @since 2.1.0
     */
    private $paramsOverrider;

    /**
     * @var ServiceInputProcessor
     * @since 2.1.0
     */
    private $serviceInputProcessor;

    /**
     * @var Router
     * @since 2.1.0
     */
    private $router;

    /**
     * @var Route
     * @since 2.1.0
     */
    private $route;

    /**
     * @var RequestValidator
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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

        $inputData = $this->paramsOverrider->override($inputData, $route->getParameters());
        $inputParams = $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);
        return $inputParams;
    }

    /**
     * Retrieve current route.
     *
     * @return Route
     * @since 2.1.0
     */
    public function getRoute()
    {
        if (!$this->route) {
            $this->route = $this->router->match($this->request);
        }
        return $this->route;
    }
}
