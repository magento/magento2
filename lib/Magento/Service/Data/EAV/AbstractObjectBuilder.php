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
namespace Magento\Service\Data\EAV;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractObjectBuilder extends \Magento\Service\Data\AbstractObjectBuilder
{
    /**
     * Set array of custom attributes
     *
     * @param array $attributes
     * @return $this
     */
    public function setCustomAttributes($attributes)
    {
        foreach ($attributes as $attributeCode => $attributeValue) {
            $this->setCustomAttribute($attributeCode, $attributeValue);
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
        if (in_array($attributeCode, $this->getCustomAttributesCodes())) {
            $this->_data[AbstractObject::CUSTOM_ATTRIBUTES_KEY][$attributeCode] = $attributeValue;
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
        $customAttributesCodes = $this->getCustomAttributesCodes();
        foreach ($data as $key => $value) {
            /* First, verify is there any getter for the key on the Service Data Object */
            $possibleMethods = array(
                'get' . $this->_snakeCaseToCamelCase($key),
                'is' . $this->_snakeCaseToCamelCase($key)
            );
            if (array_intersect($possibleMethods, $dataObjectMethods)) {
                $this->_data[$key] = $value;
            } elseif (in_array($key, $customAttributesCodes)) {
                /* If key corresponds to custom attribute code, populate custom attributes */
                $this->_data[AbstractObject::CUSTOM_ATTRIBUTES_KEY][$key] = $value;
            }
        }
        return $this;
    }
}
