<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Controller\Rest\Asynchronous;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\RequestValidator;
use Magento\Webapi\Controller\Rest\InputParamsResolver as WebapiInputParamsResolver;

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
     * @var \Magento\Webapi\Controller\Rest\InputParamsResolver
     */
    private $inputParamsResolver;
    /**
     * @var bool
     */
    private $isBulk;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \Magento\Webapi\Controller\Rest\ParamsOverrider $paramsOverrider
     * @param \Magento\Framework\Webapi\ServiceInputProcessor $inputProcessor
     * @param \Magento\Webapi\Controller\Rest\Router $router
     * @param \Magento\Webapi\Controller\Rest\RequestValidator $requestValidator
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
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
     * Return array with validated input params
     * or throw \Exception if at least one request entity params is not valid
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException if no value is provided for required parameters
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function resolve()
    {
        if ($this->isBulk === false) {
            return [$this->inputParamsResolver->resolve()];
        }
        $this->requestValidator->validate();
        $webapiResolvedParams = [];
        $inputData = $this->request->getRequestData();

        $httpMethod = $this->request->getHttpMethod();
        if ($httpMethod == \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE) {
            $requestBodyParams = $this->request->getBodyParams();
            $inputData = array_merge($requestBodyParams, $inputData);
        }

        foreach ($inputData as $key => $singleEntityParams) {
            $webapiResolvedParams[$key] = $this->resolveBulkItemParams($singleEntityParams);
        }

        return $webapiResolvedParams;
    }

    /**
     * Returns route.
     *
     * @return \Magento\Webapi\Controller\Rest\Router\Route
     */
    public function getRoute()
    {
        return $this->inputParamsResolver->getRoute();
    }

    /**
     * Convert the input array from key-value format to a list of parameters suitable for the specified class / method.
     *
     * Instead of \Magento\Webapi\Controller\Rest\InputParamsResolver
     * we don't need to merge body params with url params and use only body params
     *
     * @param array $inputData data to send to method in key-value format
     * @return array list of parameters that can be used to call the service method
     * @throws \Magento\Framework\Exception\InputException if no value is provided for required parameters
     * @throws \Magento\Framework\Webapi\Exception
     */
    private function resolveBulkItemParams($inputData)
    {
        $route = $this->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();
        $inputParams = $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);

        return $inputParams;
    }
}
