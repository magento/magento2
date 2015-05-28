<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Config\Reader;
use Magento\Framework\Api\Config\Converter;
use Magento\Framework\Data\Collection\Db as DbCollection;
use Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinData;
use Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinDataFactory;
use Magento\Framework\Reflection\TypeProcessor;

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
    protected $_objectManager = null;

    /**
     * @var Reader
     */
    private $configReader;

    /**
     * @var ExtensionAttributeJoinDataFactory
     */
    private $extensionAttributeJoinDataFactory;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Reader $configReader
     * @param ExtensionAttributeJoinDataFactory $extensionAttributeJoinDataFactory
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Reader $configReader,
        ExtensionAttributeJoinDataFactory $extensionAttributeJoinDataFactory,
        TypeProcessor $typeProcessor
    ) {
        $this->_objectManager = $objectManager;
        $this->configReader = $configReader;
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
                . "which extend 'Magento\\Framework\\Api\\ExtensibleDataInterface'. "
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
                . "which extend 'Magento\\Framework\\Api\\ExtensibleDataInterface'. "
                . "Concrete return type must be specified. Please fix :" . $interfaceName
            );
        }

        $extensionFactoryName = $extensionClassName . 'Factory';
        $extensionFactory = $this->_objectManager->create($extensionFactoryName);
        return $extensionFactory->create($data);
    }

    /**
     * Identify concrete extensible interface name based on the class name.
     *
     * @param string $extensibleClassName
     * @return string
     */
    private function getExtensibleInterfaceName($extensibleClassName)
    {
        $modelReflection = new \ReflectionClass($extensibleClassName);
        foreach ($modelReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->isSubclassOf(self::EXTENSIBLE_INTERFACE_NAME)
                && $interfaceReflection->hasMethod('getExtensionAttributes')
            ) {
                return $interfaceReflection->getName();
            }
        }
        throw new \LogicException(
            "Class '{$extensibleClassName}' must implement an interface, "
            . "which extends from 'Magento\\Framework\\Api\\ExtensibleDataInterface'"
        );
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
        // TODO: Optimize, since will be called on each collection load
        $joinDirectives = $this->getJoinDirectivesForType($extensibleEntityClass);
        foreach ($joinDirectives as $attributeCode => $directive) {
            $selectFields = explode(',', $directive[Converter::JOIN_SELECT_FIELDS]);
            foreach ($selectFields as $selectField) {
                /** @var ExtensionAttributeJoinData $joinData */
                $joinData = $this->extensionAttributeJoinDataFactory->create();
                $joinData->setReferenceTable($directive[Converter::JOIN_REFERENCE_TABLE])
                    ->setReferenceTableAlias('extension_attribute_' . $attributeCode)
                    ->setReferenceField($directive[Converter::JOIN_REFERENCE_FIELD])
                    ->setJoinField($directive[Converter::JOIN_JOIN_ON_FIELD])
                    ->setSelectField(trim($selectField));
                $collection->joinExtensionAttribute($joinData);
            }
        }
    }

    /**
     * Populate extension attributes object of the provided extensible entity based on the provided data.
     *
     * @param ExtensibleDataInterface $extensibleEntity
     * @param array $data
     */
    public function populateExtensionAttributes(
        \Magento\Framework\Api\ExtensibleDataInterface $extensibleEntity,
        array $data
    ) {
        // TODO: Optimize, since will be called on each extensible model setData()
        $joinDirectives = $this->getJoinDirectivesForType(get_class($extensibleEntity));
        foreach ($joinDirectives as $attributeCode => $directive) {
            $extensionData = [];
            $selectFields = explode(',', $directive[Converter::JOIN_SELECT_FIELDS]);
            foreach ($selectFields as $selectField) {
                $selectField = trim($selectField);
                $selectFieldAlias = 'extension_attribute_' . $attributeCode . '_' . $selectField;
                if ($this->typeProcessor->isTypeSimple($directive[Converter::DATA_TYPE])) {
                    if (isset($data[$selectFieldAlias])) {
                        $extensionData['data'][$attributeCode] = $data[$selectFieldAlias];
                        unset($data[$selectFieldAlias]);
                        break;
                    }
                } else {
                    // TODO: Add processing of case with complex field
//                    $extensionData['data'][$attributeCode][$selectField] = $collectionItem->getData($selectFieldAlias);
                }
            }
            $extensionAttributes = $this->create(
                get_class($extensibleEntity),
                $extensionData
            );
            $extensibleEntity->setExtensionAttributes($extensionAttributes);
        }
    }

    /**
     * @param string $extensibleEntityClass
     * @return array
     */
    private function getJoinDirectivesForType($extensibleEntityClass)
    {
        $extensibleInterfaceName = $this->getExtensibleInterfaceName($extensibleEntityClass);
        $config = $this->configReader->read();
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
}
