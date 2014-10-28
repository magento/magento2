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
        $availableConditions = array(self::CONDITION_NEW, self::CONDITION_USED, self::CONDITION_REFURBISHED);

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
