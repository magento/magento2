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

/**
 * Base Builder Class for extensible data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class ExtensibleObjectBuilder extends AbstractSimpleObjectBuilder implements ExtensibleDataBuilderInterface
{
    /**
     * @var AttributeValueBuilder
     */
    protected $attributeValueBuilder;

    /**
     * @var MetadataServiceInterface
     */
    protected $metadataService;

    /**
     * @var string[]
     */
    protected $customAttributesCodes = null;

    /**
     * @var string
     */
    protected $modelClassInterface;

    /**
     * @param \Magento\Framework\Api\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param string|null $modelClassInterface
     */
    public function __construct(
        \Magento\Framework\Api\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        $modelClassInterface = null
    ) {
        $this->attributeValueBuilder = $valueBuilder;
        $this->metadataService = $metadataService;
        $this->modelClassInterface = $modelClassInterface;
        parent::__construct($objectFactory);
    }

    /**
     * {@inheritdoc}
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
                $this->data[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY][$attributeCode] = $attribute;
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $customAttributesCodes = $this->getCustomAttributesCodes();
        /* If key corresponds to custom attribute code, populate custom attributes */
        if (in_array($attributeCode, $customAttributesCodes)) {
            $attribute = $this->attributeValueBuilder
                ->setAttributeCode($attributeCode)
                ->setValue($attributeValue)
                ->create();
            $this->data[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY][$attributeCode] = $attribute;
        }
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
        $customAttributesMetadata = $this->metadataService->getCustomAttributesMetadata($this->_getDataObjectType());
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
        return $this->_set($key, $value);
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
            } elseif (array_intersect($possibleMethods, $dataObjectMethods)) {
                $this->data[$key] = $value;
            } else {
                $this->setCustomAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getDataObjectType()
    {
        return $this->modelClassInterface ?: parent::_getDataObjectType();
    }
}
