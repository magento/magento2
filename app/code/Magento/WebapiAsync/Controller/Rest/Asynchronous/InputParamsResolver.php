<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Controller\Rest\Asynchronous;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Webapi\Controller\Rest\InputParamsResolver as WebapiInputParamsResolver;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\RequestValidator;
use Magento\Webapi\Controller\Rest\Router;
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
     * @var RequestValidator
     */
    private $requestValidator;
    /**
     * @var WebapiInputParamsResolver
     */
    private $inputParamsResolver;
    /**
     * @var bool
     */
    private $isBulk;

    /**
     * Initialize dependencies.
     *
     * @param RestRequest $request
     * @param ParamsOverrider $paramsOverrider
     * @param ServiceInputProcessor $inputProcessor
     * @param Router $router
     * @param RequestValidator $requestValidator
     * @param WebapiInputParamsResolver $inputParamsResolver
     * @param bool $isBulk
     */
    public function __construct(
        RestRequest $request,
        ParamsOverrider $paramsOverrider,
        ServiceInputProcessor $inputProcessor,
        Router $router,
        RequestValidator $requestValidator,
        WebapiInputParamsResolver $inputParamsResolver,
        $isBulk = false
    ) {
        $this->request = $request;
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceInputProcessor = $inputProcessor;
        $this->router = $router;
        $this->requestValidator = $requestValidator;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->isBulk = $isBulk;
    }

    /**
     * Process and resolve input parameters
     *
     * Return array with validated input params
     * or throw \Exception if at least one request entity params is not valid
     *
     * @return array
     * @throws InputException if no value is provided for required parameters
     * @throws Exception
     * @throws AuthorizationException
     */
    public function resolve()
    {
        if ($this->isBulk === false) {
            return [$this->inputParamsResolver->resolve()];
        }
        $this->requestValidator->validate();
        $webapiResolvedParams = [];
        $route = $this->getRoute();

        foreach ($this->getInputData() as $key => $singleEntityParams) {
            $webapiResolvedParams[$key] = $this->resolveBulkItemParams($singleEntityParams, $route);
        }
        return $webapiResolvedParams;
    }

    /**
     * Get API input data
     *
     * @return array
     */
    public function getInputData()
    {
        if ($this->isBulk === false) {
            return [$this->inputParamsResolver->getInputData()];
        }
        $inputData = $this->request->getRequestData();

        $httpMethod = $this->request->getHttpMethod();
        if ($httpMethod == RestRequest::HTTP_METHOD_DELETE) {
            $requestBodyParams = $this->request->getBodyParams();
            $inputData = array_merge($requestBodyParams, $inputData);
        }
        return $inputData;
    }

    /**
     * Returns route.
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->inputParamsResolver->getRoute();
    }

    /**
     * Resolve parameters for service
     *
     * Convert the input array from key-value format to a list of parameters
     * suitable for the specified class / method.
     *
     * Instead of \Magento\Webapi\Controller\Rest\InputParamsResolver
     * we don't need to merge body params with url params and use only body params
     *
     * @param array $inputData data to send to method in key-value format
     * @param Route $route
     * @return array list of parameters that can be used to call the service method
     * @throws Exception
     */
    private function resolveBulkItemParams(array $inputData, Route $route): array
    {
        return $this->serviceInputProcessor->process(
            $route->getServiceClass(),
            $route->getServiceMethod(),
            $inputData,
            $route->getInputArraySizeLimit()
        );
    }
}
