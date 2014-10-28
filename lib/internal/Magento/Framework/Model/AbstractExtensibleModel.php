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

namespace Magento\Framework\Model;

use Magento\Framework\Service\Data\MetadataServiceInterface;

/**
 * Abstract model with custom attributes support.
 *
 * This class defines basic data structure of how custom attributes are stored in an ExtensibleModel.
 * Implementations may choose to process custom attributes as their persistence requires them to.
 */
abstract class AbstractExtensibleModel extends AbstractModel implements \Magento\Framework\Api\ExtensibleDataInterface
{
    const CUSTOM_ATTRIBUTES_KEY = 'custom_attributes';

    /**
     * @var MetadataServiceInterface
     */
    protected $metadataService;

    /**
     * @var string[]
     */
    protected $customAttributesCodes = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MetadataServiceInterface $metadataService
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MetadataServiceInterface $metadataService,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->metadataService = $metadataService;
        $data = $this->filterCustomAttributes($data);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Verify custom attributes set on $data and unset if not a valid custom attribute
     *
     * @param array $data
     * @return array processed data
     */
    protected function filterCustomAttributes($data)
    {
        if (empty($data[self::CUSTOM_ATTRIBUTES_KEY])) {
            return $data;
        }
        $customAttributesCodes = $this->getCustomAttributesCodes();
        $data[self::CUSTOM_ATTRIBUTES_KEY] =
            array_intersect_key($data[self::CUSTOM_ATTRIBUTES_KEY], $customAttributesCodes);
        return $data;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Service\Data\AttributeValue[]|null
     */
    public function getCustomAttributes()
    {
        // Returning as a sequential array (instead of stored associative array) to be compatible with the interface
        return isset($this->_data[self::CUSTOM_ATTRIBUTES_KEY])
            ? array_values($this->_data[self::CUSTOM_ATTRIBUTES_KEY])
            : [];
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Service\Data\AttributeValue|null null if the attribute has not been set
     */
    public function getCustomAttribute($attributeCode)
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES_KEY][$attributeCode])
            ? $this->_data[self::CUSTOM_ATTRIBUTES_KEY][$attributeCode]
            : null;
    }

    /**
     * Overwrite data in the object.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array  $key
     * @param mixed         $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if ($key == self::CUSTOM_ATTRIBUTES_KEY) {
            throw new \LogicException("Custom attributes must be set only using setCustomAttribute() method.");
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
        if ($key == self::CUSTOM_ATTRIBUTES_KEY) {
            throw new \LogicException("Custom attributes array should be retrieved via getCustomAttributes() only.");
        } else if ($key == '') {
            /** Represent model data and custom attributes as a flat array */
            $data = array_merge($this->_data, $this->getCustomAttributes());
            unset($data[self::CUSTOM_ATTRIBUTES_KEY]);
        } else {
            $data = parent::getData($key, $index);
            if ($data === null) {
                /** Try to find necessary data in custom attributes */
                $data = parent::getData(self::CUSTOM_ATTRIBUTES_KEY . "/{$key}", $index);
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
            /** @var $attribute \Magento\Framework\Service\Data\MetadataObjectInterface */
            foreach ($customAttributesMetadata as $attribute) {
                // Create a map for easier processing
                $attributeCodes[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
            }
        }
        $this->customAttributesCodes = $attributeCodes;
        return $attributeCodes;
    }
}
