<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface TaxDetailsItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE = 'code';

    const KEY_TYPE = 'type';

    const KEY_TAX_PERCENT = 'tax_percent';

    const KEY_PRICE = 'price';

    const KEY_PRICE_INCL_TAX = 'price_incl_tax';

    const KEY_ROW_TOTAL = 'row_total';

    const KEY_ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';

    const KEY_ROW_TAX = 'row_tax';

    const KEY_TAXABLE_AMOUNT = 'taxable_amount';

    const KEY_DISCOUNT_AMOUNT = 'discount_amount';

    const KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';

    const KEY_APPLIED_TAXES = 'applied_taxes';

    const KEY_ASSOCIATED_ITEM_CODE = 'associated_item_code';
    /**#@-*/

    /**
     * Get code (sku or shipping code)
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get type (shipping, product, weee, gift wrapping, etc
     *
     * @return string|null
     */
    public function getType();

    /**
     * Get tax_percent
     *
     * @return float
     */
    public function getTaxPercent();

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Get price including tax
     *
     * @return float
     */
    public function getPriceInclTax();

    /**
     * Get row total
     *
     * @return float
     */
    public function getRowTotal();

    /**
     * Get row total including tax
     *
     * @return float
     */
    public function getRowTotalInclTax();

    /**
     * Get row tax amount
     *
     * @return float
     */
    public function getRowTax();

    /**
     * Get taxable amount
     *
     * @return float
     */
    public function getTaxableAmount();

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Get discount tax compensation amount
     *
     * @return float
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[] | null
     */
    public function getAppliedTaxes();

    /**
     * Return associated item code if this item is associated with another item, null otherwise
     *
     * @return mixed|null
     */
    public function getAssociatedItemCode();
}
