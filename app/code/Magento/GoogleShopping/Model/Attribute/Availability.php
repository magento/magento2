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
namespace Magento\GoogleShopping\Model\Attribute;

/**
 * Availability attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Availability extends \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
{
    /**
     * @var array
     */
    protected $_googleAvailabilityMap = array(0 => 'out of stock', 1 => 'in stock');

    /**
     * Set current attribute to entry (for specified product)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @return \Magento\Framework\Gdata\Gshopping\Entry
     */
    public function convertAttribute($product, $entry)
    {
        $value = $this->_googleAvailabilityMap[(int)$product->isSalable()];
        $this->_setAttribute($entry, 'availability', self::ATTRIBUTE_TYPE_TEXT, $value);
        return $entry;
    }
}
