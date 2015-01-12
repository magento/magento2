<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\MetadataServiceInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * Abstract model with custom attributes support.
 *
 * This class defines basic data structure of how custom attributes are stored in an ExtensibleModel.
 * Implementations may choose to process custom attributes as their persistence requires them to.
 */
abstract class AbstractExtensibleModel extends AbstractModel implements ExtensibleDataInterface
{
    /**
     * @var MetadataServiceInterface
     */
    protected $metadataService;

    /**
     * @var AttributeDataBuilder
     */
    protected $customAttributeBuilder;

    /**
     * @var string[]
     */
    protected $customAttributesCodes = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->metadataService = $metadataService;
        $this->customAttributeBuilder = $customAttributeBuilder;
        $data = $this->filterCustomAttributes($data);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
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
        $customAttributesCodes = $this->getCustomAttributesCodes();
        $data[self::CUSTOM_ATTRIBUTES] = array_intersect_key(
            (array)$data[self::CUSTOM_ATTRIBUTES],
            $customAttributesCodes
        );
        foreach ($data[self::CUSTOM_ATTRIBUTES] as $code => $value) {
            if (!($value instanceof \Magento\Framework\Api\AttributeInterface)) {
                $data[self::CUSTOM_ATTRIBUTES][$code] = $this->customAttributeBuilder
                    ->setAttributeCode($code)
                    ->setValue($value)
                    ->create();
            }
        }
        return $data;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes()
    {
        // Returning as a sequential array (instead of stored associative array) to be compatible with the interface
        return isset($this->_data[self::CUSTOM_ATTRIBUTES])
            ? array_values($this->_data[self::CUSTOM_ATTRIBUTES])
            : [];
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null null if the attribute has not been set
     */
    public function getCustomAttribute($attributeCode)
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode])
            ? $this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode]
            : null;
    }

    /**
     * {@inheritdoc}
     *
     * Added custom attributes support.
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $key = $this->filterCustomAttributes($key);
        } elseif ($key == self::CUSTOM_ATTRIBUTES) {
            $filteredData = $this->filterCustomAttributes([self::CUSTOM_ATTRIBUTES => $value]);
            $value = $filteredData[self::CUSTOM_ATTRIBUTES];
        }
        return parent::setData($key, $value);
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
     * @param string     $key
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
            $data = array_merge($this->_data, $customAttributes);
            unset($data[self::CUSTOM_ATTRIBUTES]);
        } else {
            $data = parent::getData($key, $index);
            if ($data === null) {
                /** Try to find necessary data in custom attributes */
                $data = parent::getData(self::CUSTOM_ATTRIBUTES . "/{$key}", $index);
            }
        }
        return $data;
    }

    /**
     * Fetch all custom attributes for the given extensible model
     * //TODO : check if the custom attribute is already defined as a getter on the data interface
     *
     * @return string[]
     */
    protected function getCustomAttributesCodes()
    {
        if (!is_null($this->customAttributesCodes)) {
            return $this->customAttributesCodes;
        }
        $attributeCodes = [];
        $customAttributesMetadata = $this->metadataService->getCustomAttributesMetadata(get_class($this));
        if (is_array($customAttributesMetadata)) {
            /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
            foreach ($customAttributesMetadata as $attribute) {
                // Create a map for easier processing
                $attributeCodes[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
            }
        }
        $this->customAttributesCodes = $attributeCodes;
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
}
