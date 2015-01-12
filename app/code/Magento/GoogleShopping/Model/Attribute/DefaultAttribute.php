<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Default attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model\Attribute;

class DefaultAttribute extends \Magento\GoogleShopping\Model\Attribute
{
    /**
     * Google Content attribute types
     *
     * @var string
     */
    const ATTRIBUTE_TYPE_TEXT = 'text';

    const ATTRIBUTE_TYPE_INT = 'int';

    const ATTRIBUTE_TYPE_FLOAT = 'float';

    const ATTRIBUTE_TYPE_URL = 'url';

    /**
     * Set current attribute to entry (for specified product)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @return \Magento\Framework\Gdata\Gshopping\Entry
     */
    public function convertAttribute($product, $entry)
    {
        if (is_null($this->getName())) {
            return $entry;
        }
        $productAttribute = $this->_gsProduct->getProductAttribute($product, $this->getAttributeId());
        $type = $this->getGcontentAttributeType($productAttribute);
        $value = $this->getProductAttributeValue($product);

        if (!is_null($value)) {
            $entry = $this->_setAttribute($entry, $this->getName(), $type, $value);
        }
        return $entry;
    }

    /**
     * Get current attribute value for specified product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return null|string
     */
    public function getProductAttributeValue($product)
    {
        if (is_null($this->getAttributeId())) {
            return null;
        }

        $productAttribute = $this->_gsProduct->getProductAttribute($product, $this->getAttributeId());
        if (is_null($productAttribute)) {
            return null;
        }

        if ($productAttribute->getFrontendInput() == 'date' || $productAttribute->getBackendType() == 'date') {
            $value = $product->getData($productAttribute->getAttributeCode());
            if (empty($value) || !\Zend_Date::isDate($value, \Zend_Date::ISO_8601)) {
                return null;
            }
            $date = new \Magento\Framework\Stdlib\DateTime\Date($value, \Zend_Date::ISO_8601);
            $value = $date->toString(\Zend_Date::ATOM);
        } else {
            $value = $productAttribute->getFrontend()->getValue($product);
        }
        return $value;
    }

    /**
     * Return Google Content Attribute Type By Product Attribute
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @return string Google Content Attribute Type
     */
    public function getGcontentAttributeType($attribute)
    {
        $typesMapping = ['price' => self::ATTRIBUTE_TYPE_FLOAT, 'decimal' => self::ATTRIBUTE_TYPE_INT];
        if (isset($typesMapping[$attribute->getFrontendInput()])) {
            return $typesMapping[$attribute->getFrontendInput()];
        } elseif (isset($typesMapping[$attribute->getBackendType()])) {
            return $typesMapping[$attribute->getBackendType()];
        } else {
            return self::ATTRIBUTE_TYPE_TEXT;
        }
    }

    /**
     * Insert/update attribute in the entry
     *
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @param string $name
     * @param string $type
     * @param string $value
     * @param string $unit
     * @return \Magento\Framework\Gdata\Gshopping\Entry
     */
    protected function _setAttribute($entry, $name, $type = self::ATTRIBUTE_TYPE_TEXT, $value = '', $unit = null)
    {
        if (is_object($value) || (string)$value != $value) {
            throw new \Magento\Framework\Model\Exception(
                __(
                    'Please correct the attribute "%1" type for Google Shopping. The product with this attribute hasn\'t been updated in Google Content.',
                    $name
                )
            );
        }
        $attribute = $entry->getContentAttributeByName($name);
        if ($attribute instanceof \Magento\Framework\Gdata\Gshopping\Extension\Attribute) {
            $attribute->text = (string)$value;
            $attribute->type = $type;
            if (!is_null($unit)) {
                $attribute->unit = $unit;
            }
        } else {
            $entry->addContentAttribute($name, $value, $type, $unit);
        }

        return $entry;
    }
}
