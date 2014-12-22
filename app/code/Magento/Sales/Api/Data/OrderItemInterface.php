<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface OrderItemInterface
 */
interface OrderItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ITEM_ID = 'item_id';
    const ORDER_ID = 'order_id';
    const PARENT_ITEM_ID = 'parent_item_id';
    const QUOTE_ITEM_ID = 'quote_item_id';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const PRODUCT_ID = 'product_id';
    const PRODUCT_TYPE = 'product_type';
    const PRODUCT_OPTIONS = 'product_options';
    const WEIGHT = 'weight';
    const IS_VIRTUAL = 'is_virtual';
    const SKU = 'sku';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const APPLIED_RULE_IDS = 'applied_rule_ids';
    const ADDITIONAL_DATA = 'additional_data';
    const IS_QTY_DECIMAL = 'is_qty_decimal';
    const NO_DISCOUNT = 'no_discount';
    const QTY_BACKORDERED = 'qty_backordered';
    const QTY_CANCELED = 'qty_canceled';
    const QTY_INVOICED = 'qty_invoiced';
    const QTY_ORDERED = 'qty_ordered';
    const QTY_REFUNDED = 'qty_refunded';
    const QTY_SHIPPED = 'qty_shipped';
    const BASE_COST = 'base_cost';
    const PRICE = 'price';
    const BASE_PRICE = 'base_price';
    const ORIGINAL_PRICE = 'original_price';
    const BASE_ORIGINAL_PRICE = 'base_original_price';
    const TAX_PERCENT = 'tax_percent';
    const TAX_AMOUNT = 'tax_amount';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const TAX_INVOICED = 'tax_invoiced';
    const BASE_TAX_INVOICED = 'base_tax_invoiced';
    const DISCOUNT_PERCENT = 'discount_percent';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const DISCOUNT_INVOICED = 'discount_invoiced';
    const BASE_DISCOUNT_INVOICED = 'base_discount_invoiced';
    const AMOUNT_REFUNDED = 'amount_refunded';
    const BASE_AMOUNT_REFUNDED = 'base_amount_refunded';
    const ROW_TOTAL = 'row_total';
    const BASE_ROW_TOTAL = 'base_row_total';
    const ROW_INVOICED = 'row_invoiced';
    const BASE_ROW_INVOICED = 'base_row_invoiced';
    const ROW_WEIGHT = 'row_weight';
    const BASE_TAX_BEFORE_DISCOUNT = 'base_tax_before_discount';
    const TAX_BEFORE_DISCOUNT = 'tax_before_discount';
    const EXT_ORDER_ITEM_ID = 'ext_order_item_id';
    const LOCKED_DO_INVOICE = 'locked_do_invoice';
    const LOCKED_DO_SHIP = 'locked_do_ship';
    const PRICE_INCL_TAX = 'price_incl_tax';
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    const HIDDEN_TAX_INVOICED = 'hidden_tax_invoiced';
    const BASE_HIDDEN_TAX_INVOICED = 'base_hidden_tax_invoiced';
    const HIDDEN_TAX_REFUNDED = 'hidden_tax_refunded';
    const BASE_HIDDEN_TAX_REFUNDED = 'base_hidden_tax_refunded';
    const TAX_CANCELED = 'tax_canceled';
    const HIDDEN_TAX_CANCELED = 'hidden_tax_canceled';
    const TAX_REFUNDED = 'tax_refunded';
    const BASE_TAX_REFUNDED = 'base_tax_refunded';
    const DISCOUNT_REFUNDED = 'discount_refunded';
    const BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    const GW_ID = 'gw_id';
    const GW_BASE_PRICE = 'gw_base_price';
    const GW_PRICE = 'gw_price';
    const GW_BASE_TAX_AMOUNT = 'gw_base_tax_amount';
    const GW_TAX_AMOUNT = 'gw_tax_amount';
    const GW_BASE_PRICE_INVOICED = 'gw_base_price_invoiced';
    const GW_PRICE_INVOICED = 'gw_price_invoiced';
    const GW_BASE_TAX_AMOUNT_INVOICED = 'gw_base_tax_amount_invoiced';
    const GW_TAX_AMOUNT_INVOICED = 'gw_tax_amount_invoiced';
    const GW_BASE_PRICE_REFUNDED = 'gw_base_price_refunded';
    const GW_PRICE_REFUNDED = 'gw_price_refunded';
    const GW_BASE_TAX_AMOUNT_REFUNDED = 'gw_base_tax_amount_refunded';
    const GW_TAX_AMOUNT_REFUNDED = 'gw_tax_amount_refunded';
    const FREE_SHIPPING = 'free_shipping';
    const QTY_RETURNED = 'qty_returned';
    const EVENT_ID = 'event_id';
    const BASE_WEEE_TAX_APPLIED_AMOUNT = 'base_weee_tax_applied_amount';
    const BASE_WEEE_TAX_APPLIED_ROW_AMNT = 'base_weee_tax_applied_row_amnt';
    const WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';
    const WEEE_TAX_APPLIED_ROW_AMOUNT = 'weee_tax_applied_row_amount';
    const WEEE_TAX_APPLIED = 'weee_tax_applied';
    const WEEE_TAX_DISPOSITION = 'weee_tax_disposition';
    const WEEE_TAX_ROW_DISPOSITION = 'weee_tax_row_disposition';
    const BASE_WEEE_TAX_DISPOSITION = 'base_weee_tax_disposition';
    const BASE_WEEE_TAX_ROW_DISPOSITION = 'base_weee_tax_row_disposition';

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData();

    /**
     * Returns amount_refunded
     *
     * @return float
     */
    public function getAmountRefunded();

    /**
     * Returns applied_rule_ids
     *
     * @return string
     */
    public function getAppliedRuleIds();

    /**
     * Returns base_amount_refunded
     *
     * @return float
     */
    public function getBaseAmountRefunded();

    /**
     * Returns base_cost
     *
     * @return float
     */
    public function getBaseCost();

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount();

    /**
     * Returns base_discount_invoiced
     *
     * @return float
     */
    public function getBaseDiscountInvoiced();

    /**
     * Returns base_discount_refunded
     *
     * @return float
     */
    public function getBaseDiscountRefunded();

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Returns base_hidden_tax_invoiced
     *
     * @return float
     */
    public function getBaseHiddenTaxInvoiced();

    /**
     * Returns base_hidden_tax_refunded
     *
     * @return float
     */
    public function getBaseHiddenTaxRefunded();

    /**
     * Returns base_original_price
     *
     * @return float
     */
    public function getBaseOriginalPrice();

    /**
     * Returns base_price
     *
     * @return float
     */
    public function getBasePrice();

    /**
     * Returns base_price_incl_tax
     *
     * @return float
     */
    public function getBasePriceInclTax();

    /**
     * Returns base_row_invoiced
     *
     * @return float
     */
    public function getBaseRowInvoiced();

    /**
     * Returns base_row_total
     *
     * @return float
     */
    public function getBaseRowTotal();

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float
     */
    public function getBaseRowTotalInclTax();

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount();

    /**
     * Returns base_tax_before_discount
     *
     * @return float
     */
    public function getBaseTaxBeforeDiscount();

    /**
     * Returns base_tax_invoiced
     *
     * @return float
     */
    public function getBaseTaxInvoiced();

    /**
     * Returns base_tax_refunded
     *
     * @return float
     */
    public function getBaseTaxRefunded();

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedAmount();

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedRowAmnt();

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxDisposition();

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxRowDisposition();

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Returns discount_invoiced
     *
     * @return float
     */
    public function getDiscountInvoiced();

    /**
     * Returns discount_percent
     *
     * @return float
     */
    public function getDiscountPercent();

    /**
     * Returns discount_refunded
     *
     * @return float
     */
    public function getDiscountRefunded();

    /**
     * Returns event_id
     *
     * @return int
     */
    public function getEventId();

    /**
     * Returns ext_order_item_id
     *
     * @return string
     */
    public function getExtOrderItemId();

    /**
     * Returns free_shipping
     *
     * @return int
     */
    public function getFreeShipping();

    /**
     * Returns gw_base_price
     *
     * @return float
     */
    public function getGwBasePrice();

    /**
     * Returns gw_base_price_invoiced
     *
     * @return float
     */
    public function getGwBasePriceInvoiced();

    /**
     * Returns gw_base_price_refunded
     *
     * @return float
     */
    public function getGwBasePriceRefunded();

    /**
     * Returns gw_base_tax_amount
     *
     * @return float
     */
    public function getGwBaseTaxAmount();

    /**
     * Returns gw_base_tax_amount_invoiced
     *
     * @return float
     */
    public function getGwBaseTaxAmountInvoiced();

    /**
     * Returns gw_base_tax_amount_refunded
     *
     * @return float
     */
    public function getGwBaseTaxAmountRefunded();

    /**
     * Returns gw_id
     *
     * @return int
     */
    public function getGwId();

    /**
     * Returns gw_price
     *
     * @return float
     */
    public function getGwPrice();

    /**
     * Returns gw_price_invoiced
     *
     * @return float
     */
    public function getGwPriceInvoiced();

    /**
     * Returns gw_price_refunded
     *
     * @return float
     */
    public function getGwPriceRefunded();

    /**
     * Returns gw_tax_amount
     *
     * @return float
     */
    public function getGwTaxAmount();

    /**
     * Returns gw_tax_amount_invoiced
     *
     * @return float
     */
    public function getGwTaxAmountInvoiced();

    /**
     * Returns gw_tax_amount_refunded
     *
     * @return float
     */
    public function getGwTaxAmountRefunded();

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount();

    /**
     * Returns hidden_tax_canceled
     *
     * @return float
     */
    public function getHiddenTaxCanceled();

    /**
     * Returns hidden_tax_invoiced
     *
     * @return float
     */
    public function getHiddenTaxInvoiced();

    /**
     * Returns hidden_tax_refunded
     *
     * @return float
     */
    public function getHiddenTaxRefunded();

    /**
     * Returns is_qty_decimal
     *
     * @return int
     */
    public function getIsQtyDecimal();

    /**
     * Returns is_virtual
     *
     * @return int
     */
    public function getIsVirtual();

    /**
     * Returns item_id
     *
     * @return int
     */
    public function getItemId();

    /**
     * Returns locked_do_invoice
     *
     * @return int
     */
    public function getLockedDoInvoice();

    /**
     * Returns locked_do_ship
     *
     * @return int
     */
    public function getLockedDoShip();

    /**
     * Returns name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns no_discount
     *
     * @return int
     */
    public function getNoDiscount();

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Returns original_price
     *
     * @return float
     */
    public function getOriginalPrice();

    /**
     * Returns parent_item_id
     *
     * @return int
     */
    public function getParentItemId();

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Returns price_incl_tax
     *
     * @return float
     */
    public function getPriceInclTax();

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Returns product_options
     *
     * @return string[]
     */
    public function getProductOptions();

    /**
     * Returns product_type
     *
     * @return string
     */
    public function getProductType();

    /**
     * Returns qty_backordered
     *
     * @return float
     */
    public function getQtyBackordered();

    /**
     * Returns qty_canceled
     *
     * @return float
     */
    public function getQtyCanceled();

    /**
     * Returns qty_invoiced
     *
     * @return float
     */
    public function getQtyInvoiced();

    /**
     * Returns qty_ordered
     *
     * @return float
     */
    public function getQtyOrdered();

    /**
     * Returns qty_refunded
     *
     * @return float
     */
    public function getQtyRefunded();

    /**
     * Returns qty_returned
     *
     * @return float
     */
    public function getQtyReturned();

    /**
     * Returns qty_shipped
     *
     * @return float
     */
    public function getQtyShipped();

    /**
     * Returns quote_item_id
     *
     * @return int
     */
    public function getQuoteItemId();

    /**
     * Returns row_invoiced
     *
     * @return float
     */
    public function getRowInvoiced();

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal();

    /**
     * Returns row_total_incl_tax
     *
     * @return float
     */
    public function getRowTotalInclTax();

    /**
     * Returns row_weight
     *
     * @return float
     */
    public function getRowWeight();

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount();

    /**
     * Returns tax_before_discount
     *
     * @return float
     */
    public function getTaxBeforeDiscount();

    /**
     * Returns tax_canceled
     *
     * @return float
     */
    public function getTaxCanceled();

    /**
     * Returns tax_invoiced
     *
     * @return float
     */
    public function getTaxInvoiced();

    /**
     * Returns tax_percent
     *
     * @return float
     */
    public function getTaxPercent();

    /**
     * Returns tax_refunded
     *
     * @return float
     */
    public function getTaxRefunded();

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Returns weee_tax_applied
     *
     * @return string
     */
    public function getWeeeTaxApplied();

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedAmount();

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedRowAmount();

    /**
     * Returns weee_tax_disposition
     *
     * @return float
     */
    public function getWeeeTaxDisposition();

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float
     */
    public function getWeeeTaxRowDisposition();

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight();
}
