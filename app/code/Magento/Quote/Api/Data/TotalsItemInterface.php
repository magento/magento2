<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface TotalsItemInterface
 * @api
 */
interface TotalsItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */

    /**
     * Item id.
     */
    const KEY_ITEM_ID = 'item_id';

    /**
     * Price.
     */
    const KEY_PRICE = 'price';

    /**
     * Base price.
     */
    const KEY_BASE_PRICE = 'base_price';

    /**
     * Quantity.
     */
    const KEY_QTY = 'qty';

    /**
     * Row total.
     */
    const KEY_ROW_TOTAL = 'row_total';

    /**
     * Base row total.
     */
    const KEY_BASE_ROW_TOTAL = 'base_row_total';

    /**
     * Row total with discount.
     */
    const KEY_ROW_TOTAL_WITH_DISCOUNT = 'row_total_with_discount';

    /**
     * Discount amount.
     */
    const KEY_DISCOUNT_AMOUNT = 'discount_amount';

    /**
     * Base discount amount.
     */
    const KEY_BASE_DISCOUNT_AMOUNT = 'base_discount_amount';

    /**
     * Discount percent.
     */
    const KEY_DISCOUNT_PERCENT = 'discount_percent';

    /**
     * Tax amount.
     */
    const KEY_TAX_AMOUNT = 'tax_amount';

    /**
     * Base tax amount.
     */
    const KEY_BASE_TAX_AMOUNT = 'base_tax_amount';

    /**
     * Tax percent.
     */
    const KEY_TAX_PERCENT = 'tax_percent';

    /**
     * Price including tax.
     */
    const KEY_PRICE_INCL_TAX = 'price_incl_tax';

    /**
     * Base price including tax.
     */
    const KEY_BASE_PRICE_INCL_TAX = 'base_price_incl_tax';

    /**
     * Row total including tax.
     */
    const KEY_ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';

    /**
     * Base row total including tax.
     */
    const KEY_BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';

    /**
     * Item options data.
     */
    const KEY_OPTIONS = 'options';

    /**
     * Item Weee Tax Applied Amount.
     */
    const KEY_WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';


    /**
     * Item Weee Tax Applied Amount.
     */
    const KEY_WEEE_TAX_APPLIED = 'weee_tax_applied';

    /**
     * Item name.
     */
    const KEY_NAME = 'name';

    /**#@-*/

    /**
     * Set totals item id
     *
     * @param int $id
     * @return $this
     */
    public function setItemId($id);

    /**
     * Get totals item id
     *
     * @return int Item id
     */
    public function getItemId();

    /**
     * Returns the item price in quote currency.
     *
     * @return float Item price in quote currency.
     */
    public function getPrice();

    /**
     * Sets the item price in quote currency.
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Returns the item price in base currency.
     *
     * @return float Item price in base currency.
     */
    public function getBasePrice();

    /**
     * Sets the item price in base currency.
     *
     * @param float $basePrice
     * @return $this
     */
    public function setBasePrice($basePrice);

    /**
     * Returns the item quantity.
     *
     * @return float Item quantity.
     */
    public function getQty();

    /**
     * Sets the item quantity.
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Returns the row total in quote currency.
     *
     * @return float Row total in quote currency.
     */
    public function getRowTotal();

    /**
     * Sets the row total in quote currency.
     *
     * @param float $rowTotal
     * @return $this
     */
    public function setRowTotal($rowTotal);

    /**
     * Returns the row total in base currency.
     *
     * @return float Row total in base currency.
     */
    public function getBaseRowTotal();

    /**
     * Sets the row total in base currency.
     *
     * @param float $baseRowTotal
     * @return $this
     */
    public function setBaseRowTotal($baseRowTotal);

    /**
     * Returns the row total with discount in quote currency.
     *
     * @return float|null Row total with discount in quote currency. Otherwise, null.
     */
    public function getRowTotalWithDiscount();

    /**
     * Sets the row total with discount in quote currency.
     *
     * @param float $rowTotalWithDiscount
     * @return $this
     */
    public function setRowTotalWithDiscount($rowTotalWithDiscount);

    /**
     * Returns the tax amount in quote currency.
     *
     * @return float|null Tax amount in quote currency. Otherwise, null.
     */
    public function getTaxAmount();

    /**
     * Sets the tax amount in quote currency.
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount);

    /**
     * Returns the tax amount in base currency.
     *
     * @return float|null Tax amount in base currency. Otherwise, null.
     */
    public function getBaseTaxAmount();

    /**
     * Sets the tax amount in base currency.
     *
     * @param float $baseTaxAmount
     * @return $this
     */
    public function setBaseTaxAmount($baseTaxAmount);

    /**
     * Returns the tax percent.
     *
     * @return float|null Tax percent. Otherwise, null.
     */
    public function getTaxPercent();

    /**
     * Sets the tax percent.
     *
     * @param float $taxPercent
     * @return $this
     */
    public function setTaxPercent($taxPercent);

    /**
     * Returns the discount amount in quote currency.
     *
     * @return float|null Discount amount in quote currency. Otherwise, null.
     */
    public function getDiscountAmount();

    /**
     * Sets the discount amount in quote currency.
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount);

    /**
     * Returns the discount amount in base currency.
     *
     * @return float|null Discount amount in base currency. Otherwise, null.
     */
    public function getBaseDiscountAmount();

    /**
     * Sets the discount amount in base currency.
     *
     * @param float $baseDiscountAmount
     * @return $this
     */
    public function setBaseDiscountAmount($baseDiscountAmount);

    /**
     * Returns the discount percent.
     *
     * @return float|null Discount percent. Otherwise, null.
     */
    public function getDiscountPercent();

    /**
     * Sets the discount percent.
     *
     * @param float $discountPercent
     * @return $this
     */
    public function setDiscountPercent($discountPercent);

    /**
     * Returns the price including tax in quote currency.
     *
     * @return float|null Price including tax in quote currency. Otherwise, null.
     */
    public function getPriceInclTax();

    /**
     * Sets the price including tax in quote currency.
     *
     * @param float $priceInclTax
     * @return $this
     */
    public function setPriceInclTax($priceInclTax);

    /**
     * Returns the price including tax in base currency.
     *
     * @return float|null Price including tax in base currency. Otherwise, null.
     */
    public function getBasePriceInclTax();

    /**
     * Sets the price including tax in base currency.
     *
     * @param float $basePriceInclTax
     * @return $this
     */
    public function setBasePriceInclTax($basePriceInclTax);

    /**
     * Returns the row total including tax in quote currency.
     *
     * @return float|null Row total including tax in quote currency. Otherwise, null.
     */
    public function getRowTotalInclTax();

    /**
     * Sets the row total including tax in quote currency.
     *
     * @param float $rowTotalInclTax
     * @return $this
     */
    public function setRowTotalInclTax($rowTotalInclTax);

    /**
     * Returns the row total including tax in base currency.
     *
     * @return float|null Row total including tax in base currency. Otherwise, null.
     */
    public function getBaseRowTotalInclTax();

    /**
     * Sets the row total including tax in base currency.
     *
     * @param float $baseRowTotalInclTax
     * @return $this
     */
    public function setBaseRowTotalInclTax($baseRowTotalInclTax);

    /**
     * Returns the item options data.
     *
     * @return string Item price in quote currency.
     */
    public function getOptions();

    /**
     * Sets the item options data.
     *
     * @param string $options
     * @return $this
     */
    public function setOptions($options);

    /**
     * Returns the item weee tax applied amount in quote currency.
     *
     * @return float Item weee tax applied amount in quote currency.
     */
    public function getWeeeTaxAppliedAmount();

    /**
     * Sets the item weee tax applied amount in quote currency.
     *
     * @param float $weeeTaxAppliedAmount
     * @return $this
     */
    public function setWeeeTaxAppliedAmount($weeeTaxAppliedAmount);

    /**
     * Returns the item weee tax applied in quote currency.
     *
     * @return string Item weee tax applied in quote currency.
     */
    public function getWeeeTaxApplied();

    /**
     * Sets the item weee tax applied in quote currency.
     *
     * @param string $weeeTaxApplied
     * @return $this
     */
    public function setWeeeTaxApplied($weeeTaxApplied);
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\TotalsItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Returns the product name.
     *
     * @return string|null Product name. Otherwise, null.
     */
    public function getName();

    /**
     * Sets the product name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\TotalsItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\TotalsItemExtensionInterface $extensionAttributes);
}
