<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto;

use Exception;
use LogicException;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttribute\InjectorProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\NameFinder;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Framework\Webapi\ServiceTypeToEntityTypeMap;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Zend\Code\Reflection\ClassReflection;

/**
 * DTO processor. Supports both mutable and immutable DTO.
 *
 * @api
 */
class DtoProcessor
{
    /**
     * Strategy for setter hydration
     */
    public const HYDRATOR_STRATEGY_SETTER = 'setter';

    /**
     * Strategy for constructor parameters injection
     */
    public const HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM = 'constructor';

    /**
     * Strategy for constructor data parameter injection
     */
    public const HYDRATOR_STRATEGY_CONSTRUCTOR_DATA = 'data';

    /**
     * List of orphan parameters
     */
    public const HYDRATOR_STRATEGY_ORPHAN = 'orphan';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var NameFinder
     */
    private $nameFinder;

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
     * @var MethodsMap
     */
    private $methodsMap;

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
     * @param ObjectFactory $objectFactory
     * @param TypeProcessor $typeProcessor
     * @param ConfigInterface $config
     * @param NameFinder $nameFinder
     * @param JoinProcessor $joinProcessor
     * @param InjectorProcessor $injectorProcessor
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param MethodsMap $methodsMap
     * @param ServiceTypeToEntityTypeMap $serviceTypeToEntityTypeMap
     * @param CustomAttributeTypeLocatorInterface $customAttributeTypeLocator
     * @param AttributeValueFactory $attributeValueFactory
     */
    public function __construct(
        ObjectFactory $objectFactory,
        TypeProcessor $typeProcessor,
        ConfigInterface $config,
        NameFinder $nameFinder,
        JoinProcessor $joinProcessor,
        InjectorProcessor $injectorProcessor,
        ExtensionAttributesFactory $extensionAttributesFactory,
        MethodsMap $methodsMap,
        ServiceTypeToEntityTypeMap $serviceTypeToEntityTypeMap,
        CustomAttributeTypeLocatorInterface $customAttributeTypeLocator,
        AttributeValueFactory $attributeValueFactory
    ) {
        $this->config = $config;
        $this->typeProcessor = $typeProcessor;
        $this->nameFinder = $nameFinder;
        $this->objectFactory = $objectFactory;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->joinProcessor = $joinProcessor;
        $this->methodsMap = $methodsMap;
        $this->injectorProcessor = $injectorProcessor;
        $this->serviceTypeToEntityTypeMap = $serviceTypeToEntityTypeMap;
        $this->customAttributeTypeLocator = $customAttributeTypeLocator;
        $this->attributeValueFactory = $attributeValueFactory;
    }

    /**
     * Return true if a class is a data object using "data" constructor field
     *
     * @param string $className
     * @return bool
     */
    private function isDataObject(string $className): bool
    {
        $className = $this->getRealClassName($className);
        return
            is_subclass_of($className, AbstractSimpleObject::class) ||
            is_subclass_of($className, DataObject::class);
    }

    /**
     * Return true if a class is extensible through extension attributes
     *
     * @param string $className
     * @return bool
     * @throws ReflectionException
     */
    private function isExtensibleObject(string $className): bool
    {
        // TODO: Add a cache layer here
        $modelReflection = new ReflectionClass($className);

        if ($modelReflection->isInterface()
            && $modelReflection->isSubclassOf(ExtensibleDataInterface::class)
            && $modelReflection->hasMethod('getExtensionAttributes')
        ) {
            return true;
        }

        foreach ($modelReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->isSubclassOf(ExtensibleDataInterface::class)
                && $interfaceReflection->hasMethod('getExtensionAttributes')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true if a class is extensible through custom attributes
     *
     * @param string $className
     * @return bool
     */
    private function isCustomAttributesObject(string $className): bool
    {
        return
            is_subclass_of($className, CustomAttributesDataInterface::class);
    }

    /**
     * Return true if a class is inherited from \Magento\Framework\Model\AbstractModel and requires setDataChange
     *
     * @param string $className
     * @return bool
     */
    private function isDataModel(string $className): bool
    {
        $className = $this->getRealClassName($className);
        return
            is_subclass_of($className, AbstractModel::class);
    }

    /**
     * Get real class name (if preferenced)
     *
     * @param string $className
     * @return string
     */
    private function getRealClassName(string $className): string
    {
        $preferenceClass = $this->config->getPreference($className);
        return $preferenceClass ?: $className;
    }


    /**
     * @param $value
     * @param string $type
     * @return mixed
     */
    private function castType($value, string $type)
    {
        if (is_array($value) || !$this->typeProcessor->isTypeSimple($type)) {
            return $value;
        }

        if ($type === 'int' || $type === 'integer') {
            return (int) $value;
        }

        if ($type === 'string') {
            return (string) $value;
        }

        if ($type === 'bool' || $type === 'boolean' || $type === 'true' || $type === 'false') {
            return (bool) $value;
        }

        if ($type === 'float') {
            return (float) $value;
        }

        if ($type === 'double') {
            return (double) $value;
        }

        return $value;
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
            return $this->castType($value, $type);
        }

        return $this->createFromArray($value, $type);
    }

    /**
     * Return the strategy for values injection.
     *
     *
     * @param string $className
     * @param array $data
     * @return array
     * @throws ReflectionException
     */
    public function getValuesHydratingStrategy(string $className, array $data): array
    {
        // TODO:: Add a cache layer here
        $strategy = [
            self::HYDRATOR_STRATEGY_SETTER => [],
            self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [],
            self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
            self::HYDRATOR_STRATEGY_ORPHAN => [],
        ];

        $class = new ClassReflection($className);
        $realClassName = $this->getRealClassName($className);
        $realClass = new ClassReflection($realClassName);

        // Enumerate parameters and types
        $paramTypes = [];
        foreach ($data as $propertyName => $propertyValue) {
            $type = $this->getPropertyTypeFromGetterMethod($class, $propertyName);
            $paramTypes[$propertyName] = $type;
        }

        $requiredConstructorParams = [];

        // Check for constructor parameters
        $constructor = $realClass->getConstructor();
        if ($constructor !== null) {
            // Inject data constructor parameter
            if ($this->isDataObject($realClass->getName())) {
                foreach ($data as $propertyName => $propertyValue) {
                    $type = $paramTypes[$propertyName];
                    if ($paramTypes[$propertyName] !== '') {
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$propertyName] = [
                            'type' => $type
                        ];
                    }
                }
            }

            // Inject into named parameters if a getter method exists
            $parameters = $constructor->getParameters();
            foreach ($parameters as $parameter) {
                $snakeCaseProperty = SimpleDataObjectConverter::camelCaseToSnakeCase($parameter->getName());
                $type = $paramTypes[$snakeCaseProperty] ?? '';

                if (($type !== '') && isset($data[$snakeCaseProperty])) {
                    unset($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$snakeCaseProperty]);
                    $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM][$snakeCaseProperty] = [
                        'parameter' => $parameter->getName(),
                        'type' => $type
                    ];

                    if (!$parameter->isDefaultValueAvailable()) {
                        $requiredConstructorParams[] = $snakeCaseProperty;
                    }
                }
            }
        }

        // Fall back to setters if defined
        foreach ($data as $propertyName => $propertyValue) {
            $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
            try {
                $setterMethod = $this->nameFinder->getSetterMethodName($class, $camelCaseProperty);
                $type = $paramTypes[$propertyName] ?? '';
                if ($type !== '') {
                    if (in_array($propertyName, $requiredConstructorParams, true)) {
                        continue;
                    }

                    unset(
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$propertyName],
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM][$propertyName]
                    );

                    $strategy[self::HYDRATOR_STRATEGY_SETTER][$propertyName] = [
                        'type' => $type,
                        'method' => $setterMethod
                    ];
                }

            } catch (LogicException $e) {
                if (!isset($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$propertyName]) &&
                    !isset($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM][$propertyName])
                ) {
                    $strategy[self::HYDRATOR_STRATEGY_ORPHAN][] = $propertyName;
                }
            }
        }

        return $strategy;
    }

    /**
     * Return the property type by its getter name
     * @param ClassReflection $classReflection
     * @param string $propertyName
     * @return string
     */
    private function getPropertyTypeFromGetterMethod(ClassReflection $classReflection, string $propertyName): string
    {
        $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
        try {
            $methodName = $this->nameFinder->getGetterMethodName($classReflection, $camelCaseProperty);
        } catch (Exception $e) {
            return '';
        }

        $methodReflection = $classReflection->getMethod($methodName);
        if ($methodReflection->isPublic()) {
            $paramType = (string) $this->typeProcessor->getGetterReturnType($methodReflection)['type'];
            return $this->typeProcessor->resolveFullyQualifiedClassName($classReflection, $paramType);
        }

        return '';
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
        if (!$this->isExtensibleObject($type)) {
            return [];
        }

        $extensionAttributesType = $this->getPropertyTypeFromGetterMethod(
            new ClassReflection($type),
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

            $methodReturnType = $this->getPropertyTypeFromGetterMethod(
                new ClassReflection($extensionAttributesType),
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

        if ($this->isExtensibleObject($type)) {
            if (!isset($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]) ||
                !is_object($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])
            ) {
                $data = $this->injectExtensionAttributesByArray($type, $data);
            }

            $data = $this->joinProcessor->extractExtensionAttributes($type, $data);
        }

        if (isset($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]) &&
            $this->isCustomAttributesObject($type)
        ) {
            $data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] = $this->convertCustomAttributeValue(
                $type,
                $data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]
            );
        }

        $strategy = $this->getValuesHydratingStrategy($type, $data);

        $constructorParams = [];
        foreach ($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM] as $paramName => $info) {
            $paramConstructor = $info['parameter'];
            $paramType = $info['type'];
            $constructorParams[$paramConstructor] = $this->createObjectByType($data[$paramName], $paramType);
        }

        if (!empty($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA])) {
            $constructorParams['data'] = [];

            foreach ($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA] as $paramName => $info) {
                $paramType = $info['type'];
                $constructorParams['data'][$paramName] = $this->createObjectByType($data[$paramName], $paramType);
            }
        }

        $resObject = $this->objectFactory->create($type, $constructorParams);

        foreach ($strategy[self::HYDRATOR_STRATEGY_SETTER] as $paramName => $info) {
            $methodName = $info['method'];
            $paramType = $info['type'];
            $resObject->$methodName($this->createObjectByType($data[$paramName], $paramType));
        }

        if ($resObject instanceof CustomAttributesDataInterface) {
            foreach ($strategy[self::HYDRATOR_STRATEGY_ORPHAN] as $paramName) {
                $resObject->setCustomAttribute($paramName, $data[$paramName]);
            }
        }

        if ($this->isDataModel($type)) {
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
     * @param $value
     * @return mixed
     * @throws ReflectionException
     */
    private function explodeObjectValue($value)
    {
        if (is_object($value)) {
            return $this->getObjectData($value);
        }

        if (is_array($value)) {
            $res = [];
            foreach ($value as $subValue) {
                $res[] = $this->explodeObjectValue($subValue);
            }

            return $res;
        }

        return $value;
    }

    /**
     * Merge data into object data
     *
     * @param $sourceObject
     * @return array
     * @throws ReflectionException
     */
    public function getObjectData($sourceObject): array
    {
        $objectType = get_class($sourceObject);

        if ($sourceObject === null || !is_object($sourceObject)) {
            return [];
        }

        $objectType = $objectType ?: get_class($sourceObject);
        $sourceObjectMethods = $this->methodsMap->getMethodsMap($objectType);

        $res = [];
        foreach ($sourceObjectMethods as $sourceObjectMethod => $sourceObjectMethodInfo) {
            if (!$this->methodsMap->isMethodValidForDataField($objectType, $sourceObjectMethod)) {
                continue;
            }

            if (preg_match('/^(is|get)([A-Z]\w*)$/', $sourceObjectMethod, $matches)) {
                $propertyName = SimpleDataObjectConverter::camelCaseToSnakeCase($matches[2]);
                $methodName = $matches[0];

                $methodReflection = new ReflectionMethod($sourceObject, $methodName);
                if ($methodReflection->getNumberOfRequiredParameters() === 0) {
                    try {
                        $value = $this->explodeObjectValue($sourceObject->$methodName());
                    } catch (Exception $e) {
                        continue;
                    }

                    if (($propertyName === 'extension_attributes') &&
                        empty($value) &&
                        $this->isExtensibleObject($objectType)
                    ) {
                        continue;
                    }

                    if ($this->isCustomAttributesObject($objectType)) {
                        if (($propertyName === 'custom_attributes') &&
                            empty($value)
                        ) {
                            continue;
                        }

                        if ($propertyName === 'custom_attributes_codes') {
                            continue;
                        }
                    }

                    if ($value !== null) {
                        $res[$propertyName] = $this->castType(
                            $value,
                            $sourceObjectMethodInfo['type']
                        );
                    }
                }
            }
        }

        return $res;
    }
}
