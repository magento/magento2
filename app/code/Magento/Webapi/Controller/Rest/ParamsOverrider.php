<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;
use Magento\Webapi\Model\Config\Converter;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Override parameter values
 */
class ParamsOverrider
{
    /**
     * @var ParamOverriderInterface[]
     */
    private $paramOverriders;

    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * Initialize dependencies
     *
     * @param ParamOverriderInterface[] $paramOverriders
     */
    public function __construct(
        array $paramOverriders = []
    ) {
        $this->paramOverriders = $paramOverriders;
    }

    /**
     * Override parameter values based on webapi.xml
     *
     * @param array $inputData Incoming data from request
     * @param array $parameters Contains parameters to replace or default
     * @return array Data in same format as $inputData with appropriate parameters added or changed
     */
    public function override(array $inputData, array $parameters)
    {
        foreach ($parameters as $name => $paramData) {
            $arrayKeys = explode('.', $name);
            if ($paramData[Converter::KEY_FORCE] || !$this->isNestedArrayValueSet($inputData, $arrayKeys)) {
                $paramValue = $paramData[Converter::KEY_VALUE];
                if (isset($this->paramOverriders[$paramValue])) {
                    $value = $this->paramOverriders[$paramValue]->getOverriddenValue();
                } else {
                    $value = $paramData[Converter::KEY_VALUE];
                }
                $this->setNestedArrayValue($inputData, $arrayKeys, $value);
            }
        }
        return $inputData;
    }

    /**
     * Determine if a nested array value is set.
     *
     * @param array &$nestedArray
     * @param string[] $arrayKeys
     * @return bool true if array value is set
     */
    protected function isNestedArrayValueSet(&$nestedArray, $arrayKeys)
    {
        $currentArray = &$nestedArray;

        foreach ($arrayKeys as $key) {
            if (!isset($currentArray[$key])) {
                return false;
            }
            $currentArray = &$currentArray[$key];
        }
        return true;
    }

    /**
     * Set a nested array value.
     *
     * @param array &$nestedArray
     * @param string[] $arrayKeys
     * @param string $valueToSet
     * @return void
     */
    protected function setNestedArrayValue(&$nestedArray, $arrayKeys, $valueToSet)
    {
        $currentArray = &$nestedArray;
        $lastKey = array_pop($arrayKeys);

        foreach ($arrayKeys as $key) {
            if (!isset($currentArray[$key])) {
                $currentArray[$key] = [];
            }
            $currentArray = &$currentArray[$key];
        }

        $currentArray[$lastKey] = $valueToSet;
    }

    /**
     * Override request body property value with matching url path parameter value
     *
     * This method assumes that webapi.xml url defines the substitution parameter as camelCase to the actual
     * snake case key described as part of the api contract. example: /:parentId/nestedResource/:entityId.
     * Here :entityId value will be used for overriding 'entity_id' property in the body.
     * Since Webapi framework allows both camelCase and snakeCase, either of them will be substituted for now.
     * If the request body is missing url path parameter as property, it will be added to the body.
     * This method works only requests with scalar properties at top level or properties of single object embedded
     * in the request body.
     * Only the last path parameter value will be substituted from the url in case of multiple parameters.
     *
     * @param array $urlPathParams url path parameters as array
     * @param array $requestBodyParams body parameters as array
     * @param string $serviceClassName name of the service class that we are trying to call
     * @param string $serviceMethodName name of the method that we are trying to call
     * @return array
     */
    public function overrideRequestBodyIdWithPathParam(
        array $urlPathParams,
        array $requestBodyParams,
        $serviceClassName,
        $serviceMethodName
    ) {
        if (empty($urlPathParams)) {
            return $requestBodyParams;
        }
        $pathParamValue = end($urlPathParams);
        // Self apis should not be overridden
        if ($pathParamValue === 'me') {
            return $requestBodyParams;
        }
        $pathParamKey = key($urlPathParams);
        // Check if the request data is a top level object of body
        if (count($requestBodyParams) == 1 && is_array(end($requestBodyParams))) {
            $requestDataKey = key($requestBodyParams);
            if ($this->isPropertyDeclaredInDataObject(
                $serviceClassName,
                $serviceMethodName,
                $requestDataKey,
                $pathParamKey
            )
            ) {
                $this->substituteParameters($requestBodyParams[$requestDataKey], $pathParamKey, $pathParamValue);
            } else {
                $this->substituteParameters($requestBodyParams, $pathParamKey, $pathParamValue);
            }
        } else { // Else parameters passed as scalar values in body will be overridden
            $this->substituteParameters($requestBodyParams, $pathParamKey, $pathParamValue);
        }
        return $requestBodyParams;
    }

    /**
     * Check presence for both camelCase and snake_case keys in array and substitute if either is present
     *
     * @param array $requestData
     * @param string $key
     * @param string $value
     * @return void
     */
    private function substituteParameters(array &$requestData, $key, $value)
    {
        $snakeCaseKey = SimpleDataObjectConverter::camelCaseToSnakeCase($key);
        $camelCaseKey = SimpleDataObjectConverter::snakeCaseToCamelCase($key);

        if (isset($requestData[$camelCaseKey])) {
            $requestData[$camelCaseKey] = $value;
        } else {
            $requestData[$snakeCaseKey] = $value;
        }
    }

    /**
     * Verify property in parameter's object
     *
     * @param string $serviceClassName name of the service class that we are trying to call
     * @param string $serviceMethodName name of the method that we are trying to call
     * @param string $serviceMethodParamName
     * @param string $objectProperty
     * @return bool
     */
    private function isPropertyDeclaredInDataObject(
        $serviceClassName,
        $serviceMethodName,
        $serviceMethodParamName,
        $objectProperty
    ) {
        if ($serviceClassName && $serviceMethodName) {
            $methodParams = $this->getMethodsMap()->getMethodParams($serviceClassName, $serviceMethodName);
            $index = array_search($serviceMethodParamName, array_column($methodParams, 'name'));
            if ($index !== false) {
                $paramObjectType = $methodParams[$index][MethodsMap::METHOD_META_TYPE];
                $setter = 'set' . ucfirst(SimpleDataObjectConverter::snakeCaseToCamelCase($objectProperty));
                if (array_key_exists(
                    $setter,
                    $this->getMethodsMap()->getMethodsMap($paramObjectType)
                )) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * The getter function to get MethodsMap object
     *
     * @return \Magento\Framework\Reflection\MethodsMap
     *
     * @deprecated
     */
    private function getMethodsMap()
    {
        if ($this->methodsMap === null) {
            $this->methodsMap = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(MethodsMap::class);
        }
        return $this->methodsMap;
    }
}
