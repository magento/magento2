<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Condition attribute's model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model\Attribute;

class Condition extends \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
{
    /**
     * Available condition values
     *
     * @var string
     */
    const CONDITION_NEW = 'new';

    const CONDITION_USED = 'used';

    const CONDITION_REFURBISHED = 'refurbished';

    /**
     * Set current attribute to entry (for specified product)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @return \Magento\Framework\Gdata\Gshopping\Entry
     */
    public function convertAttribute($product, $entry)
    {
        $availableConditions = [self::CONDITION_NEW, self::CONDITION_USED, self::CONDITION_REFURBISHED];

        $mapValue = $this->getProductAttributeValue($product);
        $mapValue = !is_null($mapValue) ? mb_convert_case($mapValue, MB_CASE_LOWER) : $mapValue;
        if (!is_null($mapValue) && in_array($mapValue, $availableConditions)) {
            $condition = $mapValue;
        } else {
            $condition = self::CONDITION_NEW;
        }

        return $this->_setAttribute($entry, 'condition', self::ATTRIBUTE_TYPE_TEXT, $condition);
    }
}
