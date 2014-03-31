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
 * Class EAV AbstractObject
 */
abstract class AbstractObject extends \Magento\Service\Data\AbstractObject
{
    /**
     * Array key for custom attributes
     */
    const CUSTOM_ATTRIBUTES_KEY = 'custom_attributes';

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return int|string|bool|float The attribute value. Null if the attribute has not been set
     */
    public function getCustomAttribute($attributeCode)
    {
        if (isset(
            $this->_data[self::CUSTOM_ATTRIBUTES_KEY]
        ) && array_key_exists(
            $attributeCode,
            $this->_data[self::CUSTOM_ATTRIBUTES_KEY]
        )
        ) {
            return $this->_data[self::CUSTOM_ATTRIBUTES_KEY][$attributeCode];
        } else {
            return null;
        }
    }

    /**
     * Retrieve custom attributes values as an associative array.
     *
     * @return string[]
     */
    public function getCustomAttributes()
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES_KEY]) ? $this->_data[self::CUSTOM_ATTRIBUTES_KEY] : array();
    }
}
