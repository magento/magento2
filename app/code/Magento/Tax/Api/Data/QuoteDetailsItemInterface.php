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
     * @api
     * @return string|null
     */
    public function getCode();

    /**
     * Set code (sku or shipping code)
     *
     * @api
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get type (e.g., shipping, product, wee, gift wrapping, etc.)
     *
     * @api
     * @return string|null
     */
    public function getType();

    /**
     * Set type (e.g., shipping, product, wee, gift wrapping, etc.)
     *
     * @api
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get tax class key
     *
     * @api
     * @return \Magento\Tax\Api\Data\TaxClassKeyInterface
     */
    public function getTaxClassKey();

    /**
     * Set tax class key
     *
     * @api
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface $taxClassKey
     * @return $this
     */
    public function setTaxClassKey(\Magento\Tax\Api\Data\TaxClassKeyInterface $taxClassKey = null);

    /**
     * Get unit price
     *
     * @api
     * @return float
     */
    public function getUnitPrice();

    /**
     * Set unit price
     *
     * @api
     * @param float $unitPrice
     * @return $this
     */
    public function setUnitPrice($unitPrice);

    /**
     * Get quantity
     *
     * @api
     * @return float
     */
    public function getQuantity();

    /**
     * Set quantity
     *
     * @api
     * @param float $quantity
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * Get indicate that if the tax is included in the unit price and row total
     *
     * @api
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTaxIncluded();

    /**
     * Set whether the tax is included in the unit price and row total
     *
     * @api
     * @param bool $isTaxIncluded
     * @return $this
     */
    public function setIsTaxIncluded($isTaxIncluded);

    /**
     * Get short description
     *
     * @api
     * @return string|null
     */
    public function getShortDescription();

    /**
     * Set short description
     *
     * @api
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription);

    /**
     * Get discount amount
     *
     * @api
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Set discount amount
     *
     * @api
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount);

    /**
     * Get parent code if this item is a child, null if this is a top level item.
     *
     * @api
     * @return string|null
     */
    public function getParentCode();

    /**
     * Set parent code
     *
     * @api
     * @param string $parentCode
     * @return $this
     */
    public function setParentCode($parentCode);

    /**
     * Get associated item code if this item is associated with another item, null otherwise
     *
     * @api
     * @return mixed|null
     */
    public function getAssociatedItemCode();

    /**
     * Set associated item code
     *
     * @api
     * @param int $associatedItemCode
     * @return $this
     */
    public function setAssociatedItemCode($associatedItemCode);

    /**
     * Get associated item tax class id
     *
     * @api
     * @return int
     */
    public function getTaxClassId();

    /**
     * Set associated item tax class id
     *
     * @api
     * @param int $taxClassId
     * @return $this
     */
    public function setTaxClassId($taxClassId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttributes
    );
}
