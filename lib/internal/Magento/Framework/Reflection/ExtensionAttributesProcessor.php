<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Laminas\Code\Reflection\MethodReflection;

/**
 * Processes extension attributes and produces an array for the data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExtensionAttributesProcessor
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var MethodsMap
     */
    private $methodsMapProcessor;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool
     */
    private $isPermissionChecked;

    /**
     * @var FieldNamer
     */
    private $fieldNamer;

    /**
     * @var TypeCaster
     */
    private $typeCaster;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param MethodsMap $methodsMapProcessor
     * @param TypeCaster $typeCaster
     * @param FieldNamer $fieldNamer
     * @param AuthorizationInterface $authorization
     * @param Config $config
     * @param bool $isPermissionChecked
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        MethodsMap $methodsMapProcessor,
        TypeCaster $typeCaster,
        FieldNamer $fieldNamer,
        AuthorizationInterface $authorization,
        Config $config,
        $isPermissionChecked = false
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->methodsMapProcessor = $methodsMapProcessor;
        $this->typeCaster = $typeCaster;
        $this->fieldNamer = $fieldNamer;
        $this->authorization = $authorization;
        $this->config = $config;
        $this->isPermissionChecked = $isPermissionChecked;
    }

    /**
     * Writes out the extension attributes in an array.
     *
     * @param ExtensionAttributeInterface $dataObject
     * @param string $dataObjectType
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function buildOutputDataArray(ExtensionAttributesInterface $dataObject, $dataObjectType)
    {
        $methods = $this->methodsMapProcessor->getMethodsMap($dataObjectType);
        $outputData = [];

        /** @var MethodReflection $method */
        foreach (array_keys($methods) as $methodName) {
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
                    $valueResult[] = $this->typeCaster->castValueToType($singleValue, $arrayElementType);
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
     * Is attribute permissions valid
     *
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
     * Get regular type for extension attribute type
     *
     * @param string $name
     * @return string
     */
    private function getRegularTypeForExtensionAttributesType($name)
    {
        return ltrim(str_replace('ExtensionInterface', 'Interface', $name), '\\');
    }

    /**
     * Get permissions for attribute type
     *
     * @param string $typeName
     * @param string $attributeCode
     * @return string[] A list of permissions
     */
    private function getPermissionsForTypeAndMethod($typeName, $attributeCode)
    {
        $attributes = $this->config->get();
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
