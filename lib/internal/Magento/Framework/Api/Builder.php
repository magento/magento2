<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Builder implements BuilderInterface
{
    /**#@+
     * Constant which defines if builder is created for building data objects or data models.
     */
    const TYPE_DATA_OBJECT = 'data_object';
    const TYPE_DATA_MODEL = 'data_model';
    /**#@-*/

    /**
     * @var string
     */
    protected $modelClassInterface;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var MetadataServiceInterface
     */
    protected $metadataService;

    /**
     * @var string[]
     */
    protected $customAttributesCodes = null;

    /**
     * @var \Magento\Framework\Api\AttributeDataBuilder
     */
    protected $attributeValueBuilder;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $objectProcessor;

    /**
     * @var array
     */
    protected $interfaceMethodsDescription;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var \Magento\Framework\Serialization\DataBuilderFactory
     */
    protected $dataBuilderFactory;

    /**
     * @var \Magento\Framework\ObjectManager\ConfigInterface
     */
    protected $objectManagerConfig;

    /**
     * @param ObjectFactory $objectFactory
     * @param MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $attributeValueBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param string $modelClassInterface
     */
    public function __construct(
        ObjectFactory $objectFactory,
        MetadataServiceInterface $metadataService,
        \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory,
        \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig,
        $modelClassInterface = null
    ) {
        $this->objectFactory = $objectFactory;
        $this->metadataService = $metadataService;
        $this->modelClassInterface = $modelClassInterface;
        $this->objectProcessor = $objectProcessor;
        $this->attributeValueBuilder = $attributeValueBuilder;
        $this->typeProcessor = $typeProcessor;
        $this->dataBuilderFactory = $dataBuilderFactory;
        $this->objectManagerConfig = $objectManagerConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $customAttributesCodes = $this->getCustomAttributesCodes();
        if (in_array($attributeCode, $customAttributesCodes)) {
            $attribute = $this->attributeValueBuilder
                ->setAttributeCode($attributeCode)
                ->setValue($attributeValue)
                ->create();
            //Stores as an associative array for easier lookup and processing
            $this->data[ExtensibleDataInterface::CUSTOM_ATTRIBUTES][$attributeCode] = $attribute;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttributes(array $attributes)
    {
        $customAttributesCodes = $this->getCustomAttributesCodes();
        /** @var \Magento\Framework\Api\AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof AttributeValue) {
                throw new \LogicException('Custom Attribute array elements can only be type of AttributeValue');
            }
            $attributeCode = $attribute->getAttributeCode();
            if (in_array($attributeCode, $customAttributesCodes)) {
                $this->data[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY][$attributeCode] = $attribute;
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        if ($this->getDataType() == self::TYPE_DATA_MODEL) {
            /** @var \Magento\Framework\Model\AbstractExtensibleModel $dataObject */
            $dataObject = $this->objectFactory->create(
                $this->_getDataObjectType(),
                ['data' => $this->data]
            );
            $dataObject->setDataChanges(true);
        } else {
            $dataObjectType = $this->_getDataObjectType();
            $dataObject = $this->objectFactory->create(
                $dataObjectType,
                ['builder' => $this]
            );
        }
        if ($dataObject instanceof \Magento\Framework\Object) {
            $dataObject->setDataChanges(true);
        }
        $this->data = [];
        return $dataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function populateWithArray(array $data)
    {
        $this->data = [];
        $this->_setDataValues($data);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(ExtensibleDataInterface $prototype)
    {
        $objectType = $this->_getDataObjectType();
        if (!($prototype instanceof $objectType)) {
            throw new \LogicException('Wrong prototype object given. It can only be of "' . $objectType . '" type.');
        }
        $prototypeArray = $this->objectProcessor->buildOutputDataArray($prototype, $objectType);
        return $this->populateWithArray($prototypeArray);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeDataObjects(
        ExtensibleDataInterface $firstDataObject,
        ExtensibleDataInterface $secondDataObject
    ) {
        $objectType = $this->_getDataObjectType();
        if (!$firstDataObject instanceof $objectType || !$secondDataObject instanceof $objectType) {
            throw new \LogicException('Wrong prototype object given. It can only be of "' . $objectType . '" type.');
        }
        $firstObjectArray = $this->objectProcessor->buildOutputDataArray($firstDataObject, $objectType);
        $secondObjectArray = $this->objectProcessor->buildOutputDataArray($secondDataObject, $objectType);
        $this->_setDataValues($firstObjectArray);
        $this->_setDataValues($secondObjectArray);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeDataObjectWithArray(ExtensibleDataInterface $dataObject, array $data)
    {
        $objectType = $this->_getDataObjectType();
        if (!($dataObject instanceof $objectType)) {
            throw new \LogicException('Wrong prototype object given. It can only be of "' . $objectType . '" type.');
        }
        $dataArray = $this->objectProcessor->buildOutputDataArray($dataObject, $objectType);
        $this->_setDataValues($dataArray);
        $this->_setDataValues($data);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    protected function _set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Identify type of objects which should be built with generated builder. Value can be one of self::TYPE_DATA_*.
     *
     * @return string
     * @throws \LogicException
     */
    protected function getDataType()
    {
        $dataType = $this->_getDataObjectType();
        if (is_subclass_of($dataType, '\Magento\Framework\Api\AbstractSimpleObject')) {
            return self::TYPE_DATA_OBJECT;
        } elseif (is_subclass_of($dataType, '\Magento\Framework\Model\AbstractExtensibleModel')) {
            return self::TYPE_DATA_MODEL;
        }

        $sourceClassPreference = $this->objectManagerConfig->getPreference($dataType);
        if (empty($sourceClassPreference)) {
            throw new \LogicException(
                "Preference for {$this->_getDataObjectType()} is not defined."
            );
        }

        if (is_subclass_of($sourceClassPreference, '\Magento\Framework\Api\AbstractSimpleObject')) {
            return self::TYPE_DATA_OBJECT;
        } elseif (is_subclass_of($sourceClassPreference, '\Magento\Framework\Model\AbstractExtensibleModel')) {
            return self::TYPE_DATA_MODEL;
        } else {
            throw new \LogicException(
                'Preference of ' . $this->_getDataObjectType()
                . ' must extend from AbstractSimpleObject or AbstractExtensibleModel'
            );
        }
    }

    /**
     * Template method used to configure the attribute codes for the custom attributes
     *
     * @return string[]
     */
    protected function getCustomAttributesCodes()
    {
        if (!is_null($this->customAttributesCodes)) {
            return $this->customAttributesCodes;
        }
        $attributeCodes = [];
        /** @var \Magento\Framework\Api\MetadataObjectInterface[] $customAttributesMetadata */
        $customAttributesMetadata = $this->metadataService
            ->getCustomAttributesMetadata($this->_getDataObjectType());
        if (is_array($customAttributesMetadata)) {
            foreach ($customAttributesMetadata as $attribute) {
                $attributeCodes[] = $attribute->getAttributeCode();
            }
        }
        $this->customAttributesCodes = $attributeCodes;
        return $attributeCodes;
    }

    /**
     * Initializes Data Object with the data from array
     *
     * @param array $data
     * @return $this
     */
    protected function _setDataValues(array $data)
    {
        $dataObjectMethods = get_class_methods($this->_getDataObjectType());
        foreach ($data as $key => $value) {
            /* First, verify is there any getter for the key on the Service Data Object */
            $camelCaseKey = \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);
            $possibleMethods = [
                'get' . $camelCaseKey,
                'is' . $camelCaseKey,
            ];
            if ($key === ExtensibleDataInterface::CUSTOM_ATTRIBUTES
                && is_array($data[$key])
                && !empty($data[$key])
            ) {
                foreach ($data[$key] as $customAttribute) {
                    $this->setCustomAttribute(
                        $customAttribute[AttributeInterface::ATTRIBUTE_CODE],
                        $customAttribute[AttributeInterface::VALUE]
                    );
                }
            } elseif ($methodName = array_intersect($possibleMethods, $dataObjectMethods)) {
                if (!is_array($value)) {
                    $this->data[$key] = $value;
                } else {
                    $this->setComplexValue($methodName[0], $key, $value);
                }
            } elseif (in_array($key, $this->getCustomAttributesCodes())) {
                $this->setCustomAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param string $methodName
     * @param string $key
     * @param array $value
     * @return $this
     */
    protected function setComplexValue($methodName, $key, array $value)
    {
        $returnType = $this->objectProcessor->getMethodReturnType($this->_getDataObjectType(), $methodName);
        if ($this->typeProcessor->isTypeSimple($returnType)) {
            $this->data[$key] = $value;
            return $this;
        }

        if ($this->typeProcessor->isArrayType($returnType)) {
            $type = $this->typeProcessor->getArrayItemType($returnType);
            $dataBuilder = $this->dataBuilderFactory->getDataBuilder($type);
            $objects = [];
            foreach ($value as $arrayElementData) {
                $objects[] = $dataBuilder->populateWithArray($arrayElementData)
                    ->create();
            }
            $this->data[$key] = $objects;
            return $this;
        }

        $dataBuilder = $this->dataBuilderFactory->getDataBuilder($returnType);
        $object = $dataBuilder->populateWithArray($value)
            ->create();
        $this->data[$key] = $object;
        return $this;
    }

    /**
     * Get data object type based on suffix
     *
     * @return string
     */
    protected function _getDataObjectType()
    {
        if ($this->modelClassInterface) {
            return $this->modelClassInterface;
        }
        $currentClass = get_class($this);
        $dataBuilderSuffix = 'DataBuilder';
        if (substr($currentClass, -strlen($dataBuilderSuffix)) === $dataBuilderSuffix) {
            $dataObjectType = substr($currentClass, 0, -strlen($dataBuilderSuffix)) . 'Interface';
        } else {
            $builderSuffix = 'Builder';
            $dataObjectType = substr($currentClass, 0, -strlen($builderSuffix));
        }
        return $dataObjectType;
    }
}
