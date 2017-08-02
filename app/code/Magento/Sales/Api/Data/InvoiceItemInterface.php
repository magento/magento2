<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Invoice item interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice item is a purchased item in an invoice.
 * @api
 * @since 2.0.0
 */
interface InvoiceItemInterface extends ExtensibleDataInterface, LineItemInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
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
     * Discount amount.
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

    /**
     * Invoice
     */
    const INVOICE = 'invoice';

    /**
     * Gets the additional data for the invoice item.
     *
     * @return string|null Additional data.
     * @since 2.0.0
     */
    public function getAdditionalData();

    /**
     * Gets the base cost for the invoice item.
     *
     * @return float|null Base cost.
     * @since 2.0.0
     */
    public function getBaseCost();

    /**
     * Gets the base discount amount for the invoice item.
     *
     * @return float|null Base discount amount.
     * @since 2.0.0
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base discount tax compensation amount for the invoice item.
     *
     * @return float|null Base discount tax compensation amount.
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationAmount();

    /**
     * Gets the base price for the invoice item.
     *
     * @return float|null Base price.
     * @since 2.0.0
     */
    public function getBasePrice();

    /**
     * Gets the base price including tax for the invoice item.
     *
     * @return float|null Base price including tax.
     * @since 2.0.0
     */
    public function getBasePriceInclTax();

    /**
     * Gets the base row total for the invoice item.
     *
     * @return float|null Base row total.
     * @since 2.0.0
     */
    public function getBaseRowTotal();

    /**
     * Gets the base row total including tax for the invoice item.
     *
     * @return float|null Base row total including tax.
     * @since 2.0.0
     */
    public function getBaseRowTotalInclTax();

    /**
     * Gets the base tax amount for the invoice item.
     *
     * @return float|null Base tax amount.
     * @since 2.0.0
     */
    public function getBaseTaxAmount();

    /**
     * Gets the description for the invoice item.
     *
     * @return string|null Description.
     * @since 2.0.0
     */
    public function getDescription();

    /**
     * Gets the discount amount for the invoice item.
     *
     * @return float|null Discount amount.
     * @since 2.0.0
     */
    public function getDiscountAmount();

    /**
     * Gets the ID for the invoice item.
     *
     * @return int|null Invoice item ID.
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
     * Gets the discount tax compensation amount for the invoice item.
     *
     * @return float|null Discount tax compensation amount.
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Gets the name for the invoice item.
     *
     * @return string|null Name.
     * @since 2.0.0
     */
    public function getName();

    /**
     * Gets the parent ID for the invoice item.
     *
     * @return int|null Parent ID.
     * @since 2.0.0
     */
    public function getParentId();

    /**
     * Gets the price for the invoice item.
     *
     * @return float|null Price.
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Gets the price including tax for the invoice item.
     *
     * @return float|null Price including tax.
     * @since 2.0.0
     */
    public function getPriceInclTax();

    /**
     * Gets the product ID for the invoice item.
     *
     * @return int|null Product ID.
     * @since 2.0.0
     */
    public function getProductId();

    /**
     * Gets the row total for the invoice item.
     *
     * @return float|null Row total.
     * @since 2.0.0
     */
    public function getRowTotal();

    /**
     * Gets the row total including tax for the invoice item.
     *
     * @return float|null Row total including tax.
     * @since 2.0.0
     */
    public function getRowTotalInclTax();

    /**
     * Gets the SKU for the invoice item.
     *
     * @return string SKU.
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Gets the tax amount for the invoice item.
     *
     * @return float|null Tax amount.
     * @since 2.0.0
     */
    public function getTaxAmount();

    /**
     * Sets the parent ID for the invoice item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setParentId($id);

    /**
     * Sets the base price for the invoice item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setBasePrice($price);

    /**
     * Sets the tax amount for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxAmount($amount);

    /**
     * Sets the base row total for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowTotal($amount);

    /**
     * Sets the discount amount for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountAmount($amount);

    /**
     * Sets the row total for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotal($amount);

    /**
     * Sets the base discount amount for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountAmount($amount);

    /**
     * Sets the price including tax for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setPriceInclTax($amount);

    /**
     * Sets the base tax amount for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseTaxAmount($amount);

    /**
     * Sets the base price including tax for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBasePriceInclTax($amount);

    /**
     * Sets the base cost for the invoice item.
     *
     * @param float $baseCost
     * @return $this
     * @since 2.0.0
     */
    public function setBaseCost($baseCost);

    /**
     * Sets the price for the invoice item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Sets the base row total including tax for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowTotalInclTax($amount);

    /**
     * Sets the row total including tax for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotalInclTax($amount);

    /**
     * Sets the product ID for the invoice item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setProductId($id);

    /**
     * Sets the additional data for the invoice item.
     *
     * @param string $additionalData
     * @return $this
     * @since 2.0.0
     */
    public function setAdditionalData($additionalData);

    /**
     * Sets the description for the invoice item.
     *
     * @param string $description
     * @return $this
     * @since 2.0.0
     */
    public function setDescription($description);

    /**
     * Sets the SKU for the invoice item.
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Sets the name for the invoice item.
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Sets the discount tax compensation amount for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationAmount($amount);

    /**
     * Sets the base discount tax compensation amount for the invoice item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationAmount($amount);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\InvoiceItemExtensionInterface $extensionAttributes);
}
