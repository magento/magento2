<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order item interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 2.0.0
 */
interface OrderItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    /*
     * Item ID.
     */
    const ITEM_ID = 'item_id';
    /*
     * Order ID.
     */
    const ORDER_ID = 'order_id';
    /*
     * Parent item ID.
     */
    const PARENT_ITEM_ID = 'parent_item_id';
    /*
     * Quote item ID.
     */
    const QUOTE_ITEM_ID = 'quote_item_id';
    /*
     * Store ID.
     */
    const STORE_ID = 'store_id';
    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Updated-at timestamp.
     */
    const UPDATED_AT = 'updated_at';
    /*
     * Product ID.
     */
    const PRODUCT_ID = 'product_id';
    /*
     * Product type.
     */
    const PRODUCT_TYPE = 'product_type';
    /*
     * Weight.
     */
    const WEIGHT = 'weight';
    /*
     * Is-virtual flag.
     */
    const IS_VIRTUAL = 'is_virtual';
    /*
     * SKU.
     */
    const SKU = 'sku';
    /*
     * Name.
     */
    const NAME = 'name';
    /*
     * Description.
     */
    const DESCRIPTION = 'description';
    /*
     * Applied rule IDs.
     */
    const APPLIED_RULE_IDS = 'applied_rule_ids';
    /*
     * Additional data.
     */
    const ADDITIONAL_DATA = 'additional_data';
    /*
     * Is-quantity-decimal flag.
     */
    const IS_QTY_DECIMAL = 'is_qty_decimal';
    /*
     * No-discount flag.
     */
    const NO_DISCOUNT = 'no_discount';
    /*
     * Quantity backordered.
     */
    const QTY_BACKORDERED = 'qty_backordered';
    /*
     * Quantity canceled.
     */
    const QTY_CANCELED = 'qty_canceled';
    /*
     * Quantity invoiced.
     */
    const QTY_INVOICED = 'qty_invoiced';
    /*
     * Quantity ordered.
     */
    const QTY_ORDERED = 'qty_ordered';
    /*
     * Quantity refunded.
     */
    const QTY_REFUNDED = 'qty_refunded';
    /*
     * Quantity shipped.
     */
    const QTY_SHIPPED = 'qty_shipped';
    /*
     * Base cost.
     */
    const BASE_COST = 'base_cost';
    /*
     * Price.
     */
    const PRICE = 'price';
    /*
     * Base price.
     */
    const BASE_PRICE = 'base_price';
    /*
     * Original price.
     */
    const ORIGINAL_PRICE = 'original_price';
    /*
     * Base original price.
     */
    const BASE_ORIGINAL_PRICE = 'base_original_price';
    /*
     * Tax percent.
     */
    const TAX_PERCENT = 'tax_percent';
    /*
     * Tax amount.
     */
    const TAX_AMOUNT = 'tax_amount';
    /*
     * Base tax amount.
     */
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    /*
     * Tax invoiced.
     */
    const TAX_INVOICED = 'tax_invoiced';
    /*
     * Base tax invoiced.
     */
    const BASE_TAX_INVOICED = 'base_tax_invoiced';
    /*
     * Discount percent.
     */
    const DISCOUNT_PERCENT = 'discount_percent';
    /*
     * Discount amount.
     */
    const DISCOUNT_AMOUNT = 'discount_amount';
    /*
     * Base discount amount.
     */
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    /*
     * Discount invoiced.
     */
    const DISCOUNT_INVOICED = 'discount_invoiced';
    /*
     * Base discount invoiced.
     */
    const BASE_DISCOUNT_INVOICED = 'base_discount_invoiced';
    /*
     * Amount refunded.
     */
    const AMOUNT_REFUNDED = 'amount_refunded';
    /*
     * Base amount refunded.
     */
    const BASE_AMOUNT_REFUNDED = 'base_amount_refunded';
    /*
     * Row total.
     */
    const ROW_TOTAL = 'row_total';
    /*
     * Base row total.
     */
    const BASE_ROW_TOTAL = 'base_row_total';
    /*
     * Row invoiced.
     */
    const ROW_INVOICED = 'row_invoiced';
    /*
     * Base row invoiced.
     */
    const BASE_ROW_INVOICED = 'base_row_invoiced';
    /*
     * Row weight.
     */
    const ROW_WEIGHT = 'row_weight';
    /*
     * Base tax before discount.
     */
    const BASE_TAX_BEFORE_DISCOUNT = 'base_tax_before_discount';
    /*
     * Tax before discount.
     */
    const TAX_BEFORE_DISCOUNT = 'tax_before_discount';
    /*
     * External order item ID.
     */
    const EXT_ORDER_ITEM_ID = 'ext_order_item_id';
    /*
     * Locked DO invoice.
     */
    const LOCKED_DO_INVOICE = 'locked_do_invoice';
    /*
     * Locked DO ship.
     */
    const LOCKED_DO_SHIP = 'locked_do_ship';
    /*
     * Price including tax.
     */
    const PRICE_INCL_TAX = 'price_incl_tax';
    /*
     * Base price including tax.
     */
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    /*
     * Row total including tax.
     */
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    /*
     * Base row total including tax.
     */
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    /*
     * Discount tax compensation amount.
     */
    const DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
    /*
     * Base discount tax compensation amount.
     */
    const BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'base_discount_tax_compensation_amount';
    /*
     * Discount tax compensation invoiced.
     */
    const DISCOUNT_TAX_COMPENSATION_INVOICED = 'discount_tax_compensation_invoiced';
    /*
     * Base discount tax compensation invoiced.
     */
    const BASE_DISCOUNT_TAX_COMPENSATION_INVOICED = 'base_discount_tax_compensation_invoiced';
    /*
     * Discount tax compensation refunded.
     */
    const DISCOUNT_TAX_COMPENSATION_REFUNDED = 'discount_tax_compensation_refunded';
    /*
     * Base discount tax compensation refunded.
     */
    const BASE_DISCOUNT_TAX_COMPENSATION_REFUNDED = 'base_discount_tax_compensation_refunded';
    /*
     * Tax canceled flag
     */
    const TAX_CANCELED = 'tax_canceled';
    /*
     * Discount-tax-compensation-canceled flag.
     */
    const DISCOUNT_TAX_COMPENSATION_CANCELED = 'discount_tax_compensation_canceled';
    /*
     * Tax refunded.
     */
    const TAX_REFUNDED = 'tax_refunded';
    /*
     * Base tax refunded.
     */
    const BASE_TAX_REFUNDED = 'base_tax_refunded';
    /*
     * Discount refunded.
     */
    const DISCOUNT_REFUNDED = 'discount_refunded';
    /*
     * Base discount refunded.
     */
    const BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    /*
     * GW ID.
     */
    const GW_ID = 'gw_id';
    /*
     * GW base price.
     */
    const GW_BASE_PRICE = 'gw_base_price';
    /*
     * GW price.
     */
    const GW_PRICE = 'gw_price';
    /*
     * GW base tax amount.
     */
    const GW_BASE_TAX_AMOUNT = 'gw_base_tax_amount';
    /*
     * GW tax amount.
     */
    const GW_TAX_AMOUNT = 'gw_tax_amount';
    /*
     * GW base price invoiced.
     */
    const GW_BASE_PRICE_INVOICED = 'gw_base_price_invoiced';
    /*
     * GW price invoiced.
     */
    const GW_PRICE_INVOICED = 'gw_price_invoiced';
    /*
     * GW base tax amount invoiced.
     */
    const GW_BASE_TAX_AMOUNT_INVOICED = 'gw_base_tax_amount_invoiced';
    /*
     * GW tax amount invoiced.
     */
    const GW_TAX_AMOUNT_INVOICED = 'gw_tax_amount_invoiced';
    /*
     * GW base price refunded.
     */
    const GW_BASE_PRICE_REFUNDED = 'gw_base_price_refunded';
    /*
     * GW price refunded.
     */
    const GW_PRICE_REFUNDED = 'gw_price_refunded';
    /*
     * GW base tax amount refunded.
     */
    const GW_BASE_TAX_AMOUNT_REFUNDED = 'gw_base_tax_amount_refunded';
    /*
     * GW tax amount refunded.
     */
    const GW_TAX_AMOUNT_REFUNDED = 'gw_tax_amount_refunded';
    /*
     * Free shipping.
     */
    const FREE_SHIPPING = 'free_shipping';
    /*
     * Quantity returned.
     */
    const QTY_RETURNED = 'qty_returned';
    /*
     * Event ID.
     */
    const EVENT_ID = 'event_id';
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
    /*
     * WEEE tax applied.
     */
    const WEEE_TAX_APPLIED = 'weee_tax_applied';
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

    /**
     * Parent Item
     */
    const PARENT_ITEM = 'parent_item';

    /**
     * Product Option
     */
    const KEY_PRODUCT_OPTION = 'product_option';

    /**
     * Gets the additional data for the order item.
     *
     * @return string|null Additional data.
     * @since 2.0.0
     */
    public function getAdditionalData();

    /**
     * Gets the amount refunded for the order item.
     *
     * @return float|null Amount refunded.
     * @since 2.0.0
     */
    public function getAmountRefunded();

    /**
     * Gets the applied rule IDs for the order item.
     *
     * @return string|null Applied rule IDs.
     * @since 2.0.0
     */
    public function getAppliedRuleIds();

    /**
     * Gets the base amount refunded for the order item.
     *
     * @return float|null Base amount refunded.
     * @since 2.0.0
     */
    public function getBaseAmountRefunded();

    /**
     * Gets the base cost for the order item.
     *
     * @return float|null Base cost.
     * @since 2.0.0
     */
    public function getBaseCost();

    /**
     * Gets the base discount amount for the order item.
     *
     * @return float|null Base discount amount.
     * @since 2.0.0
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base discount invoiced for the order item.
     *
     * @return float|null Base discount invoiced.
     * @since 2.0.0
     */
    public function getBaseDiscountInvoiced();

    /**
     * Gets the base discount refunded for the order item.
     *
     * @return float|null Base discount refunded.
     * @since 2.0.0
     */
    public function getBaseDiscountRefunded();

    /**
     * Gets the base discount tax compensation amount for the order item.
     *
     * @return float|null Base discount tax compensation amount.
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationAmount();

    /**
     * Gets the base discount tax compensation invoiced for the order item.
     *
     * @return float|null Base discount tax compensation invoiced.
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationInvoiced();

    /**
     * Gets the base discount tax compensation refunded for the order item.
     *
     * @return float|null Base discount tax compensation refunded.
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationRefunded();

    /**
     * Gets the base original price for the order item.
     *
     * @return float|null Base original price.
     * @since 2.0.0
     */
    public function getBaseOriginalPrice();

    /**
     * Gets the base price for the order item.
     *
     * @return float|null Base price.
     * @since 2.0.0
     */
    public function getBasePrice();

    /**
     * Gets the base price including tax for the order item.
     *
     * @return float|null Base price including tax.
     * @since 2.0.0
     */
    public function getBasePriceInclTax();

    /**
     * Gets the base row invoiced for the order item.
     *
     * @return float|null Base row invoiced.
     * @since 2.0.0
     */
    public function getBaseRowInvoiced();

    /**
     * Gets the base row total for the order item.
     *
     * @return float|null Base row total.
     * @since 2.0.0
     */
    public function getBaseRowTotal();

    /**
     * Gets the base row total including tax for the order item.
     *
     * @return float|null Base row total including tax.
     * @since 2.0.0
     */
    public function getBaseRowTotalInclTax();

    /**
     * Gets the base tax amount for the order item.
     *
     * @return float|null Base tax amount.
     * @since 2.0.0
     */
    public function getBaseTaxAmount();

    /**
     * Gets the base tax before discount for the order item.
     *
     * @return float|null Base tax before discount.
     * @since 2.0.0
     */
    public function getBaseTaxBeforeDiscount();

    /**
     * Gets the base tax invoiced for the order item.
     *
     * @return float|null Base tax invoiced.
     * @since 2.0.0
     */
    public function getBaseTaxInvoiced();

    /**
     * Gets the base tax refunded for the order item.
     *
     * @return float|null Base tax refunded.
     * @since 2.0.0
     */
    public function getBaseTaxRefunded();

    /**
     * Gets the base WEEE tax applied amount for the order item.
     *
     * @return float|null Base WEEE tax applied amount.
     * @since 2.0.0
     */
    public function getBaseWeeeTaxAppliedAmount();

    /**
     * Gets the base WEEE tax applied row amount for the order item.
     *
     * @return float|null Base WEEE tax applied row amount.
     * @since 2.0.0
     */
    public function getBaseWeeeTaxAppliedRowAmnt();

    /**
     * Gets the base WEEE tax disposition for the order item.
     *
     * @return float|null Base WEEE tax disposition.
     * @since 2.0.0
     */
    public function getBaseWeeeTaxDisposition();

    /**
     * Gets the base WEEE tax row disposition for the order item.
     *
     * @return float|null Base WEEE tax row disposition.
     * @since 2.0.0
     */
    public function getBaseWeeeTaxRowDisposition();

    /**
     * Gets the created-at timestamp for the order item.
     *
     * @return string|null Created-at timestamp.
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the order item.
     *
     * @param string $createdAt timestamp
     * @return $this
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the description for the order item.
     *
     * @return string|null Description.
     * @since 2.0.0
     */
    public function getDescription();

    /**
     * Gets the discount amount for the order item.
     *
     * @return float|null Discount amount.
     * @since 2.0.0
     */
    public function getDiscountAmount();

    /**
     * Gets the discount invoiced for the order item.
     *
     * @return float|null Discount invoiced.
     * @since 2.0.0
     */
    public function getDiscountInvoiced();

    /**
     * Gets the discount percent for the order item.
     *
     * @return float|null Discount percent.
     * @since 2.0.0
     */
    public function getDiscountPercent();

    /**
     * Gets the discount refunded for the order item.
     *
     * @return float|null Discount refunded.
     * @since 2.0.0
     */
    public function getDiscountRefunded();

    /**
     * Gets the event ID for the order item.
     *
     * @return int|null Event ID.
     * @since 2.0.0
     */
    public function getEventId();

    /**
     * Gets the external order item ID for the order item.
     *
     * @return string|null External order item ID.
     * @since 2.0.0
     */
    public function getExtOrderItemId();

    /**
     * Gets the free-shipping flag value for the order item.
     *
     * @return int|null Free-shipping flag value.
     * @since 2.0.0
     */
    public function getFreeShipping();

    /**
     * Gets the GW base price for the order item.
     *
     * @return float|null GW base price.
     * @since 2.0.0
     */
    public function getGwBasePrice();

    /**
     * Gets the GW base price invoiced for the order item.
     *
     * @return float|null GW base price invoiced.
     * @since 2.0.0
     */
    public function getGwBasePriceInvoiced();

    /**
     * Gets the GW base price refunded for the order item.
     *
     * @return float|null GW base price refunded.
     * @since 2.0.0
     */
    public function getGwBasePriceRefunded();

    /**
     * Gets the GW base tax amount for the order item.
     *
     * @return float|null GW base tax amount.
     * @since 2.0.0
     */
    public function getGwBaseTaxAmount();

    /**
     * Gets the GW base tax amount invoiced for the order item.
     *
     * @return float|null GW base tax amount invoiced.
     * @since 2.0.0
     */
    public function getGwBaseTaxAmountInvoiced();

    /**
     * Gets the GW base tax amount refunded for the order item.
     *
     * @return float|null GW base tax amount refunded.
     * @since 2.0.0
     */
    public function getGwBaseTaxAmountRefunded();

    /**
     * Gets the GW ID for the order item.
     *
     * @return int|null GW ID.
     * @since 2.0.0
     */
    public function getGwId();

    /**
     * Gets the GW price for the order item.
     *
     * @return float|null GW price.
     * @since 2.0.0
     */
    public function getGwPrice();

    /**
     * Gets the GW price invoiced for the order item.
     *
     * @return float|null GW price invoiced.
     * @since 2.0.0
     */
    public function getGwPriceInvoiced();

    /**
     * Gets the GW price refunded for the order item.
     *
     * @return float|null GW price refunded.
     * @since 2.0.0
     */
    public function getGwPriceRefunded();

    /**
     * Gets the GW tax amount for the order item.
     *
     * @return float|null GW tax amount.
     * @since 2.0.0
     */
    public function getGwTaxAmount();

    /**
     * Gets the GW tax amount invoiced for the order item.
     *
     * @return float|null GW tax amount invoiced.
     * @since 2.0.0
     */
    public function getGwTaxAmountInvoiced();

    /**
     * Gets the GW tax amount refunded for the order item.
     *
     * @return float|null GW tax amount refunded.
     * @since 2.0.0
     */
    public function getGwTaxAmountRefunded();

    /**
     * Gets the discount tax compensation amount for the order item.
     *
     * @return float|null Discount tax compensation amount.
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Gets the discount tax compensation canceled for the order item.
     *
     * @return float|null Discount tax compensation canceled.
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationCanceled();

    /**
     * Gets the discount tax compensation invoiced for the order item.
     *
     * @return float|null Discount tax compensation invoiced.
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationInvoiced();

    /**
     * Gets the discount tax compensation refunded for the order item.
     *
     * @return float|null Discount tax compensation refunded.
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationRefunded();

    /**
     * Gets the is-quantity-decimal flag value for the order item.
     *
     * @return int|null Is-quantity-decimal flag value.
     * @since 2.0.0
     */
    public function getIsQtyDecimal();

    /**
     * Gets the is-virtual flag value for the order item.
     *
     * @return int|null Is-virtual flag value.
     * @since 2.0.0
     */
    public function getIsVirtual();

    /**
     * Gets the item ID for the order item.
     *
     * @return int|null Item ID.
     * @since 2.0.0
     */
    public function getItemId();

    /**
     * Gets the locked DO invoice flag value for the order item.
     *
     * @return int|null Locked DO invoice flag value.
     * @since 2.0.0
     */
    public function getLockedDoInvoice();

    /**
     * Gets the locked DO ship flag value for the order item.
     *
     * @return int|null Locked DO ship flag value.
     * @since 2.0.0
     */
    public function getLockedDoShip();

    /**
     * Gets the name for the order item.
     *
     * @return string|null Name.
     * @since 2.0.0
     */
    public function getName();

    /**
     * Gets the no discount flag value for the order item.
     *
     * @return int|null No-discount flag value.
     * @since 2.0.0
     */
    public function getNoDiscount();

    /**
     * Gets the order ID for the order item.
     *
     * @return int|null Order ID.
     * @since 2.0.0
     */
    public function getOrderId();

    /**
     * Gets the original price for the order item.
     *
     * @return float|null Original price.
     * @since 2.0.0
     */
    public function getOriginalPrice();

    /**
     * Gets the parent item ID for the order item.
     *
     * @return int|null Parent item ID.
     * @since 2.0.0
     */
    public function getParentItemId();

    /**
     * Gets the price for the order item.
     *
     * @return float|null Price.
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Gets the price including tax for the order item.
     *
     * @return float|null Price including tax.
     * @since 2.0.0
     */
    public function getPriceInclTax();

    /**
     * Gets the product ID for the order item.
     *
     * @return int|null Product ID.
     * @since 2.0.0
     */
    public function getProductId();

    /**
     * Gets the product type for the order item.
     *
     * @return string|null Product type.
     * @since 2.0.0
     */
    public function getProductType();

    /**
     * Gets the quantity backordered for the order item.
     *
     * @return float|null Quantity backordered.
     * @since 2.0.0
     */
    public function getQtyBackordered();

    /**
     * Gets the quantity canceled for the order item.
     *
     * @return float|null Quantity canceled.
     * @since 2.0.0
     */
    public function getQtyCanceled();

    /**
     * Gets the quantity invoiced for the order item.
     *
     * @return float|null Quantity invoiced.
     * @since 2.0.0
     */
    public function getQtyInvoiced();

    /**
     * Gets the quantity ordered for the order item.
     *
     * @return float|null Quantity ordered.
     * @since 2.0.0
     */
    public function getQtyOrdered();

    /**
     * Gets the quantity refunded for the order item.
     *
     * @return float|null Quantity refunded.
     * @since 2.0.0
     */
    public function getQtyRefunded();

    /**
     * Gets the quantity returned for the order item.
     *
     * @return float|null Quantity returned.
     * @since 2.0.0
     */
    public function getQtyReturned();

    /**
     * Gets the quantity shipped for the order item.
     *
     * @return float|null Quantity shipped.
     * @since 2.0.0
     */
    public function getQtyShipped();

    /**
     * Gets the quote item ID for the order item.
     *
     * @return int|null Quote item ID.
     * @since 2.0.0
     */
    public function getQuoteItemId();

    /**
     * Gets the row invoiced for the order item.
     *
     * @return float|null Row invoiced.
     * @since 2.0.0
     */
    public function getRowInvoiced();

    /**
     * Gets the row total for the order item.
     *
     * @return float|null Row total.
     * @since 2.0.0
     */
    public function getRowTotal();

    /**
     * Gets the row total including tax for the order item.
     *
     * @return float|null Row total including tax.
     * @since 2.0.0
     */
    public function getRowTotalInclTax();

    /**
     * Gets the row weight for the order item.
     *
     * @return float|null Row weight.
     * @since 2.0.0
     */
    public function getRowWeight();

    /**
     * Gets the SKU for the order item.
     *
     * @return string SKU.
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Gets the store ID for the order item.
     *
     * @return int|null Store ID.
     * @since 2.0.0
     */
    public function getStoreId();

    /**
     * Gets the tax amount for the order item.
     *
     * @return float|null Tax amount.
     * @since 2.0.0
     */
    public function getTaxAmount();

    /**
     * Gets the tax before discount for the order item.
     *
     * @return float|null Tax before discount.
     * @since 2.0.0
     */
    public function getTaxBeforeDiscount();

    /**
     * Gets the tax canceled for the order item.
     *
     * @return float|null Tax canceled.
     * @since 2.0.0
     */
    public function getTaxCanceled();

    /**
     * Gets the tax invoiced for the order item.
     *
     * @return float|null Tax invoiced.
     * @since 2.0.0
     */
    public function getTaxInvoiced();

    /**
     * Gets the tax percent for the order item.
     *
     * @return float|null Tax percent.
     * @since 2.0.0
     */
    public function getTaxPercent();

    /**
     * Gets the tax refunded for the order item.
     *
     * @return float|null Tax refunded.
     * @since 2.0.0
     */
    public function getTaxRefunded();

    /**
     * Gets the updated-at timestamp for the order item.
     *
     * @return string|null Updated-at timestamp.
     * @since 2.0.0
     */
    public function getUpdatedAt();

    /**
     * Gets the WEEE tax applied for the order item.
     *
     * @return string|null WEEE tax applied.
     * @since 2.0.0
     */
    public function getWeeeTaxApplied();

    /**
     * Gets the WEEE tax applied amount for the order item.
     *
     * @return float|null WEEE tax applied amount.
     * @since 2.0.0
     */
    public function getWeeeTaxAppliedAmount();

    /**
     * Gets the WEEE tax applied row amount for the order item.
     *
     * @return float|null WEEE tax applied row amount.
     * @since 2.0.0
     */
    public function getWeeeTaxAppliedRowAmount();

    /**
     * Gets the WEEE tax disposition for the order item.
     *
     * @return float|null WEEE tax disposition.
     * @since 2.0.0
     */
    public function getWeeeTaxDisposition();

    /**
     * Gets the WEEE tax row disposition for the order item.
     *
     * @return float|null WEEE tax row disposition.
     * @since 2.0.0
     */
    public function getWeeeTaxRowDisposition();

    /**
     * Gets the weight for the order item.
     *
     * @return float|null Weight.
     * @since 2.0.0
     */
    public function getWeight();

    /**
     * Gets the parent item
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface|null Parent item
     * @since 2.0.0
     */
    public function getParentItem();

    /**
     * Sets the parent item
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $parentItem
     * @return $this
     * @since 2.0.0
     */
    public function setParentItem($parentItem);

    /**
     * Sets the updated-at timestamp for the order item.
     *
     * @param string $timestamp
     * @return $this
     * @since 2.0.0
     */
    public function setUpdatedAt($timestamp);

    /**
     * Sets the item ID for the order item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setItemId($id);

    /**
     * Sets the order ID for the order item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setOrderId($id);

    /**
     * Sets the parent item ID for the order item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setParentItemId($id);

    /**
     * Sets the quote item ID for the order item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setQuoteItemId($id);

    /**
     * Sets the store ID for the order item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($id);

    /**
     * Sets the product ID for the order item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setProductId($id);

    /**
     * Sets the product type for the order item.
     *
     * @param string $productType
     * @return $this
     * @since 2.0.0
     */
    public function setProductType($productType);

    /**
     * Sets the weight for the order item.
     *
     * @param float $weight
     * @return $this
     * @since 2.0.0
     */
    public function setWeight($weight);

    /**
     * Sets the is-virtual flag value for the order item.
     *
     * @param int $isVirtual
     * @return $this
     * @since 2.0.0
     */
    public function setIsVirtual($isVirtual);

    /**
     * Sets the SKU for the order item.
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Sets the name for the order item.
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Sets the description for the order item.
     *
     * @param string $description
     * @return $this
     * @since 2.0.0
     */
    public function setDescription($description);

    /**
     * Sets the applied rule IDs for the order item.
     *
     * @param string $appliedRuleIds
     * @return $this
     * @since 2.0.0
     */
    public function setAppliedRuleIds($appliedRuleIds);

    /**
     * Sets the additional data for the order item.
     *
     * @param string $additionalData
     * @return $this
     * @since 2.0.0
     */
    public function setAdditionalData($additionalData);

    /**
     * Sets the is-quantity-decimal flag value for the order item.
     *
     * @param int $isQtyDecimal
     * @return $this
     * @since 2.0.0
     */
    public function setIsQtyDecimal($isQtyDecimal);

    /**
     * Sets the no discount flag value for the order item.
     *
     * @param int $noDiscount
     * @return $this
     * @since 2.0.0
     */
    public function setNoDiscount($noDiscount);

    /**
     * Sets the quantity backordered for the order item.
     *
     * @param float $qtyBackordered
     * @return $this
     * @since 2.0.0
     */
    public function setQtyBackordered($qtyBackordered);

    /**
     * Sets the quantity canceled for the order item.
     *
     * @param float $qtyCanceled
     * @return $this
     * @since 2.0.0
     */
    public function setQtyCanceled($qtyCanceled);

    /**
     * Sets the quantity invoiced for the order item.
     *
     * @param float $qtyInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setQtyInvoiced($qtyInvoiced);

    /**
     * Sets the quantity ordered for the order item.
     *
     * @param float $qtyOrdered
     * @return $this
     * @since 2.0.0
     */
    public function setQtyOrdered($qtyOrdered);

    /**
     * Sets the quantity refunded for the order item.
     *
     * @param float $qtyRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setQtyRefunded($qtyRefunded);

    /**
     * Sets the quantity shipped for the order item.
     *
     * @param float $qtyShipped
     * @return $this
     * @since 2.0.0
     */
    public function setQtyShipped($qtyShipped);

    /**
     * Sets the base cost for the order item.
     *
     * @param float $baseCost
     * @return $this
     * @since 2.0.0
     */
    public function setBaseCost($baseCost);

    /**
     * Sets the price for the order item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Sets the base price for the order item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setBasePrice($price);

    /**
     * Sets the original price for the order item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setOriginalPrice($price);

    /**
     * Sets the base original price for the order item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setBaseOriginalPrice($price);

    /**
     * Sets the tax percent for the order item.
     *
     * @param float $taxPercent
     * @return $this
     * @since 2.0.0
     */
    public function setTaxPercent($taxPercent);

    /**
     * Sets the tax amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxAmount($amount);

    /**
     * Sets the base tax amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseTaxAmount($amount);

    /**
     * Sets the tax invoiced for the order item.
     *
     * @param float $taxInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setTaxInvoiced($taxInvoiced);

    /**
     * Sets the base tax invoiced for the order item.
     *
     * @param float $baseTaxInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setBaseTaxInvoiced($baseTaxInvoiced);

    /**
     * Sets the discount percent for the order item.
     *
     * @param float $discountPercent
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountPercent($discountPercent);

    /**
     * Sets the discount amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountAmount($amount);

    /**
     * Sets the base discount amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountAmount($amount);

    /**
     * Sets the discount invoiced for the order item.
     *
     * @param float $discountInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountInvoiced($discountInvoiced);

    /**
     * Sets the base discount invoiced for the order item.
     *
     * @param float $baseDiscountInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountInvoiced($baseDiscountInvoiced);

    /**
     * Sets the amount refunded for the order item.
     *
     * @param float $amountRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setAmountRefunded($amountRefunded);

    /**
     * Sets the base amount refunded for the order item.
     *
     * @param float $baseAmountRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setBaseAmountRefunded($baseAmountRefunded);

    /**
     * Sets the row total for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotal($amount);

    /**
     * Sets the base row total for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowTotal($amount);

    /**
     * Sets the row invoiced for the order item.
     *
     * @param float $rowInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setRowInvoiced($rowInvoiced);

    /**
     * Sets the base row invoiced for the order item.
     *
     * @param float $baseRowInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowInvoiced($baseRowInvoiced);

    /**
     * Sets the row weight for the order item.
     *
     * @param float $rowWeight
     * @return $this
     * @since 2.0.0
     */
    public function setRowWeight($rowWeight);

    /**
     * Sets the base tax before discount for the order item.
     *
     * @param float $baseTaxBeforeDiscount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseTaxBeforeDiscount($baseTaxBeforeDiscount);

    /**
     * Sets the tax before discount for the order item.
     *
     * @param float $taxBeforeDiscount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxBeforeDiscount($taxBeforeDiscount);

    /**
     * Sets the external order item ID for the order item.
     *
     * @param string $id
     * @return $this
     * @since 2.0.0
     */
    public function setExtOrderItemId($id);

    /**
     * Sets the locked DO invoice flag value for the order item.
     *
     * @param int $flag
     * @return $this
     * @since 2.0.0
     */
    public function setLockedDoInvoice($flag);

    /**
     * Sets the locked DO ship flag value for the order item.
     *
     * @param int $flag
     * @return $this
     * @since 2.0.0
     */
    public function setLockedDoShip($flag);

    /**
     * Sets the price including tax for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setPriceInclTax($amount);

    /**
     * Sets the base price including tax for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBasePriceInclTax($amount);

    /**
     * Sets the row total including tax for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotalInclTax($amount);

    /**
     * Sets the base row total including tax for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowTotalInclTax($amount);

    /**
     * Sets the discount tax compensation amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationAmount($amount);

    /**
     * Sets the base discount tax compensation amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationAmount($amount);

    /**
     * Sets the discount tax compensation invoiced for the order item.
     *
     * @param float $discountTaxCompensationInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationInvoiced($discountTaxCompensationInvoiced);

    /**
     * Sets the base discount tax compensation invoiced for the order item.
     *
     * @param float $baseDiscountTaxCompensationInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationInvoiced($baseDiscountTaxCompensationInvoiced);

    /**
     * Sets the discount tax compensation refunded for the order item.
     *
     * @param float $discountTaxCompensationRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationRefunded($discountTaxCompensationRefunded);

    /**
     * Sets the base discount tax compensation refunded for the order item.
     *
     * @param float $baseDiscountTaxCompensationRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationRefunded($baseDiscountTaxCompensationRefunded);

    /**
     * Sets the tax canceled for the order item.
     *
     * @param float $taxCanceled
     * @return $this
     * @since 2.0.0
     */
    public function setTaxCanceled($taxCanceled);

    /**
     * Sets the discount tax compensation canceled for the order item.
     *
     * @param float $discountTaxCompensationCanceled
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationCanceled($discountTaxCompensationCanceled);

    /**
     * Sets the tax refunded for the order item.
     *
     * @param float $taxRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setTaxRefunded($taxRefunded);

    /**
     * Sets the base tax refunded for the order item.
     *
     * @param float $baseTaxRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setBaseTaxRefunded($baseTaxRefunded);

    /**
     * Sets the discount refunded for the order item.
     *
     * @param float $discountRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountRefunded($discountRefunded);

    /**
     * Sets the base discount refunded for the order item.
     *
     * @param float $baseDiscountRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountRefunded($baseDiscountRefunded);

    /**
     * Sets the GW ID for the order item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setGwId($id);

    /**
     * Sets the GW base price for the order item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setGwBasePrice($price);

    /**
     * Sets the GW price for the order item.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setGwPrice($price);

    /**
     * Sets the GW base tax amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setGwBaseTaxAmount($amount);

    /**
     * Sets the GW tax amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setGwTaxAmount($amount);

    /**
     * Sets the GW base price invoiced for the order item.
     *
     * @param float $gwBasePriceInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setGwBasePriceInvoiced($gwBasePriceInvoiced);

    /**
     * Sets the GW price invoiced for the order item.
     *
     * @param float $gwPriceInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setGwPriceInvoiced($gwPriceInvoiced);

    /**
     * Sets the GW base tax amount invoiced for the order item.
     *
     * @param float $gwBaseTaxAmountInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setGwBaseTaxAmountInvoiced($gwBaseTaxAmountInvoiced);

    /**
     * Sets the GW tax amount invoiced for the order item.
     *
     * @param float $gwTaxAmountInvoiced
     * @return $this
     * @since 2.0.0
     */
    public function setGwTaxAmountInvoiced($gwTaxAmountInvoiced);

    /**
     * Sets the GW base price refunded for the order item.
     *
     * @param float $gwBasePriceRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setGwBasePriceRefunded($gwBasePriceRefunded);

    /**
     * Sets the GW price refunded for the order item.
     *
     * @param float $gwPriceRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setGwPriceRefunded($gwPriceRefunded);

    /**
     * Sets the GW base tax amount refunded for the order item.
     *
     * @param float $gwBaseTaxAmountRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setGwBaseTaxAmountRefunded($gwBaseTaxAmountRefunded);

    /**
     * Sets the GW tax amount refunded for the order item.
     *
     * @param float $gwTaxAmountRefunded
     * @return $this
     * @since 2.0.0
     */
    public function setGwTaxAmountRefunded($gwTaxAmountRefunded);

    /**
     * Sets the free-shipping flag value for the order item.
     *
     * @param int $freeShipping
     * @return $this
     * @since 2.0.0
     */
    public function setFreeShipping($freeShipping);

    /**
     * Sets the quantity returned for the order item.
     *
     * @param float $qtyReturned
     * @return $this
     * @since 2.0.0
     */
    public function setQtyReturned($qtyReturned);

    /**
     * Sets the event ID for the order item.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setEventId($id);

    /**
     * Sets the base WEEE tax applied amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseWeeeTaxAppliedAmount($amount);

    /**
     * Sets the base WEEE tax applied row amount for the order item.
     *
     * @param float $amnt
     * @return $this
     * @since 2.0.0
     */
    public function setBaseWeeeTaxAppliedRowAmnt($amnt);

    /**
     * Sets the WEEE tax applied amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxAppliedAmount($amount);

    /**
     * Sets the WEEE tax applied row amount for the order item.
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxAppliedRowAmount($amount);

    /**
     * Sets the WEEE tax applied for the order item.
     *
     * @param string $weeeTaxApplied
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxApplied($weeeTaxApplied);

    /**
     * Sets the WEEE tax disposition for the order item.
     *
     * @param float $weeeTaxDisposition
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxDisposition($weeeTaxDisposition);

    /**
     * Sets the WEEE tax row disposition for the order item.
     *
     * @param float $weeeTaxRowDisposition
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxRowDisposition($weeeTaxRowDisposition);

    /**
     * Sets the base WEEE tax disposition for the order item.
     *
     * @param float $baseWeeeTaxDisposition
     * @return $this
     * @since 2.0.0
     */
    public function setBaseWeeeTaxDisposition($baseWeeeTaxDisposition);

    /**
     * Sets the base WEEE tax row disposition for the order item.
     *
     * @param float $baseWeeeTaxRowDisposition
     * @return $this
     * @since 2.0.0
     */
    public function setBaseWeeeTaxRowDisposition($baseWeeeTaxRowDisposition);

    /**
     * Returns product option
     *
     * @return \Magento\Catalog\Api\Data\ProductOptionInterface|null
     * @since 2.0.0
     */
    public function getProductOption();

    /**
     * Sets product option
     *
     * @param \Magento\Catalog\Api\Data\ProductOptionInterface $productOption
     * @return $this
     * @since 2.0.0
     */
    public function setProductOption(\Magento\Catalog\Api\Data\ProductOptionInterface $productOption);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\OrderItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\OrderItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\OrderItemExtensionInterface $extensionAttributes);
}
