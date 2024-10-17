<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Abstract model with custom attributes support.
 *
 * This class defines basic data structure of how custom attributes are stored in an ExtensibleModel.
 * Implementations may choose to process custom attributes as their persistence requires them to.
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractExtensibleModel extends AbstractModel implements
    \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**
     * @var ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesInterface
     */
    protected $extensionAttributes;

    /**
     * @var AttributeValueFactory
     */
    protected $customAttributeFactory;

    /**
     * @var string[]
     */
    protected $customAttributesCodes = null;

    /**
     * @var bool
     */
    protected $customAttributesChanged = false;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->extensionAttributesFactory = $extensionFactory;
        $this->customAttributeFactory = $customAttributeFactory;
        $data = $this->filterCustomAttributes($data);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
        if (isset($data[self::EXTENSION_ATTRIBUTES_KEY]) && is_array($data[self::EXTENSION_ATTRIBUTES_KEY])) {
            $this->populateExtensionAttributes($data[self::EXTENSION_ATTRIBUTES_KEY]);
        }
    }

    /**
     * Convert the custom attributes array format to map format
     *
     * The method \Magento\Framework\Reflection\DataObjectProcessor::buildOutputDataArray generates a custom_attributes
     * array representation where each custom attribute is a sub-array with a `attribute_code and value key.
     * This method maps such an array to the plain code => value map format exprected by filterCustomAttributes
     *
     * @param array[] $customAttributesData
     * @return array
     */
    private function flattenCustomAttributesArrayToMap(array $customAttributesData): array
    {
        return array_reduce(
            $customAttributesData,
            function (array $acc, array $customAttribute): array {
                if (!isset($customAttribute['value'])
                    && isset($customAttribute['selected_options'])
                    && is_array($customAttribute['selected_options'])
                ) {
                    $customAttribute['value'] = implode(
                        ',',
                        array_map(
                            function (array $option): string {
                                return (string)$option['value'];
                            },
                            $customAttribute['selected_options']
                        )
                    );
                }
                $acc[$customAttribute['attribute_code']] = $customAttribute['value'];
                return $acc;
            },
            []
        );
    }

    /**
     * Verify custom attributes set on $data and unset if not a valid custom attribute
     *
     * @param array $data
     * @return array processed data
     */
    protected function filterCustomAttributes($data)
    {
        if (empty($data[self::CUSTOM_ATTRIBUTES])) {
            return $data;
        }
        if (isset($data[self::CUSTOM_ATTRIBUTES][0])) {
            $data[self::CUSTOM_ATTRIBUTES] = $this->flattenCustomAttributesArrayToMap($data[self::CUSTOM_ATTRIBUTES]);
        }
        $customAttributesCodes = $this->getCustomAttributesCodes();
        $data[self::CUSTOM_ATTRIBUTES] = array_intersect_key(
            (array) $data[self::CUSTOM_ATTRIBUTES],
            array_flip($customAttributesCodes)
        );
        foreach ($data[self::CUSTOM_ATTRIBUTES] as $code => $value) {
            if (!($value instanceof \Magento\Framework\Api\AttributeInterface)) {
                $data[self::CUSTOM_ATTRIBUTES][$code] = $this->customAttributeFactory->create()
                    ->setAttributeCode($code)
                    ->setValue($value);
            }
        }
        return $data;
    }

    /**
     * Initialize customAttributes based on existing data
     */
    protected function initializeCustomAttributes()
    {
        if (!isset($this->_data[self::CUSTOM_ATTRIBUTES]) || $this->customAttributesChanged) {
            if (!empty($this->_data[self::CUSTOM_ATTRIBUTES])) {
                $customAttributes = $this->_data[self::CUSTOM_ATTRIBUTES];
            } else {
                $customAttributes = [];
            }
            $customAttributeCodes = $this->getCustomAttributesCodes();

            foreach ($customAttributeCodes as $customAttributeCode) {
                if (isset($this->_data[self::CUSTOM_ATTRIBUTES][$customAttributeCode])) {
                    $customAttribute = $this->customAttributeFactory->create()
                        ->setAttributeCode($customAttributeCode)
                        ->setValue($this->_data[self::CUSTOM_ATTRIBUTES][$customAttributeCode]->getValue());
                    $customAttributes[$customAttributeCode] = $customAttribute;
                } elseif (isset($this->_data[$customAttributeCode])) {
                    $customAttribute = $this->customAttributeFactory->create()
                        ->setAttributeCode($customAttributeCode)
                        ->setValue($this->_data[$customAttributeCode]);
                    $customAttributes[$customAttributeCode] = $customAttribute;
                }
            }
            $this->_data[self::CUSTOM_ATTRIBUTES] = $customAttributes;
            $this->customAttributesChanged = false;
        }
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes()
    {
        $this->initializeCustomAttributes();
        // Returning as a sequential array (instead of stored associative array) to be compatible with the interface
        return array_values($this->_data[self::CUSTOM_ATTRIBUTES]);
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null null if the attribute has not been set
     */
    public function getCustomAttribute($attributeCode)
    {
        $this->initializeCustomAttributes();
        return $this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setCustomAttributes(array $attributes)
    {
        return $this->setData(self::CUSTOM_ATTRIBUTES, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $customAttributesCodes = $this->getCustomAttributesCodes();
        /* If key corresponds to custom attribute code, populate custom attributes */
        if (in_array($attributeCode, $customAttributesCodes)) {
            $attribute = $this->customAttributeFactory->create();
            $attribute->setAttributeCode($attributeCode)
                ->setValue($attributeValue);
            $this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode] = $attribute;
        }
        return $this;
    }

    /**
     * {@inheritdoc} Added custom attributes support.
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $key = $this->filterCustomAttributes($key);
        } elseif ($key === self::CUSTOM_ATTRIBUTES) {
            $filteredData = $this->filterCustomAttributes([self::CUSTOM_ATTRIBUTES => $value]);
            $value = $filteredData[self::CUSTOM_ATTRIBUTES];
        }
        $this->customAttributesChanged = true;
        parent::setData($key, $value);
        return $this;
    }

    /**
     * {@inheritdoc} Unset customAttributesChanged flag
     *
     * @param null|string|array $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if (is_string($key) && isset($this->_data[self::CUSTOM_ATTRIBUTES][$key])) {
            unset($this->_data[self::CUSTOM_ATTRIBUTES][$key]);
        }
        return parent::unsetData($key);
    }

    /**
     * Convert custom values if necessary
     *
     * @param array $customAttributes
     * @return void
     */
    protected function convertCustomAttributeValues(array &$customAttributes)
    {
        foreach ($customAttributes as $attributeCode => $attributeValue) {
            if ($attributeValue instanceof \Magento\Framework\Api\AttributeValue) {
                $customAttributes[$attributeCode] = $attributeValue->getValue();
            }
        }
    }

    /**
     * Object data getter
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     * It is possible to use keys like a/b/c for access nested array data
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member. If data is the string - it will be explode
     * by new line character and converted to array.
     *
     * In addition to parent implementation custom attributes support is added.
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        if ($key === self::CUSTOM_ATTRIBUTES) {
            throw new \LogicException("Custom attributes array should be retrieved via getCustomAttributes() only.");
        } elseif ($key === '') {
            /** Represent model data and custom attributes as a flat array */
            $customAttributes = isset($this->_data[self::CUSTOM_ATTRIBUTES])
                ? $this->_data[self::CUSTOM_ATTRIBUTES]
                : [];
            $this->convertCustomAttributeValues($customAttributes);
            $data = array_merge($this->_data, $customAttributes);
            unset($data[self::CUSTOM_ATTRIBUTES]);
        } else {
            $data = parent::getData($key, $index);
            if ($data === null) {
                /** Try to find necessary data in custom attributes */
                $data = isset($this->_data[self::CUSTOM_ATTRIBUTES][$key])
                    ? $this->_data[self::CUSTOM_ATTRIBUTES][$key]
                    : null;
                if ($data instanceof \Magento\Framework\Api\AttributeValue) {
                    $data = $data->getValue();
                }
                if (null !== $index && isset($data[$index])) {
                    return $data[$index];
                }
            }
        }

        return $data;
    }

    /**
     * Get a list of custom attribute codes.
     *
     * By default, entity can be extended only using extension attributes functionality.
     *
     * @return string[]
     */
    protected function getCustomAttributesCodes()
    {
        return [];
    }

    /**
     * Receive a list of EAV attributes using provided metadata service.
     *
     * Can be used in child classes, which represent EAV entities.
     *
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @return string[]
     */
    protected function getEavAttributesCodes(\Magento\Framework\Api\MetadataServiceInterface $metadataService)
    {
        $attributeCodes = [];
        $customAttributesMetadata = $metadataService->getCustomAttributesMetadata(get_class($this));
        if (is_array($customAttributesMetadata)) {
            /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
            foreach ($customAttributesMetadata as $attribute) {
                $attributeCodes[] = $attribute->getAttributeCode();
            }
        }
        return $attributeCodes;
    }

    /**
     * Identifier setter
     *
     * @param mixed $value
     * @return $this
     */
    public function setId($value)
    {
        parent::setId($value);
        return $this->setData('id', $value);
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Framework\Api\ExtensionAttributesInterface $extensionAttributes
     * @return $this
     */
    protected function _setExtensionAttributes(\Magento\Framework\Api\ExtensionAttributesInterface $extensionAttributes)
    {
        $this->_data[self::EXTENSION_ATTRIBUTES_KEY] = $extensionAttributes;
        return $this;
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    protected function _getExtensionAttributes()
    {
        if (!$this->getData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->populateExtensionAttributes([]);
        }
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * Instantiate extension attributes object and populate it with the provided data.
     *
     * @param array $extensionAttributesData
     * @return void
     */
    private function populateExtensionAttributes(array $extensionAttributesData = [])
    {
        $extensionAttributes = $this->extensionAttributesFactory->create(get_class($this), $extensionAttributesData);
        $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheritdoc
     * @since 100.0.11
     */
    public function __sleep()
    {
        return array_diff(parent::__sleep(), ['extensionAttributesFactory', 'customAttributeFactory']);
    }

    /**
     * @inheritdoc
     * @since 100.0.11
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->extensionAttributesFactory = $objectManager->get(ExtensionAttributesFactory::class);
        $this->customAttributeFactory = $objectManager->get(AttributeValueFactory::class);
    }
}
