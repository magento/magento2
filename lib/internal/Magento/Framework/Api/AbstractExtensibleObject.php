<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use \Magento\Framework\Api\AttributeValueFactory;

/**
 * Base Class for extensible data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * TODO: This class can be split into Custom attribute and Extension attribute implementation classes
 *
 * @api
 */
abstract class AbstractExtensibleObject extends AbstractSimpleObject implements CustomAttributesDataInterface
{
    /**
     * Array key for custom attributes
     */
    const CUSTOM_ATTRIBUTES_KEY = 'custom_attributes';

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extensionFactory;

    /**
     * @var AttributeValueFactory
     */
    protected $attributeValueFactory;

    /**
     * @var string[]
     */
    protected $customAttributesCodes;

    /**
     * Initialize internal storage
     *
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $attributeValueFactory,
        $data = []
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->attributeValueFactory = $attributeValueFactory;
        parent::__construct($data);
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null null if the attribute has not been set
     */
    public function getCustomAttribute($attributeCode)
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES])
            && isset($this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode])
                ? $this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode]
                : null;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes()
    {
        return $this->_data[self::CUSTOM_ATTRIBUTES] ?? [];
    }

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function setCustomAttributes(array $attributes)
    {
        $customAttributesCodes = $this->getCustomAttributesCodes();
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof AttributeValue) {
                throw new \LogicException('Custom Attribute array elements can only be type of AttributeValue');
            }
            $attributeCode = $attribute->getAttributeCode();
            if (in_array($attributeCode, $customAttributesCodes)) {
                $this->_data[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY][$attributeCode] = $attribute;
            }
        }
        return $this;
    }

    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $customAttributesCodes = $this->getCustomAttributesCodes();
        /* If key corresponds to custom attribute code, populate custom attributes */
        if (in_array($attributeCode, $customAttributesCodes)) {
            /** @var AttributeValue $attribute */
            $attribute = $this->attributeValueFactory->create();
            $attribute->setAttributeCode($attributeCode)
                ->setValue($attributeValue);
            $this->_data[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY][$attributeCode] = $attribute;
        }
        return $this;
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
        return $this->customAttributesCodes ?? [];
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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    protected function _getExtensionAttributes()
    {
        return $this->_get(self::EXTENSION_ATTRIBUTES_KEY);
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
}
