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

namespace Magento\Tax\Service\V1\Data\OrderTaxDetails;

class Item extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_TYPE = 'type';

    const KEY_ITEM_ID = 'item_id';

    const KEY_ASSOCIATED_ITEM_ID = 'associated_item_id';

    const KEY_APPLIED_TAXES = 'applied_taxes';
    /**#@-*/

    /**
     * Get type (shipping, product, weee, gift wrapping, etc)
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->_get(self::KEY_TYPE);
    }

    /**
     * Return item id if this item is a product
     *
     * @return int|null
     */
    public function getItemId()
    {
        return $this->_get(self::KEY_ITEM_ID);
    }

    /**
     * Return associated item id if this item is associated with another item, null otherwise
     *
     * @return int|null
     */
    public function getAssociatedItemId()
    {
        return $this->_get(self::KEY_ASSOCIATED_ITEM_ID);
    }

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTax[]|null
     */
    public function getAppliedTaxes()
    {
        return $this->_get(self::KEY_APPLIED_TAXES);
    }
}
