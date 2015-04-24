<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Api\Config\Reader as ExtensionAttributesConfigReader;
use Magento\Framework\Api\Config\Converter;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Zend\Code\Reflection\MethodReflection;

/**
 * Processes extension attributes and produces a PHP array for the data.
 */
class ExtensionAttributesProcessor
{
    const IS_METHOD_PREFIX = 'is';
    const HAS_METHOD_PREFIX = 'has';
    const GETTER_PREFIX = 'get';

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ExtensionAttributesConfigReader
     */
    private $configReader;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param AuthorizationInterface $authorization
     * @param ExtensionAttributesConfigReader $configReader
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        AuthorizationInterface $authorization,
        ExtensionAttributesConfigReader $configReader
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->authorization = $authorization;
        $this->configReader = $configReader;
    }

    /**
     * Writes out the extension attributes in an array.
     *
     * @param ExtensionAttributeInterface $dataObject
     * @param string $dataObjectType
     * @return array
     */
    public function buildOutputDataArray(ExtensionAttributesInterface $dataObject, $dataObjectType)
    {
        // TODO: cleanup all of this that's already duplicated in DataObjectProcessor; re-write the serializers

        $methods = $this->dataObjectProcessor->getMethodsMap($dataObjectType);
        $outputData = [];

        /** @var MethodReflection $method */
        foreach ($methods as $methodName => $methodReflectionData) {
            // any method with parameter(s) gets ignored because we do not know the type and value of
            // the parameter(s), so we are not able to process
            if ($methodReflectionData['parameterCount'] > 0) {
                continue;
            }
            $returnType = $methodReflectionData['type'];
            if (substr($methodName, 0, 3) === self::GETTER_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));

                if (!$this->isAttributePermissionValid($dataObjectType, $key)) {
                    $outputData[$key] = null;
                    continue;
                }

                if (is_object($value) && !($value instanceof Phrase)) {
                    $value = $this->dataObjectProcessor->buildOutputDataArray($value, $returnType);
                } elseif (is_array($value)) {
                    $valueResult = [];
                    $arrayElementType = substr($returnType, 0, -2);
                    foreach ($value as $singleValue) {
                        if (is_object($singleValue) && !($singleValue instanceof Phrase)) {
                            $singleValue = $this->dataObjectProcessor->buildOutputDataArray(
                                $singleValue,
                                $arrayElementType
                            );
                        }
                        $valueResult[] = $this->dataObjectProcessor->castValueToType($singleValue, $arrayElementType);
                    }
                    $value = $valueResult;
                }
                $outputData[$key] = $this->dataObjectProcessor->castValueToType($value, $returnType);
            }
        }

        return $outputData;
    }

    /**
     * @param string $dataObjectType
     * @param string $attributeCode
     * @return bool
     */
    private function isAttributePermissionValid($dataObjectType, $attributeCode)
    {
        $typeName = $this->getRegularTypeForExtensionAttributesType($dataObjectType);
        $permissions = $this->getPermissionsForTypeAndMethod($typeName, $attributeCode);
        foreach ($permissions as $permission) {
            if (!$this->authorization->isAllowed($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $name
     * @return string
     */
    private function getRegularTypeForExtensionAttributesType($name)
    {
        return ltrim(str_replace('ExtensionInterface', 'Interface', $name), '\\');
    }

    /**
     * @param string $typeName
     * @param string $attributeCode
     * @return string[] A list of permissions
     */
    private function getPermissionsForTypeAndMethod($typeName, $attributeCode)
    {
        // TODO: Move function to the Config and hope this is cached
        $attributes = $this->configReader->read();
        if (isset($attributes[$typeName]) && isset($attributes[$typeName][$attributeCode])) {
            $attributeMetadata = $attributes[$typeName][$attributeCode];
            $permissions = [];
            foreach ($attributeMetadata[Converter::RESOURCE_PERMISSIONS] as $permission) {
                $permissions[] = $permission;
            }
            return $permissions;
        }

        return [];
    }
}
