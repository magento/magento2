<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * Quote details item interface.
 * @api
 * @since 2.0.0
 */
interface QuoteDetailsItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get code (sku or shipping code)
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set code (sku or shipping code)
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get type (e.g., shipping, product, wee, gift wrapping, etc.)
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set type (e.g., shipping, product, wee, gift wrapping, etc.)
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type);

    /**
     * Get tax class key
     *
     * @return \Magento\Tax\Api\Data\TaxClassKeyInterface
     * @since 2.0.0
     */
    public function getTaxClassKey();

    /**
     * Set tax class key
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface $taxClassKey
     * @return $this
     * @since 2.0.0
     */
    public function setTaxClassKey(\Magento\Tax\Api\Data\TaxClassKeyInterface $taxClassKey = null);

    /**
     * Get unit price
     *
     * @return float
     * @since 2.0.0
     */
    public function getUnitPrice();

    /**
     * Set unit price
     *
     * @param float $unitPrice
     * @return $this
     * @since 2.0.0
     */
    public function setUnitPrice($unitPrice);

    /**
     * Get quantity
     *
     * @return float
     * @since 2.0.0
     */
    public function getQuantity();

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return $this
     * @since 2.0.0
     */
    public function setQuantity($quantity);

    /**
     * Get indicate that if the tax is included in the unit price and row total
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsTaxIncluded();

    /**
     * Set whether the tax is included in the unit price and row total
     *
     * @param bool $isTaxIncluded
     * @return $this
     * @since 2.0.0
     */
    public function setIsTaxIncluded($isTaxIncluded);

    /**
     * Get short description
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getShortDescription();

    /**
     * Set short description
     *
     * @param string $shortDescription
     * @return $this
     * @since 2.0.0
     */
    public function setShortDescription($shortDescription);

    /**
     * Get discount amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getDiscountAmount();

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountAmount($discountAmount);

    /**
     * Get parent code if this item is a child, null if this is a top level item.
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getParentCode();

    /**
     * Set parent code
     *
     * @param string $parentCode
     * @return $this
     * @since 2.0.0
     */
    public function setParentCode($parentCode);

    /**
     * Get associated item code if this item is associated with another item, null otherwise
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getAssociatedItemCode();

    /**
     * Set associated item code
     *
     * @param int $associatedItemCode
     * @return $this
     * @since 2.0.0
     */
    public function setAssociatedItemCode($associatedItemCode);

    /**
     * Get associated item tax class id
     *
     * @return int
     * @since 2.0.0
     */
    public function getTaxClassId();

    /**
     * Set associated item tax class id
     *
     * @param int $taxClassId
     * @return $this
     * @since 2.0.0
     */
    public function setTaxClassId($taxClassId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttributes
    );
}
