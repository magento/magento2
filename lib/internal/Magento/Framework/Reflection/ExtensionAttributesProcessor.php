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
     * @var bool
     */
    private $isPermissionChecked;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param AuthorizationInterface $authorization
     * @param ExtensionAttributesConfigReader $configReader
     * @param bool $isPermissionChecked
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        AuthorizationInterface $authorization,
        ExtensionAttributesConfigReader $configReader,
        $isPermissionChecked = false
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->authorization = $authorization;
        $this->configReader = $configReader;
        $this->isPermissionChecked = $isPermissionChecked;
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
        $methods = $this->dataObjectProcessor->getMethodsMap($dataObjectType);
        $outputData = [];

        /** @var MethodReflection $method */
        foreach ($methods as $methodName => $methodReflectionData) {
            if (!$this->methodsMapProcessor->isMethodValidForDataField($dataObjectType, $methodName)) {
                continue;
            }

            $key = $this->fieldNamer->getFieldNameForMethodName($methodName);
            if ($this->isPermissionChecked && !$this->isAttributePermissionValid($dataObjectType, $key)) {
                continue;
            }

            $value = $dataObject->{$methodName}();
            if ($value === null) {
                // all extension attributes are optional so don't need to check if isRequired
                continue;
            }

            // should write field?
            // isWriterValid
            // what value should be written

            $returnType = $this->methodsMapProcessor->getMethodReturnType($dataObjectType, $methodName);

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
            } else {
                $value = $this->typeCaster->castValueToType($value, $returnType);
            }

            $outputData[$key] = $value;
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
