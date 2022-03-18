<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Router\Route;

/**
 * This class is responsible for retrieving resolved input data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @param RestRequest $request
     * @param ParamsOverrider $paramsOverrider
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param Router $router
     * @param RequestValidator $requestValidator
     * @param MethodsMap|null $methodsMap
     */
    public function __construct(
        RestRequest $request,
        ParamsOverrider $paramsOverrider,
        ServiceInputProcessor $serviceInputProcessor,
        Router $router,
        RequestValidator $requestValidator,
        MethodsMap $methodsMap = null
    ) {
        $this->request = $request;
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->router = $router;
        $this->requestValidator = $requestValidator;
        $this->methodsMap = $methodsMap ?: ObjectManager::getInstance()
            ->get(MethodsMap::class);
    }

    /**
     * Process and resolve input parameters
     *
     * @return array
     * @throws Exception
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
        $this->validateParameters($serviceClassName, $serviceMethodName, array_keys($route->getParameters()));

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

    /**
     * Validate that parameters are really used in the current request.
     *
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @param array $paramOverriders
     */
    private function validateParameters(
        string $serviceClassName,
        string $serviceMethodName,
        array $paramOverriders
    ): void {
        $methodParams = $this->methodsMap->getMethodParams($serviceClassName, $serviceMethodName);
        foreach ($paramOverriders as $key => $param) {
            $arrayKeys = explode('.', $param);
            $value = array_shift($arrayKeys);

            foreach ($methodParams as $serviceMethodParam) {
                $serviceMethodParamName = $serviceMethodParam[MethodsMap::METHOD_META_NAME];
                $serviceMethodType = $serviceMethodParam[MethodsMap::METHOD_META_TYPE];

                $camelCaseValue = SimpleDataObjectConverter::snakeCaseToCamelCase($value);
                if ($serviceMethodParamName === $value || $serviceMethodParamName === $camelCaseValue) {
                    if (count($arrayKeys) > 0) {
                        $camelCaseKey = SimpleDataObjectConverter::snakeCaseToCamelCase('set_' . $arrayKeys[0]);
                        $this->validateParameters($serviceMethodType, $camelCaseKey, [implode('.', $arrayKeys)]);
                    }
                    unset($paramOverriders[$key]);
                    break;
                }
            }
        }
        if (!empty($paramOverriders)) {
            throw new \UnexpectedValueException(
                __('The current request does not expect the next parameters: ' . implode(', ', $paramOverriders))
            );
        }
    }
}
