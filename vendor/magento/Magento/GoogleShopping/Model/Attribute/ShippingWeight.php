<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Sipping weight attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model\Attribute;

class ShippingWeight extends \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
{
    /**
     * Default weight unit
     *
     * @var string
     */
    const WEIGHT_UNIT = 'lb';

    /**
     * Set current attribute to entry (for specified product)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @return \Magento\Framework\Gdata\Gshopping\Entry
     */
    public function convertAttribute($product, $entry)
    {
        $mapValue = $this->getProductAttributeValue($product);
        if (!$mapValue) {
            $weight = $this->getGroupAttributeWeight();
            $mapValue = $weight ? $weight->getProductAttributeValue($product) : null;
        }

        if ($mapValue) {
            $this->_setAttribute($entry, 'shipping_weight', self::ATTRIBUTE_TYPE_FLOAT, $mapValue, self::WEIGHT_UNIT);
        }

        return $entry;
    }
}
