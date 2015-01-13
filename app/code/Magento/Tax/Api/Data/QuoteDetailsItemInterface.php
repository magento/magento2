<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

interface QuoteDetailsItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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

    const KEY_ASSOCIATED_ITEM_CODE = 'associated_item_code';

    const KEY_TAX_CLASS_ID = 'tax_class_id';
    /**#@-*/

    /**
     * Get code (sku or shipping code)
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get type (e.g., shipping, product, wee, gift wrapping, etc.)
     *
     * @return string|null
     */
    public function getType();

    /**
     * Get tax class key
     *
     * @return \Magento\Tax\Api\Data\TaxClassKeyInterface
     */
    public function getTaxClassKey();

    /**
     * Get unit price
     *
     * @return float
     */
    public function getUnitPrice();

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Get indicate that if the tax is included in the unit price and row total
     *
     * @return bool
     */
    public function getTaxIncluded();

    /**
     * Get short description
     *
     * @return string|null
     */
    public function getShortDescription();

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Get parent code if this item is a child, null if this is a top level item.
     *
     * @return string|null
     */
    public function getParentCode();

    /**
     * Get associated item code if this item is associated with another item, null otherwise
     *
     * @return mixed|null
     */
    public function getAssociatedItemCode();

    /**
     * Get associated item tax class id
     *
     * @return int
     */
    public function getTaxClassId();
}
