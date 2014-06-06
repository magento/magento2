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
namespace Magento\Framework\Service\Data\Eav;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractObjectBuilder extends \Magento\Framework\Service\Data\AbstractObjectBuilder
{
    /**
     * @var AttributeValueBuilder
     */
    protected $_valueBuilder;

    /**
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder
    ) {
        $this->_valueBuilder = $valueBuilder;
        parent::__construct($objectFactory);
    }

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Service\Data\Eav\AttributeValue[] $attributes
     * @return $this
     * @throws \LogicException If array elements are not of AttributeValue type
     */
    public function setCustomAttributes(array $attributes)
    {
        $customAttributesCodes = $this->getCustomAttributesCodes();
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof AttributeValue) {
                throw new \LogicException('Custom Attribute array elements can only be type of AttributeValue');
            }
            if (in_array($attribute->getAttributeCode(), $customAttributesCodes)) {
                $this->_data[AbstractObject::CUSTOM_ATTRIBUTES_KEY][$attribute->getAttributeCode()] = $attribute;
            }
        }
        return $this;
    }

    /**
     * Set custom attribute value
     *
     * @param string $attributeCode
     * @param string|int|float|bool $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        $customAttributesCodes = $this->getCustomAttributesCodes();
        /* If key corresponds to custom attribute code, populate custom attributes */
        if (in_array($attributeCode, $customAttributesCodes)) {
            $valueObject = $this->_valueBuilder
                ->setAttributeCode($attributeCode)
                ->setValue($attributeValue)
                ->create();
            $this->_data[AbstractObject::CUSTOM_ATTRIBUTES_KEY][$attributeCode] = $valueObject;
        }
        return $this;
    }

    /**
     * Template method used to configure the attribute codes for the custom attributes
     *
     * @return string[]
     */
    public function getCustomAttributesCodes()
    {
        return array();
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
            $camelCaseKey = \Magento\Framework\Service\DataObjectConverter::snakeCaseToCamelCase($key);
            $possibleMethods = array(
                'get' . $camelCaseKey,
                'is' . $camelCaseKey
            );
            if ($key == AbstractObject::CUSTOM_ATTRIBUTES_KEY && !empty($data[$key])) {
                foreach ($data[$key] as $customAttribute) {
                    $this->setCustomAttribute(
                        $customAttribute[AttributeValue::ATTRIBUTE_CODE],
                        $customAttribute[AttributeValue::VALUE]
                    );
                }
            } elseif (array_intersect($possibleMethods, $dataObjectMethods)) {
                $this->_data[$key] = $value;
            } else {
                $this->setCustomAttribute($key, $value);
            }
        }
        return $this;
    }
}
