<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Api\ExtensionAttribute\JoinData;
use Magento\Framework\Api\ExtensionAttribute\JoinDataFactory;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Factory class for instantiation of extension attributes objects.
 */
class ExtensionAttributesFactory
{
    const EXTENSIBLE_INTERFACE_NAME = 'Magento\Framework\Api\ExtensibleDataInterface';

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var JoinDataFactory
     */
    private $extensionAttributeJoinDataFactory;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * Map is used for performance optimization.
     *
     * @var array
     */
    private $classInterfaceMap = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Config $config
     * @param JoinDataFactory $extensionAttributeJoinDataFactory
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Config $config,
        JoinDataFactory $extensionAttributeJoinDataFactory,
        TypeProcessor $typeProcessor
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->extensionAttributeJoinDataFactory = $extensionAttributeJoinDataFactory;
        $this->typeProcessor = $typeProcessor;
    }

    /**
     * Create extension attributes object, custom for each extensible class.
     *
     * @param string $extensibleClassName
     * @param array $data
     * @return object
     */
    public function create($extensibleClassName, $data = [])
    {
        $interfaceReflection = new \ReflectionClass($this->getExtensibleInterfaceName($extensibleClassName));

        $methodReflection = $interfaceReflection->getMethod('getExtensionAttributes');
        if ($methodReflection->getDeclaringClass() == self::EXTENSIBLE_INTERFACE_NAME) {
            throw new \LogicException(
                "Method 'getExtensionAttributes' must be overridden in the interfaces "
                . "which extend '" . self::EXTENSIBLE_INTERFACE_NAME . "'. "
                . "Concrete return type should be specified."
            );
        }

        $interfaceName = '\\' . $interfaceReflection->getName();
        $extensionClassName = substr($interfaceName, 0, -strlen('Interface')) . 'Extension';
        $extensionInterfaceName = $extensionClassName . 'Interface';

        /** Ensure that proper return type of getExtensionAttributes() method is specified */
        $methodDocBlock = $methodReflection->getDocComment();
        $pattern = "/@return\s+" . str_replace('\\', '\\\\', $extensionInterfaceName) . "/";
        if (!preg_match($pattern, $methodDocBlock)) {
            throw new \LogicException(
                "Method 'getExtensionAttributes' must be overridden in the interfaces "
                . "which extend '" . self::EXTENSIBLE_INTERFACE_NAME . "'. "
                . "Concrete return type must be specified. Please fix :" . $interfaceName
            );
        }

        $extensionFactoryName = $extensionClassName . 'Factory';
        $extensionFactory = $this->objectManager->create($extensionFactoryName);
        return $extensionFactory->create($data);
    }

    /**
     * Processes join instructions to add to the collection for a data interface.
     *
     * @param DbCollection $collection
     * @param string $extensibleEntityClass
     * @return void
     */
    public function process(DbCollection $collection, $extensibleEntityClass)
    {
        $joinDirectives = $this->getJoinDirectivesForType($extensibleEntityClass);
        foreach ($joinDirectives as $attributeCode => $directive) {
            /** @var JoinData $joinData */
            $joinData = $this->extensionAttributeJoinDataFactory->create();
            $joinData->setReferenceTable($directive[Converter::JOIN_REFERENCE_TABLE])
                ->setReferenceTableAlias('extension_attribute_' . $attributeCode)
                ->setReferenceField($directive[Converter::JOIN_REFERENCE_FIELD])
                ->setJoinField($directive[Converter::JOIN_JOIN_ON_FIELD]);
            $selectFieldsMapper = function ($selectFieldData) {
                return $selectFieldData[Converter::JOIN_SELECT_FIELD];
            };
            $joinData->setSelectFields(array_map($selectFieldsMapper, $directive[Converter::JOIN_SELECT_FIELDS]));
            $collection->joinExtensionAttribute($joinData, [$this, 'extractExtensionAttributes']);
        }
    }

    /**
     * Extract extension attributes into separate extension object.
     *
     * @param string $extensibleEntityClass
     * @param array $data
     * @return array
     * @throws \LogicException
     */
    public function extractExtensionAttributes($extensibleEntityClass, array $data)
    {
        if (!$this->isExtensibleAttributesImplemented($extensibleEntityClass)) {
            /* do nothing as there are no extension attributes */
            return $data;
        }

        $joinDirectives = $this->getJoinDirectivesForType($extensibleEntityClass);
        $extensionData = [];
        foreach ($joinDirectives as $attributeCode => $directive) {
            $this->populateAttributeCodeWithDirective(
                $attributeCode,
                $directive,
                $data,
                $extensionData,
                $extensibleEntityClass
            );
        }
        if (!empty($extensionData)) {
            $extensionAttributes = $this->create($extensibleEntityClass, $extensionData);
            $data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY] = $extensionAttributes;
        }
        return $data;
    }

    /**
     * Populate a specific attribute code with join directive instructions.
     *
     * @param string $attributeCode
     * @param array $directive
     * @param array &$data
     * @param array &$extensionData
     * @param string $extensibleEntityClass
     * @return void
     */
    private function populateAttributeCodeWithDirective(
        $attributeCode,
        $directive,
        &$data,
        &$extensionData,
        $extensibleEntityClass
    ) {
        $attributeType = $directive[Converter::DATA_TYPE];
        $selectFields = $directive[Converter::JOIN_SELECT_FIELDS];
        foreach ($selectFields as $selectField) {
            $selectFieldAlias = 'extension_attribute_' . $attributeCode
                . '_' . $selectField[Converter::JOIN_SELECT_FIELD];
            if (isset($data[$selectFieldAlias])) {
                if ($this->typeProcessor->isArrayType($attributeType)) {
                    throw new \LogicException(
                        sprintf(
                            'Join directives cannot be processed for attribute (%s) of extensible entity (%s),'
                            . ' which has an Array type (%s).',
                            $attributeCode,
                            $this->getExtensibleInterfaceName($extensibleEntityClass),
                            $attributeType
                        )
                    );
                } elseif ($this->typeProcessor->isTypeSimple($attributeType)) {
                    $extensionData['data'][$attributeCode] = $data[$selectFieldAlias];
                    unset($data[$selectFieldAlias]);
                    break;
                } else {
                    if (!isset($extensionData['data'][$attributeCode])) {
                        $extensionData['data'][$attributeCode] = $this->objectManager->create($attributeType);
                    }
                    $setterName = $selectField[Converter::JOIN_SELECT_FIELD_SETTER]
                        ? $selectField[Converter::JOIN_SELECT_FIELD_SETTER]
                        :'set' . ucfirst(
                            SimpleDataObjectConverter::snakeCaseToCamelCase(
                                $selectField[Converter::JOIN_SELECT_FIELD]
                            )
                        );
                    $extensionData['data'][$attributeCode]->$setterName($data[$selectFieldAlias]);
                    unset($data[$selectFieldAlias]);
                }
            }
        }
    }

    /**
     * Returns the internal join directive config for a given type.
     *
     * Array returned has all of the \Magento\Framework\Api\ExtensionAttribute\Config\Converter JOIN* fields set.
     *
     * @param string $extensibleEntityClass
     * @return array
     */
    private function getJoinDirectivesForType($extensibleEntityClass)
    {
        $extensibleInterfaceName = $this->getExtensibleInterfaceName($extensibleEntityClass);
        $extensibleInterfaceName = ltrim($extensibleInterfaceName, '\\');
        $config = $this->config->get();
        if (!isset($config[$extensibleInterfaceName])) {
            return [];
        }

        $typeAttributesConfig = $config[$extensibleInterfaceName];
        $joinDirectives = [];
        foreach ($typeAttributesConfig as $attributeCode => $attributeConfig) {
            if (isset($attributeConfig[Converter::JOIN_DIRECTIVE])) {
                $joinDirectives[$attributeCode] = $attributeConfig[Converter::JOIN_DIRECTIVE];
                $joinDirectives[$attributeCode][Converter::DATA_TYPE] = $attributeConfig[Converter::DATA_TYPE];
            }
        }

        return $joinDirectives;
    }

    /**
     * Identify concrete extensible interface name based on the class name.
     *
     * @param string $extensibleClassName
     * @return string
     */
    private function getExtensibleInterfaceName($extensibleClassName)
    {
        $exceptionMessage = "Class '{$extensibleClassName}' must implement an interface, "
            . "which extends from '" . self::EXTENSIBLE_INTERFACE_NAME . "'";
        $notExtensibleClassFlag = '';
        if (isset($this->classInterfaceMap[$extensibleClassName])) {
            if ($notExtensibleClassFlag === $this->classInterfaceMap[$extensibleClassName]) {
                throw new \LogicException($exceptionMessage);
            } else {
                return $this->classInterfaceMap[$extensibleClassName];
            }
        }
        $modelReflection = new \ReflectionClass($extensibleClassName);
        if ($modelReflection->isInterface()
            && $modelReflection->isSubClassOf(self::EXTENSIBLE_INTERFACE_NAME)
            && $modelReflection->hasMethod('getExtensionAttributes')
        ) {
            $this->classInterfaceMap[$extensibleClassName] = $extensibleClassName;
            return $this->classInterfaceMap[$extensibleClassName];
        }
        foreach ($modelReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->isSubclassOf(self::EXTENSIBLE_INTERFACE_NAME)
                && $interfaceReflection->hasMethod('getExtensionAttributes')
            ) {
                $this->classInterfaceMap[$extensibleClassName] = $interfaceReflection->getName();
                return $this->classInterfaceMap[$extensibleClassName];
            }
        }
        $this->classInterfaceMap[$extensibleClassName] = $notExtensibleClassFlag;
        throw new \LogicException($exceptionMessage);
    }

    /**
     * Determine if the type is an actual extensible data interface.
     *
     * @param string $typeName
     * @return bool
     */
    private function isExtensibleAttributesImplemented($typeName)
    {
        try {
            $this->getExtensibleInterfaceName($typeName);
            return true;
        } catch (\LogicException $e) {
            return false;
        }
    }
}
