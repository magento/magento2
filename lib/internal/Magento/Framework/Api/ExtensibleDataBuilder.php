<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\ExtensibleDataBuilderInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\ObjectManager;

/**
 * Implementation for \Magento\Framework\Api\ExtensibleDataBuilderInterface.
 */
class ExtensibleDataBuilder implements ExtensibleDataBuilderInterface
{
    /**
     * @var string
     */
    protected $modelClassInterface;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

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
     * Initialize the builder
     *
     * @param ObjectManager $objectManager
     * @param MetadataServiceInterface $metadataService
     * @param \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory
     * @param string $modelClassInterface
     */
    public function __construct(
        ObjectManager $objectManager,
        MetadataServiceInterface $metadataService,
        \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory,
        $modelClassInterface
    ) {
        $this->objectManager = $objectManager;
        $this->metadataService = $metadataService;
        $this->modelClassInterface = $modelClassInterface;
        $this->objectProcessor = $objectProcessor;
        $this->attributeValueBuilder = $attributeValueBuilder;
        $this->typeProcessor = $typeProcessor;
        $this->dataBuilderFactory = $dataBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $attribute = $this->attributeValueBuilder
            ->setAttributeCode($attributeCode)
            ->setValue($attributeValue)
            ->create();
        // Store as an associative array for easier lookup and processing
        $this->data[AbstractExtensibleModel::CUSTOM_ATTRIBUTES_KEY][$attributeCode] = $attribute;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttributes(array $attributes)
    {
        /** @var \Magento\Framework\Api\AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $this->data[AbstractExtensibleModel::CUSTOM_ATTRIBUTES_KEY][$attribute->getAttributeCode()] = $attribute;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->objectManager->create(
            $this->modelClassInterface,
            ['data' => $this->data]
        );
    }

    /**
     * Populates the fields with data from the array.
     *
     * Keys for the map are snake_case attribute/field names.
     *
     * @param array $data
     * @return $this
     */
    public function populateWithArray(array $data)
    {
        $this->data = array();
        $this->_setDataValues($data);
        return $this;
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
            ->getCustomAttributesMetadata($this->modelClassInterface);
        if (is_array($customAttributesMetadata)) {
            foreach ($customAttributesMetadata as $attribute) {
                $attributeCodes[] = $attribute->getAttributeCode();
            }
        }
        $this->customAttributesCodes = $attributeCodes;
        return $attributeCodes;
    }

    /**
     * Set data item value.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     * @deprecated This method should not be used in the client code and will be removed after Service Layer refactoring
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Initializes Data Object with the data from array
     *
     * @param array $data
     * @return $this
     */
    protected function _setDataValues(array $data)
    {
        $dataObjectMethods = get_class_methods($this->modelClassInterface);
        foreach ($data as $key => $value) {
            /* First, verify is there any getter for the key on the Service Data Object */
            $camelCaseKey = \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);
            $possibleMethods = array(
                'get' . $camelCaseKey,
                'is' . $camelCaseKey
            );
            if ($key == AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY
                && is_array($data[$key])
                && !empty($data[$key])
            ) {
                foreach ($data[$key] as $customAttribute) {
                    $this->setCustomAttribute(
                        $customAttribute[AttributeValue::ATTRIBUTE_CODE],
                        $customAttribute[AttributeValue::VALUE]
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
        $returnType = $this->objectProcessor->getMethodReturnType($this->modelClassInterface, $methodName);
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
}
