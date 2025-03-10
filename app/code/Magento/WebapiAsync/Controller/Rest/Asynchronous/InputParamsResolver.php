<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Controller\Rest\Asynchronous;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Validator\EntityArrayValidator\InputArraySizeLimitValue;
use Magento\Webapi\Controller\Rest\InputParamsResolver as WebapiInputParamsResolver;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\RequestValidator;
use Magento\Webapi\Controller\Rest\Router;
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
     * @var InputArraySizeLimitValue|null
     */
    private $inputArraySizeLimitValue;

    /**
     * @var MethodsMap
     */
    private $methodsMap;

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
     * @param InputArraySizeLimitValue|null $inputArraySizeLimitValue
     * @param MethodsMap|null $methodsMap
     */
    public function __construct(
        RestRequest $request,
        ParamsOverrider $paramsOverrider,
        ServiceInputProcessor $inputProcessor,
        Router $router,
        RequestValidator $requestValidator,
        WebapiInputParamsResolver $inputParamsResolver,
        bool $isBulk = false,
        ?InputArraySizeLimitValue $inputArraySizeLimitValue = null,
        ?MethodsMap $methodsMap = null
    ) {
        $this->request = $request;
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceInputProcessor = $inputProcessor;
        $this->router = $router;
        $this->requestValidator = $requestValidator;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->isBulk = $isBulk;
        $this->inputArraySizeLimitValue = $inputArraySizeLimitValue ?? ObjectManager::getInstance()
                ->get(InputArraySizeLimitValue::class);
        $this->methodsMap = $methodsMap ?? ObjectManager::getInstance()
            ->get(MethodsMap::class);
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
     * @throws AuthorizationException|LocalizedException
     */
    public function resolve()
    {
        if ($this->isBulk === false) {
            return [$this->inputParamsResolver->resolve()];
        }

        $this->requestValidator->validate();
        $webapiResolvedParams = [];
        $inputData = $this->getInputData();
        $route = $this->getRoute();
        $routeServiceClass = $route->getServiceClass();
        $routeServiceMethod = $route->getServiceMethod();
        $this->inputArraySizeLimitValue->set($route->getInputArraySizeLimit());

        $this->validateParameters($routeServiceClass, $routeServiceMethod, array_keys($route->getParameters()));

        foreach ($inputData as $key => $singleEntityParams) {
            if (!is_array($singleEntityParams)) {
                continue;
            }

            $webapiResolvedParams[$key] = $this->resolveBulkItemParams(
                $singleEntityParams,
                $routeServiceClass,
                $routeServiceMethod
            );
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
        if ($httpMethod === RestRequest::HTTP_METHOD_DELETE) {
            $requestBodyParams = $this->request->getBodyParams();
            $inputData = array_merge($requestBodyParams, $inputData);
        }

        return array_map(function ($singleEntityParams) {
            if (is_array($singleEntityParams)) {
                $singleEntityParams = $this->filterInputData($singleEntityParams);
                $singleEntityParams = $this->paramsOverrider->override(
                    $singleEntityParams,
                    $this->getRoute()->getParameters()
                );
            }

            return $singleEntityParams;
        }, $inputData);
    }

    /**
     * Returns route.
     *
     * @return Route
     * @throws Exception
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
     * @param string $serviceClass route Service Class
     * @param string $serviceMethod route Service Method
     * @return array list of parameters that can be used to call the service method
     * @throws Exception|LocalizedException
     */
    private function resolveBulkItemParams(array $inputData, string $serviceClass, string $serviceMethod): array
    {
        return $this->serviceInputProcessor->process($serviceClass, $serviceMethod, $inputData);
    }

    /**
     * Validates InputData
     *
     * @param array $inputData
     * @return array
     */
    private function filterInputData(array $inputData): array
    {
        $result = [];

        $data = array_filter($inputData, function ($k) use (&$result) {
            $key = is_string($k) ? strtolower(str_replace('_', "", $k)) : $k;
            return !isset($result[$key]) && ($result[$key] = true);
        }, ARRAY_FILTER_USE_KEY);

        return array_map(function ($value) {
            return is_array($value) ? $this->filterInputData($value) : $value;
        }, $data);
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
            $arrayKeys = explode('.', $param ?? '');
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
             $message = 'The current request does not expect the next parameters: '
                 . implode(', ', $paramOverriders);
             throw new \UnexpectedValueException(__($message)->__toString());
        }
    }
}
