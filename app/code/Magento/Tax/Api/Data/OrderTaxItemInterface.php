<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface OrderTaxItemInterface extends ExtensibleDataInterface
{
    public const TAX_ITEM_ID = 'tax_item_id';
    public const TAX_ID = 'tax_id';
    public const ITEM_ID = 'item_id';
    public const TAX_PERCENT = 'tax_percent';
    public const TAX_CODE = 'tax_code';
    public const AMOUNT = 'amount';
    public const BASE_AMOUNT = 'base_amount';
    public const REAL_AMOUNT = 'real_amount';
    public const REAL_BASE_AMOUNT = 'real_base_amount';
    public const ASSOCIATED_ITEM_ID = 'associated_item_id';
    public const TAXABLE_ITEM_TYPE = 'taxable_item_type';

    /**
     * Get tax item ID
     *
     * @return int|null
     */
    public function getTaxItemId();

    /**
     * Set tax item ID
     *
     * @param int $taxItemId
     * @return $this
     */
    public function setTaxItemId($taxItemId);

    /**
     * Get tax ID
     *
     * @return int|null
     */
    public function getTaxId();

    /**
     * Set tax ID
     *
     * @param int $taxId
     * @return $this
     */
    public function setTaxId($taxId);

    /**
     * Get order item ID
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Set order item id
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Get tax code
     *
     * @return string|null
     */
    public function getTaxCode();

    /**
     * Set tax code
     *
     * @param string $taxCode
     * @return $this
     */
    public function setTaxCode($taxCode);

    /**
     * Get tax percent
     *
     * @return float
     */
    public function getTaxPercent();

    /**
     * Set tax percent
     *
     * @param float $taxPercent
     * @return $this
     */
    public function setTaxPercent($taxPercent);

    /**
     * Get tax amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Set tax amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get tax amount in base currency
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Set tax amount in base currency
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount);

    /**
     * Get real tax amount
     *
     * @return float
     */
    public function getRealAmount();

    /**
     * Set real tax amount
     *
     * @param float $realAmount
     * @return $this
     */
    public function setRealAmount($realAmount);

    /**
     * Get real tax amount in base currency
     *
     * @return float
     */
    public function getRealBaseAmount();

    /**
     * Set real tax amount in base currency
     *
     * @param float $realBaseAmount
     * @return $this
     */
    public function setRealBaseAmount($realBaseAmount);

    /**
     * Get associated order item ID
     *
     * @return int|null
     */
    public function getAssociatedItemId();

    /**
     * Set associated order item ID
     *
     * @param int $associatedItemId
     * @return $this
     */
    public function setAssociatedItemId($associatedItemId);

    /**
     * Get taxable item type
     *
     * @return string|null shipping, product, weee, quote_gw, etc...
     */
    public function getTaxableItemType();

    /**
     * Set taxable item type
     *
     * @param string $taxableItemType shipping, product, weee, quote_gw, etc...
     * @return $this
     */
    public function setTaxableItemType($taxableItemType);

    /**
     * Get extension attributes object
     *
     * @return \Magento\Tax\Api\Data\OrderTaxItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set extension attributes object
     *
     * @param \Magento\Tax\Api\Data\OrderTaxItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxItemExtensionInterface $extensionAttributes
    );
}
