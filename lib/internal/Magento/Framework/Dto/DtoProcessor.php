<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttribute\InjectorProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Dto\DtoProcessor\DtoReflection;
use Magento\Framework\Dto\DtoProcessor\GetHydrationStrategy;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Reflection\TypeCaster;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Framework\Webapi\ServiceTypeToEntityTypeMap;
use ReflectionException;

/**
 * DTO processor. Supports both mutable and immutable DTO.
 *
 * @api
 */
class DtoProcessor
{
    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @var JoinProcessor
     */
    private $joinProcessor;

    /**
     * @var InjectorProcessor
     */
    private $injectorProcessor;

    /**
     * @var ServiceTypeToEntityTypeMap
     */
    private $serviceTypeToEntityTypeMap;

    /**
     * @var CustomAttributeTypeLocatorInterface
     */
    private $customAttributeTypeLocator;

    /**
     * @var AttributeValueFactory
     */
    private $attributeValueFactory;

    /**
     * @var DtoReflection
     */
    private $dtoReflection;

    /**
     * @var GetHydrationStrategy
     */
    private $getHydrationStrategy;

    /**
     * @var TypeCaster
     */
    private $typeCaster;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param DtoReflection $dtoReflection
     * @param GetHydrationStrategy $getHydrationStrategy
     * @param ObjectFactory $objectFactory
     * @param TypeCaster $typeCaster
     * @param TypeProcessor $typeProcessor
     * @param JoinProcessor $joinProcessor
     * @param InjectorProcessor $injectorProcessor
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param ServiceTypeToEntityTypeMap $serviceTypeToEntityTypeMap
     * @param CustomAttributeTypeLocatorInterface $customAttributeTypeLocator
     * @param AttributeValueFactory $attributeValueFactory
     */
    public function __construct(
        DtoReflection $dtoReflection,
        GetHydrationStrategy $getHydrationStrategy,
        ObjectFactory $objectFactory,
        TypeCaster $typeCaster,
        TypeProcessor $typeProcessor,
        JoinProcessor $joinProcessor,
        InjectorProcessor $injectorProcessor,
        ExtensionAttributesFactory $extensionAttributesFactory,
        DataObjectProcessor $dataObjectProcessor,
        ServiceTypeToEntityTypeMap $serviceTypeToEntityTypeMap,
        CustomAttributeTypeLocatorInterface $customAttributeTypeLocator,
        AttributeValueFactory $attributeValueFactory
    ) {
        $this->typeProcessor = $typeProcessor;
        $this->objectFactory = $objectFactory;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->joinProcessor = $joinProcessor;
        $this->injectorProcessor = $injectorProcessor;
        $this->serviceTypeToEntityTypeMap = $serviceTypeToEntityTypeMap;
        $this->customAttributeTypeLocator = $customAttributeTypeLocator;
        $this->attributeValueFactory = $attributeValueFactory;
        $this->dtoReflection = $dtoReflection;
        $this->getHydrationStrategy = $getHydrationStrategy;
        $this->typeCaster = $typeCaster;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * @param $value
     * @param string $type
     * @return array|object
     * @throws ReflectionException
     * @throws SerializationException
     */
    private function createObjectByType($value, string $type)
    {
        if (is_object($value) || ($type === 'array') || ($type === 'mixed')) {
            return $value;
        }

        if ($this->typeProcessor->isArrayType($type)) {
            $res = [];
            foreach ($value as $k => $subValue) {
                $itemType = $this->typeProcessor->getArrayItemType($type);
                $res[$k] = $this->createObjectByType($subValue, $itemType);
            }

            return $res;
        }

        if ($this->typeProcessor->isTypeSimple($type)) {
            return $this->typeCaster->castValueToType($value, $type);
        }

        return $this->createFromArray($value, $type);
    }

    /**
     * Derive the custom attribute code and value.
     *
     * @param string[] $customAttribute
     * @return string[]
     * @throws SerializationException
     */
    private function processCustomAttribute($customAttribute): array
    {
        $camelCaseAttributeCodeKey = lcfirst(
            SimpleDataObjectConverter::snakeCaseToUpperCamelCase(AttributeValue::ATTRIBUTE_CODE)
        );
        // attribute code key could be snake or camel case, depending on whether SOAP or REST is used.
        if (isset($customAttribute[AttributeValue::ATTRIBUTE_CODE])) {
            $customAttributeCode = $customAttribute[AttributeValue::ATTRIBUTE_CODE];
        } elseif (isset($customAttribute[$camelCaseAttributeCodeKey])) {
            $customAttributeCode = $customAttribute[$camelCaseAttributeCodeKey];
        } else {
            $customAttributeCode = null;
        }

        if (!$customAttributeCode && !isset($customAttribute[AttributeValue::VALUE])) {
            throw new SerializationException(
                new Phrase('An empty custom attribute is specified. Enter the attribute and try again.')
            );
        }

        if (!$customAttributeCode) {
            throw new SerializationException(
                new Phrase(
                    'A custom attribute is specified with a missing attribute code. Verify the code and try again.'
                )
            );
        }

        if (!array_key_exists(AttributeValue::VALUE, $customAttribute)) {
            throw new SerializationException(
                new Phrase(
                    'The "' . $customAttributeCode .
                    '" attribute code doesn\'t have a value set. Enter the value and try again.'
                )
            );
        }

        return [$customAttributeCode, $customAttribute[AttributeValue::VALUE]];
    }

    /**
     * Convert custom attribute data array to array of AttributeValue Data Object
     *
     * @param string $dataObjectClassName
     * @param array $customAttributesValueArray
     * @return AttributeValue[]
     * @throws SerializationException
     * @throws ReflectionException
     */
    private function convertCustomAttributeValue(string $dataObjectClassName, array $customAttributesValueArray): array
    {
        $result = [];
        $dataObjectClassName = ltrim($dataObjectClassName, '\\');

        foreach ($customAttributesValueArray as $key => $customAttribute) {
            if (!is_array($customAttribute)) {
                $customAttribute = [
                    AttributeValue::ATTRIBUTE_CODE => $key,
                    AttributeValue::VALUE => $customAttribute
                ];
            }

            [$customAttributeCode, $customAttributeValue] = $this->processCustomAttribute($customAttribute);

            $entityType = $this->serviceTypeToEntityTypeMap->getEntityType($dataObjectClassName);
            if ($entityType) {
                $type = $this->customAttributeTypeLocator->getType(
                    $customAttributeCode,
                    $entityType
                );
            } else {
                $type = TypeProcessor::ANY_TYPE;
            }

            $attributeValue = $this->createObjectByType($customAttributeValue, $type);

            //Populate the attribute value data object once the value for custom attribute is derived based on type
            $result[$customAttributeCode] = $this->attributeValueFactory->create()
                ->setAttributeCode($customAttributeCode)
                ->setValue($attributeValue);
        }

        return $result;
    }

    /**
     * Inject extension attributes from an array definition
     *
     * @param string $type
     * @param array $data
     * @return array
     * @throws ReflectionException
     * @throws SerializationException
     */
    private function injectExtensionAttributesByArray(string $type, array $data): array
    {
        if (!$this->dtoReflection->isExtensibleObject($type)) {
            return [];
        }

        $extensionAttributesType = $this->dtoReflection->getPropertyTypeFromGetterMethod(
            $type,
            ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY
        );

        $extensionAttributes = $data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY] ?? [];

        $extensionAttributes = array_replace(
            $extensionAttributes,
            $this->injectorProcessor->execute($type, $extensionAttributes)
        );


        foreach ($extensionAttributes as $attributeName => $attributeValue) {
            if (!is_array($attributeValue) && !is_object($attributeValue)) {
                continue;
            }

            $methodReturnType = $this->dtoReflection->getPropertyTypeFromGetterMethod(
                $extensionAttributesType,
                $attributeName
            );
            $attributeType = $this->typeProcessor->isArrayType($methodReturnType)
                ? $this->typeProcessor->getArrayItemType($methodReturnType)
                : $methodReturnType;

            $extensionAttributes[$attributeName] = $this->createFromArray($attributeValue, $attributeType);
        }

        $data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY] = $this->extensionAttributesFactory->create(
            $type,
            ['data' => $extensionAttributes]
        );


        return $data;
    }

    /**
     * Populate data object using data in array format.
     *
     * @param array $data
     * @param string $type
     * @return object
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function createFromArray(array $data, string $type)
    {
        // Normalize snake case properties
        foreach ($data as $k => $v) {
            $snakeCaseKey = SimpleDataObjectConverter::camelCaseToSnakeCase($k);
            if ($snakeCaseKey !== $k) {
                $data[$snakeCaseKey] = $v;
                unset($data[$k]);
            }
        }

        if ($this->dtoReflection->isExtensibleObject($type)) {
            if (!isset($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]) ||
                !is_object($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])
            ) {
                $data = $this->injectExtensionAttributesByArray($type, $data);
            }

            $data = $this->joinProcessor->extractExtensionAttributes($type, $data);
        }

        if (isset($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]) &&
            $this->dtoReflection->isCustomAttributesObject($type)
        ) {
            $data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] = $this->convertCustomAttributeValue(
                $type,
                $data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]
            );
        }

        $strategy = $this->getHydrationStrategy->execute($type, $data);
        if (!empty($strategy[GetHydrationStrategy::HYDRATOR_STRATEGY_ORPHAN])) {
            $firstProperty = current($strategy[GetHydrationStrategy::HYDRATOR_STRATEGY_ORPHAN]);
            throw new \LogicException(
                sprintf(
                    'Cannot inject property "%s" in class "%s".',
                    $firstProperty,
                    $type
                )
            );
        }

        $constructorParams = [];
        foreach ($strategy[GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM] as $paramName => $info) {
            $paramConstructor = $info['parameter'];
            $paramType = $info['type'];
            $constructorParams[$paramConstructor] = $this->createObjectByType($data[$paramName], $paramType);
        }

        if (!empty($strategy[GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA])) {
            $constructorParams['data'] = [];

            foreach ($strategy[GetHydrationStrategy::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA] as $paramName => $info) {
                $paramType = $info['type'];
                $constructorParams['data'][$paramName] = $this->createObjectByType($data[$paramName], $paramType);
            }
        }

        $resObject = $this->objectFactory->create($type, $constructorParams);

        foreach ($strategy[GetHydrationStrategy::HYDRATOR_STRATEGY_SETTER] as $paramName => $info) {
            $methodName = $info['method'];
            $paramType = $info['type'];
            $resObject->$methodName($this->createObjectByType($data[$paramName], $paramType));
        }

        if ($resObject instanceof CustomAttributesDataInterface) {
            foreach ($strategy[GetHydrationStrategy::HYDRATOR_STRATEGY_ORPHAN] as $paramName) {
                $resObject->setCustomAttribute($paramName, $data[$paramName]);
            }
        }

        if ($this->dtoReflection->isDataModel($type)) {
            $resObject->setDataChanges(true);
        }

        return $resObject;
    }

    /**
     * Create a new DTO with updated information from array
     *
     * @param $sourceObject
     * @param array $data
     * @return object
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function createUpdatedObjectFromArray(
        $sourceObject,
        array $data
    ) {
        $sourceObjectData = $this->getObjectData($sourceObject);
        $data = array_replace($sourceObjectData, $data);

        return $this->createFromArray($data, get_class($sourceObject));
    }

    /**
     * Merge data into object data
     *
     * @param $sourceObject
     * @return array
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getObjectData($sourceObject): array
    {
        return $this->dataObjectProcessor->buildOutputDataArray($sourceObject, get_class($sourceObject));
    }
}
