<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Credit memo item interface.
 *
 * After a customer places and pays for an order and an invoice has been issued, the merchant can create a credit memo
 * to refund all or part of the amount paid for any returned or undelivered items. The memo restores funds to the
 * customer account so that the customer can make future purchases. A credit memo item is an invoiced item for which
 * a merchant creates a credit memo.
 * @api
 * @since 2.0.0
 */
interface CreditmemoItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Credit memo item ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Parent ID.
     */
    const PARENT_ID = 'parent_id';
    /*
     * Base price.
     */
    const BASE_PRICE = 'base_price';
    /*
     * Tax amount.
     */
    const TAX_AMOUNT = 'tax_amount';
    /*
     * Base row total.
     */
    const BASE_ROW_TOTAL = 'base_row_total';
    /*
     * Base row total.
     */
    const DISCOUNT_AMOUNT = 'discount_amount';
    /*
     * Row total.
     */
    const ROW_TOTAL = 'row_total';
    /*
     * Base discount amount.
     */
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    /*
     * Price including tax.
     */
    const PRICE_INCL_TAX = 'price_incl_tax';
    /*
     * Base tax amount.
     */
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    /*
     * Base price including tax.
     */
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    /*
     * Quantity.
     */
    const QTY = 'qty';
    /*
     * Base cost.
     */
    const BASE_COST = 'base_cost';
    /*
     * Price.
     */
    const PRICE = 'price';
    /*
     * Base row total including tax.
     */
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    /*
     * Row total including tax.
     */
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    /*
     * Product ID.
     */
    const PRODUCT_ID = 'product_id';
    /*
     * Order item ID.
     */
    const ORDER_ITEM_ID = 'order_item_id';
    /*
     * Additional data.
     */
    const ADDITIONAL_DATA = 'additional_data';
    /*
     * Description.
     */
    const DESCRIPTION = 'description';
    /*
     * SKU.
     */
    const SKU = 'sku';
    /*
     * Name.
     */
    const NAME = 'name';
    /*
     * Discount tax compensation amount.
     */
    const DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
    /*
     * Base discount tax compensation amount.
     */
    const BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'base_discount_tax_compensation_amount';
    /*
     * WEEE tax disposition.
     */
    const WEEE_TAX_DISPOSITION = 'weee_tax_disposition';
    /*
     * WEEE tax row disposition.
     */
    const WEEE_TAX_ROW_DISPOSITION = 'weee_tax_row_disposition';
    /*
     * Base WEEE tax disposition.
     */
    const BASE_WEEE_TAX_DISPOSITION = 'base_weee_tax_disposition';
    /*
     * Base WEEE tax row disposition.
     */
    const BASE_WEEE_TAX_ROW_DISPOSITION = 'base_weee_tax_row_disposition';
    /*
     * WEEE tax applied.
     */
    const WEEE_TAX_APPLIED = 'weee_tax_applied';
    /*
     * Base WEEE tax applied amount.
     */
    const BASE_WEEE_TAX_APPLIED_AMOUNT = 'base_weee_tax_applied_amount';
    /*
     * Base WEEE tax applied row amount.
     */
    const BASE_WEEE_TAX_APPLIED_ROW_AMNT = 'base_weee_tax_applied_row_amnt';
    /*
     * WEEE tax applied amount.
     */
    const WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';
    /*
     * WEEE tax applied row amount.
     */
    const WEEE_TAX_APPLIED_ROW_AMOUNT = 'weee_tax_applied_row_amount';

    /**
     * Gets the additional data for a credit memo item.
     *
     * @return string|null Additional data.
     * @since 2.0.0
     */
    public function getAdditionalData();

    /**
     * Gets the base cost for a credit memo item.
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseCost();

    /**
     * Gets the base discount amount for a credit memo item.
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base discount tax compensation amount for a credit memo item.
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationAmount();

    /**
     * Gets the base price for a credit memo item.
     *
     * @return float
     * @since 2.0.0
     */
    public function getBasePrice();

    /**
     * Gets the base price including tax for a credit memo item.
     *
     * @return float|null Base price including tax.
     * @since 2.0.0
     */
    public function getBasePriceInclTax();

    /**
     * Gets the base row total for a credit memo item.
     *
     * @return float|null Base row total.
     * @since 2.0.0
     */
    public function getBaseRowTotal();

    /**
     * Gets the base row total including tax for a credit memo item.
     *
     * @return float|null Base row total including tax.
     * @since 2.0.0
     */
    public function getBaseRowTotalInclTax();

    /**
     * Gets the base tax amount for a credit memo item.
     *
     * @return float|null Base tax amount.
     * @since 2.0.0
     */
    public function getBaseTaxAmount();

    /**
     * Gets the base WEEE tax applied amount for a credit memo item.
     *
     * @return float|null Base WEEE tax applied amount.
     * @since 2.0.0
     */
    public function getBaseWeeeTaxAppliedAmount();

    /**
     * Gets the base WEEE tax applied row amount for a credit memo item.
     *
     * @return float|null Base WEEE tax applied row amount.
     * @since 2.0.0
     */
    public function getBaseWeeeTaxAppliedRowAmnt();

    /**
     * Gets the base WEEE tax disposition for a credit memo item.
     *
     * @return float|null Base WEEE tax disposition.
     * @since 2.0.0
     */
    public function getBaseWeeeTaxDisposition();

    /**
     * Gets the base WEEE tax row disposition for a credit memo item.
     *
     * @return float|null Base WEEE tax row disposition.
     * @since 2.0.0
     */
    public function getBaseWeeeTaxRowDisposition();

    /**
     * Gets the description for a credit memo item.
     *
     * @return string|null Description.
     * @since 2.0.0
     */
    public function getDescription();

    /**
     * Gets the discount amount for a credit memo item.
     *
     * @return float|null Discount amount.
     * @since 2.0.0
     */
    public function getDiscountAmount();

    /**
     * Gets the ID for a credit memo item.
     *
     * @return int Credit memo item ID.
     * @since 2.0.0
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     * @since 2.0.0
     */
    public function setEntityId($entityId);

    /**
     * Gets the discount tax compensation amount for a credit memo item.
     *
     * @return float|null Discount tax compensation amount.
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Gets the name for a credit memo item.
     *
     * @return string|null Name.
     * @since 2.0.0
     */
    public function getName();

    /**
     * Gets the order item ID for a credit memo item.
     *
     * @return int Order item ID.
     * @since 2.0.0
     */
    public function getOrderItemId();

    /**
     * Gets the parent ID for a credit memo item.
     *
     * @return int|null Parent ID.
     * @since 2.0.0
     */
    public function getParentId();

    /**
     * Gets the price for a credit memo item.
     *
     * @return float|null Price.
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Gets the price including tax for a credit memo item.
     *
     * @return float|null Price including tax.
     * @since 2.0.0
     */
    public function getPriceInclTax();

    /**
     * Gets the product ID for a credit memo item.
     *
     * @return int|null Product ID.
     * @since 2.0.0
     */
    public function getProductId();

    /**
     * Gets the quantity for a credit memo item.
     *
     * @return float Quantity.
     * @since 2.0.0
     */
    public function getQty();

    /**
     * Gets the row total for a credit memo item.
     *
     * @return float|null Row total.
     * @since 2.0.0
     */
    public function getRowTotal();

    /**
     * Gets the row total including tax for a credit memo item.
     *
     * @return float|null Row total including tax.
     * @since 2.0.0
     */
    public function getRowTotalInclTax();

    /**
     * Gets the SKU for a credit memo item.
     *
     * @return string|null SKU.
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Gets the tax amount for a credit memo item.
     *
     * @return float|null Tax amount.
     * @since 2.0.0
     */
    public function getTaxAmount();

    /**
     * Gets the WEEE tax applied for a credit memo item.
     *
     * @return string|null WEEE tax applied.
     * @since 2.0.0
     */
    public function getWeeeTaxApplied();

    /**
     * Gets the WEEE tax applied amount for a credit memo item.
     *
     * @return float|null WEEE tax applied amount.
     * @since 2.0.0
     */
    public function getWeeeTaxAppliedAmount();

    /**
     * Gets the WEEE tax applied row amount for a credit memo item.
     *
     * @return float|null WEEE tax applied row amount.
     * @since 2.0.0
     */
    public function getWeeeTaxAppliedRowAmount();

    /**
     * Gets the WEEE tax disposition for a credit memo item.
     *
     * @return float|null WEEE tax disposition.
     * @since 2.0.0
     */
    public function getWeeeTaxDisposition();

    /**
     * Gets the WEEE tax row disposition for a credit memo item.
     *
     * @return float|null WEEE tax row disposition.
     * @since 2.0.0
     */
    public function getWeeeTaxRowDisposition();

    /**
     * Sets the parent ID for a credit memo item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setParentId($id);

    /**
     * Sets the base price for a credit memo item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setBasePrice($price);

    /**
     * Sets the tax amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxAmount($amount);

    /**
     * Sets the base row total for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowTotal($amount);

    /**
     * Sets the discount amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountAmount($amount);

    /**
     * Sets the row total for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotal($amount);

    /**
     * Sets the base discount amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountAmount($amount);

    /**
     * Sets the price including tax for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setPriceInclTax($amount);

    /**
     * Sets the base tax amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseTaxAmount($amount);

    /**
     * Sets the base price including tax for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBasePriceInclTax($amount);

    /**
     * Sets the quantity for a credit memo item.
     *
     * @param float $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty);

    /**
     * Sets the base cost for a credit memo item.
     *
     * @param float $baseCost
     * @return $this
     * @since 2.0.0
     */
    public function setBaseCost($baseCost);

    /**
     * Sets the price for a credit memo item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Sets the base row total including tax for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowTotalInclTax($amount);

    /**
     * Sets the row total including tax for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotalInclTax($amount);

    /**
     * Sets the product ID for a credit memo item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setProductId($id);

    /**
     * Sets the order item ID for a credit memo item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setOrderItemId($id);

    /**
     * Sets the additional data for a credit memo item.
     *
     * @param string $additionalData
     * @return $this
     * @since 2.0.0
     */
    public function setAdditionalData($additionalData);

    /**
     * Sets the description for a credit memo item.
     *
     * @param string $description
     * @return $this
     * @since 2.0.0
     */
    public function setDescription($description);

    /**
     * Sets the SKU for a credit memo item.
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Sets the name for a credit memo item.
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Sets the discount tax compensation amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationAmount($amount);

    /**
     * Sets the base discount tax compensation amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationAmount($amount);

    /**
     * Sets the WEEE tax disposition for a credit memo item.
     *
     * @param float $weeeTaxDisposition
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxDisposition($weeeTaxDisposition);

    /**
     * Sets the WEEE tax row disposition for a credit memo item.
     *
     * @param float $weeeTaxRowDisposition
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxRowDisposition($weeeTaxRowDisposition);

    /**
     * Sets the base WEEE tax disposition for a credit memo item.
     *
     * @param float $baseWeeeTaxDisposition
     * @return $this
     * @since 2.0.0
     */
    public function setBaseWeeeTaxDisposition($baseWeeeTaxDisposition);

    /**
     * Sets the base WEEE tax row disposition for a credit memo item.
     *
     * @param float $baseWeeeTaxRowDisposition
     * @return $this
     * @since 2.0.0
     */
    public function setBaseWeeeTaxRowDisposition($baseWeeeTaxRowDisposition);

    /**
     * Sets the WEEE tax applied for a credit memo item.
     *
     * @param string $weeeTaxApplied
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxApplied($weeeTaxApplied);

    /**
     * Sets the base WEEE tax applied amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseWeeeTaxAppliedAmount($amount);

    /**
     * Sets the base WEEE tax applied row amount for a credit memo item.
     *
     * @param float $amnt
     * @return $this
     * @since 2.0.0
     */
    public function setBaseWeeeTaxAppliedRowAmnt($amnt);

    /**
     * Sets the WEEE tax applied amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxAppliedAmount($amount);

    /**
     * Sets the WEEE tax applied row amount for a credit memo item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxAppliedRowAmount($amount);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoItemExtensionInterface $extensionAttributes
    );
}
