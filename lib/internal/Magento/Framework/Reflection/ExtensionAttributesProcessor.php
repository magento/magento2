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
use Magento\Framework\Reflection\MethodsMap;
use Zend\Code\Reflection\MethodReflection;

/**
 * Processes extension attributes and produces an array for the data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class ExtensionAttributesProcessor
{
    /**
     * @var DataObjectProcessor
     * @since 2.0.0
     */
    private $dataObjectProcessor;

    /**
     * @var MethodsMap
     * @since 2.0.0
     */
    private $methodsMapProcessor;

    /**
     * @var AuthorizationInterface
     * @since 2.0.0
     */
    private $authorization;

    /**
     * @var Config
     * @since 2.0.0
     */
    private $config;

    /**
     * @var bool
     * @since 2.0.0
     */
    private $isPermissionChecked;

    /**
     * @var FieldNamer
     * @since 2.0.0
     */
    private $fieldNamer;

    /**
     * @var TypeCaster
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param string $dataObjectType
     * @param string $attributeCode
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function getRegularTypeForExtensionAttributesType($name)
    {
        return ltrim(str_replace('ExtensionInterface', 'Interface', $name), '\\');
    }

    /**
     * @param string $typeName
     * @param string $attributeCode
     * @return string[] A list of permissions
     * @since 2.0.0
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
