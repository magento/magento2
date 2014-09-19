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
namespace Magento\Tax\Service\V1\Data\QuoteDetails;

class Item extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE = 'code';

    const KEY_TYPE = 'type';

    const KEY_TAX_CLASS_KEY = 'tax_class_key';

    const KEY_UNIT_PRICE = 'unit_price';

    const KEY_QUANTITY = 'quantity';

    const KEY_TAX_INCLUDED = 'tax_included';

    const KEY_SHORT_DESCRIPTION = 'short_description';

    const KEY_DISCOUNT_AMOUNT = 'discount_amount';

    const KEY_PARENT_CODE = 'parent_code';

    const KEY_ASSOCIATED_ITEM_CODE = 'association_code';

    const KEY_TAX_CLASS_ID = 'tax_class_id';
    /**#@-*/

    /**
     * Get code (sku or shipping code)
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->_get(self::KEY_CODE);
    }

    /**
     * Get type (e.g., shipping, product, wee, gift wrapping, etc.)
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->_get(self::KEY_TYPE);
    }

    /**
     * Get tax class key
     *
     * @return \Magento\Tax\Service\V1\Data\TaxClassKey
     */
    public function getTaxClassKey()
    {
        return $this->_get(self::KEY_TAX_CLASS_KEY);
    }

    /**
     * Get unit price
     *
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->_get(self::KEY_UNIT_PRICE);
    }

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->_get(self::KEY_QUANTITY);
    }

    /**
     * Get indicate that if the tax is included in the unit price and row total
     *
     * @return bool
     */
    public function getTaxIncluded()
    {
        return $this->_get(self::KEY_TAX_INCLUDED);
    }

    /**
     * Get short description
     *
     * @return string|null
     */
    public function getShortDescription()
    {
        return $this->_get(self::KEY_SHORT_DESCRIPTION);
    }

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->_get(self::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * Get parent code if this item is a child, null if this is a top level item.
     *
     * @return string|null
     */
    public function getParentCode()
    {
        return $this->_get(self::KEY_PARENT_CODE);
    }

    /**
     * Get associated item code if this item is associated with another item, null otherwise
     *
     * @return mixed|null
     */
    public function getAssociatedItemCode()
    {
        return $this->_get(self::KEY_ASSOCIATED_ITEM_CODE);
    }

    /**
     * Get associated item tax class id
     *
     * @return int
     */
    public function getTaxClassId()
    {
        return $this->_get(self::KEY_TAX_CLASS_ID);
    }
}
