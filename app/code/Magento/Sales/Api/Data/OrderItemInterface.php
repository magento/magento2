<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order item interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
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
     * Product options.
     */
    const PRODUCT_OPTIONS = 'product_options';
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
     * Hidden tax amount.
     */
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    /*
     * Base hidden tax amount.
     */
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    /*
     * Hidden tax invoiced.
     */
    const HIDDEN_TAX_INVOICED = 'hidden_tax_invoiced';
    /*
     * Base hidden tax invoiced.
     */
    const BASE_HIDDEN_TAX_INVOICED = 'base_hidden_tax_invoiced';
    /*
     * Hidden tax refunded.
     */
    const HIDDEN_TAX_REFUNDED = 'hidden_tax_refunded';
    /*
     * Base hidden tax refunded.
     */
    const BASE_HIDDEN_TAX_REFUNDED = 'base_hidden_tax_refunded';
    /*
     * Tax canceled flag
     */
    const TAX_CANCELED = 'tax_canceled';
    /*
     * Hidden-tax-canceled flag.
     */
    const HIDDEN_TAX_CANCELED = 'hidden_tax_canceled';
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
     * Gets the additional data for the order item.
     *
     * @return string Additional data.
     */
    public function getAdditionalData();

    /**
     * Gets the amount refunded for the order item.
     *
     * @return float Amount refunded.
     */
    public function getAmountRefunded();

    /**
     * Gets the applied rule IDs for the order item.
     *
     * @return string Applied rule IDs.
     */
    public function getAppliedRuleIds();

    /**
     * Gets the base amount refunded for the order item.
     *
     * @return float Base amount refunded.
     */
    public function getBaseAmountRefunded();

    /**
     * Gets the base cost for the order item.
     *
     * @return float Base cost.
     */
    public function getBaseCost();

    /**
     * Gets the base discount amount for the order item.
     *
     * @return float Base discount amount.
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base discount invoiced for the order item.
     *
     * @return float Base discount invoiced.
     */
    public function getBaseDiscountInvoiced();

    /**
     * Gets the base discount refunded for the order item.
     *
     * @return float Base discount refunded.
     */
    public function getBaseDiscountRefunded();

    /**
     * Gets the base hidden tax amount for the order item.
     *
     * @return float Base hidden tax amount.
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Gets the base hidden tax invoiced for the order item.
     *
     * @return float Base hidden tax invoiced.
     */
    public function getBaseHiddenTaxInvoiced();

    /**
     * Gets the base hidden tax refunded for the order item.
     *
     * @return float Base hidden tax refunded.
     */
    public function getBaseHiddenTaxRefunded();

    /**
     * Gets the base original price for the order item.
     *
     * @return float Base original price.
     */
    public function getBaseOriginalPrice();

    /**
     * Gets the base price for the order item.
     *
     * @return float Base price.
     */
    public function getBasePrice();

    /**
     * Gets the base price including tax for the order item.
     *
     * @return float Base price including tax.
     */
    public function getBasePriceInclTax();

    /**
     * Gets the base row invoiced for the order item.
     *
     * @return float Base row invoiced.
     */
    public function getBaseRowInvoiced();

    /**
     * Gets the base row total for the order item.
     *
     * @return float Base row total.
     */
    public function getBaseRowTotal();

    /**
     * Gets the base row total including tax for the order item.
     *
     * @return float Base row total including tax.
     */
    public function getBaseRowTotalInclTax();

    /**
     * Gets the base tax amount for the order item.
     *
     * @return float Base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the base tax before discount for the order item.
     *
     * @return float Base tax before discount.
     */
    public function getBaseTaxBeforeDiscount();

    /**
     * Gets the base tax invoiced for the order item.
     *
     * @return float Base tax invoiced.
     */
    public function getBaseTaxInvoiced();

    /**
     * Gets the base tax refunded for the order item.
     *
     * @return float Base tax refunded.
     */
    public function getBaseTaxRefunded();

    /**
     * Gets the base WEEE tax applied amount for the order item.
     *
     * @return float Base WEEE tax applied amount.
     */
    public function getBaseWeeeTaxAppliedAmount();

    /**
     * Gets the base WEEE tax applied row amount for the order item.
     *
     * @return float Base WEEE tax applied row amount.
     */
    public function getBaseWeeeTaxAppliedRowAmnt();

    /**
     * Gets the base WEEE tax disposition for the order item.
     *
     * @return float Base WEEE tax disposition.
     */
    public function getBaseWeeeTaxDisposition();

    /**
     * Gets the base WEEE tax row disposition for the order item.
     *
     * @return float Base WEEE tax row disposition.
     */
    public function getBaseWeeeTaxRowDisposition();

    /**
     * Gets the created-at timestamp for the order item.
     *
     * @return string Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Gets the description for the order item.
     *
     * @return string Description.
     */
    public function getDescription();

    /**
     * Gets the discount amount for the order item.
     *
     * @return float Discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the discount invoiced for the order item.
     *
     * @return float Discount invoiced.
     */
    public function getDiscountInvoiced();

    /**
     * Gets the discount percent for the order item.
     *
     * @return float Discount percent.
     */
    public function getDiscountPercent();

    /**
     * Gets the discount refunded for the order item.
     *
     * @return float Discount refunded.
     */
    public function getDiscountRefunded();

    /**
     * Gets the event ID for the order item.
     *
     * @return int Event ID.
     */
    public function getEventId();

    /**
     * Gets the external order item ID for the order item.
     *
     * @return string External order item ID.
     */
    public function getExtOrderItemId();

    /**
     * Gets the free-shipping flag value for the order item.
     *
     * @return int Free-shipping flag value.
     */
    public function getFreeShipping();

    /**
     * Gets the GW base price for the order item.
     *
     * @return float GW base price.
     */
    public function getGwBasePrice();

    /**
     * Gets the GW base price invoiced for the order item.
     *
     * @return float GW base price invoiced.
     */
    public function getGwBasePriceInvoiced();

    /**
     * Gets the GW base price refunded for the order item.
     *
     * @return float GW base price refunded.
     */
    public function getGwBasePriceRefunded();

    /**
     * Gets the GW base tax amount for the order item.
     *
     * @return float GW base tax amount.
     */
    public function getGwBaseTaxAmount();

    /**
     * Gets the GW base tax amount invoiced for the order item.
     *
     * @return float GW base tax amount invoiced.
     */
    public function getGwBaseTaxAmountInvoiced();

    /**
     * Gets the GW base tax amount refunded for the order item.
     *
     * @return float GW base tax amount refunded.
     */
    public function getGwBaseTaxAmountRefunded();

    /**
     * Gets the GW ID for the order item.
     *
     * @return int GW ID.
     */
    public function getGwId();

    /**
     * Gets the GW price for the order item.
     *
     * @return float GW price.
     */
    public function getGwPrice();

    /**
     * Gets the GW price invoiced for the order item.
     *
     * @return float GW price invoiced.
     */
    public function getGwPriceInvoiced();

    /**
     * Gets the GW price refunded for the order item.
     *
     * @return float GW price refunded.
     */
    public function getGwPriceRefunded();

    /**
     * Gets the GW tax amount for the order item.
     *
     * @return float GW tax amount.
     */
    public function getGwTaxAmount();

    /**
     * Gets the GW tax amount invoiced for the order item.
     *
     * @return float GW tax amount invoiced.
     */
    public function getGwTaxAmountInvoiced();

    /**
     * Gets the GW tax amount refunded for the order item.
     *
     * @return float GW tax amount refunded.
     */
    public function getGwTaxAmountRefunded();

    /**
     * Gets the hidden tax amount for the order item.
     *
     * @return float Hidden tax amount.
     */
    public function getHiddenTaxAmount();

    /**
     * Gets the hidden tax canceled for the order item.
     *
     * @return float Hidden tax canceled.
     */
    public function getHiddenTaxCanceled();

    /**
     * Gets the hidden tax invoiced for the order item.
     *
     * @return float Hidden tax invoiced.
     */
    public function getHiddenTaxInvoiced();

    /**
     * Gets the hidden tax refunded for the order item.
     *
     * @return float Hidden tax refunded.
     */
    public function getHiddenTaxRefunded();

    /**
     * Gets the is-quantity-decimal flag value for the order item.
     *
     * @return int Is-quantity-decimal flag value.
     */
    public function getIsQtyDecimal();

    /**
     * Gets the is-virtual flag value for the order item.
     *
     * @return int Is-virtual flag value.
     */
    public function getIsVirtual();

    /**
     * Gets the item ID for the order item.
     *
     * @return int Item ID.
     */
    public function getItemId();

    /**
     * Gets the locked DO invoice flag value for the order item.
     *
     * @return int Locked DO invoice flag value.
     */
    public function getLockedDoInvoice();

    /**
     * Gets the locked DO ship flag value for the order item.
     *
     * @return int Locked DO ship flag value.
     */
    public function getLockedDoShip();

    /**
     * Gets the name for the order item.
     *
     * @return string Name.
     */
    public function getName();

    /**
     * Gets the no discount flag value for the order item.
     *
     * @return int No-discount flag value.
     */
    public function getNoDiscount();

    /**
     * Gets the order ID for the order item.
     *
     * @return int Order ID.
     */
    public function getOrderId();

    /**
     * Gets the original price for the order item.
     *
     * @return float Original price.
     */
    public function getOriginalPrice();

    /**
     * Gets the parent item ID for the order item.
     *
     * @return int Parent item ID.
     */
    public function getParentItemId();

    /**
     * Gets the price for the order item.
     *
     * @return float Price.
     */
    public function getPrice();

    /**
     * Gets the price including tax for the order item.
     *
     * @return float Price including tax.
     */
    public function getPriceInclTax();

    /**
     * Gets the product ID for the order item.
     *
     * @return int Product ID.
     */
    public function getProductId();

    /**
     * Gets the product options for the order item.
     *
     * @return string[] Array of product options.
     */
    public function getProductOptions();

    /**
     * Gets the product type for the order item.
     *
     * @return string Product type.
     */
    public function getProductType();

    /**
     * Gets the quantity backordered for the order item.
     *
     * @return float Quantity backordered.
     */
    public function getQtyBackordered();

    /**
     * Gets the quantity canceled for the order item.
     *
     * @return float Quantity canceled.
     */
    public function getQtyCanceled();

    /**
     * Gets the quantity invoiced for the order item.
     *
     * @return float Quantity invoiced.
     */
    public function getQtyInvoiced();

    /**
     * Gets the quantity ordered for the order item.
     *
     * @return float Quantity ordered.
     */
    public function getQtyOrdered();

    /**
     * Gets the quantity refunded for the order item.
     *
     * @return float Quantity refunded.
     */
    public function getQtyRefunded();

    /**
     * Gets the quantity returned for the order item.
     *
     * @return float Quantity returned.
     */
    public function getQtyReturned();

    /**
     * Gets the quantity shipped for the order item.
     *
     * @return float Quantity shipped.
     */
    public function getQtyShipped();

    /**
     * Gets the quote item ID for the order item.
     *
     * @return int Quote item ID.
     */
    public function getQuoteItemId();

    /**
     * Gets the row invoiced for the order item.
     *
     * @return float Row invoiced.
     */
    public function getRowInvoiced();

    /**
     * Gets the row total for the order item.
     *
     * @return float Row total.
     */
    public function getRowTotal();

    /**
     * Gets the row total including tax for the order item.
     *
     * @return float Row total including tax.
     */
    public function getRowTotalInclTax();

    /**
     * Gets the row weight for the order item.
     *
     * @return float Row weight.
     */
    public function getRowWeight();

    /**
     * Gets the SKU for the order item.
     *
     * @return string SKU.
     */
    public function getSku();

    /**
     * Gets the store ID for the order item.
     *
     * @return int Store ID.
     */
    public function getStoreId();

    /**
     * Gets the tax amount for the order item.
     *
     * @return float Tax amount.
     */
    public function getTaxAmount();

    /**
     * Gets the tax before discount for the order item.
     *
     * @return float Tax before discount.
     */
    public function getTaxBeforeDiscount();

    /**
     * Gets the tax canceled for the order item.
     *
     * @return float Tax canceled.
     */
    public function getTaxCanceled();

    /**
     * Gets the tax invoiced for the order item.
     *
     * @return float Tax invoiced.
     */
    public function getTaxInvoiced();

    /**
     * Gets the tax percent for the order item.
     *
     * @return float Tax percent.
     */
    public function getTaxPercent();

    /**
     * Gets the tax refunded for the order item.
     *
     * @return float Tax refunded.
     */
    public function getTaxRefunded();

    /**
     * Gets the updated-at timestamp for the order item.
     *
     * @return string Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the WEEE tax applied for the order item.
     *
     * @return string WEEE tax applied.
     */
    public function getWeeeTaxApplied();

    /**
     * Gets the WEEE tax applied amount for the order item.
     *
     * @return float WEEE tax applied amount.
     */
    public function getWeeeTaxAppliedAmount();

    /**
     * Gets the WEEE tax applied row amount for the order item.
     *
     * @return float WEEE tax applied row amount.
     */
    public function getWeeeTaxAppliedRowAmount();

    /**
     * Gets the WEEE tax disposition for the order item.
     *
     * @return float WEEE tax disposition.
     */
    public function getWeeeTaxDisposition();

    /**
     * Gets the WEEE tax row disposition for the order item.
     *
     * @return float WEEE tax row disposition.
     */
    public function getWeeeTaxRowDisposition();

    /**
     * Gets the weight for the order item.
     *
     * @return float Weight.
     */
    public function getWeight();
}
