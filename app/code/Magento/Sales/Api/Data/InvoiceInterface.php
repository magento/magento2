<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface InvoiceInterface
 */
interface InvoiceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const STORE_ID = 'store_id';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    const TAX_AMOUNT = 'tax_amount';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const STORE_TO_ORDER_RATE = 'store_to_order_rate';
    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const BASE_TO_ORDER_RATE = 'base_to_order_rate';
    const GRAND_TOTAL = 'grand_total';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    const STORE_TO_BASE_RATE = 'store_to_base_rate';
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    const TOTAL_QTY = 'total_qty';
    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    const SUBTOTAL = 'subtotal';
    const BASE_SUBTOTAL = 'base_subtotal';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const BILLING_ADDRESS_ID = 'billing_address_id';
    const IS_USED_FOR_REFUND = 'is_used_for_refund';
    const ORDER_ID = 'order_id';
    const EMAIL_SENT = 'email_sent';
    const CAN_VOID_FLAG = 'can_void_flag';
    const STATE = 'state';
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    const STORE_CURRENCY_CODE = 'store_currency_code';
    const TRANSACTION_ID = 'transaction_id';
    const ORDER_CURRENCY_CODE = 'order_currency_code';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
    const INCREMENT_ID = 'increment_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    const SHIPPING_HIDDEN_TAX_AMOUNT = 'shipping_hidden_tax_amount';
    const BASE_SHIPPING_HIDDEN_TAX_AMNT = 'base_shipping_hidden_tax_amnt';
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    const BASE_TOTAL_REFUNDED = 'base_total_refunded';
    const DISCOUNT_DESCRIPTION = 'discount_description';
    const ITEMS = 'items';
    const COMMENTS = 'comments';

    /**
     * Returns base_currency_code
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount();

    /**
     * Returns base_grand_total
     *
     * @return float
     */
    public function getBaseGrandTotal();

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Returns base_shipping_amount
     *
     * @return float
     */
    public function getBaseShippingAmount();

    /**
     * Returns base_shipping_hidden_tax_amnt
     *
     * @return float
     */
    public function getBaseShippingHiddenTaxAmnt();

    /**
     * Returns base_shipping_incl_tax
     *
     * @return float
     */
    public function getBaseShippingInclTax();

    /**
     * Returns base_shipping_tax_amount
     *
     * @return float
     */
    public function getBaseShippingTaxAmount();

    /**
     * Returns base_subtotal
     *
     * @return float
     */
    public function getBaseSubtotal();

    /**
     * Returns base_subtotal_incl_tax
     *
     * @return float
     */
    public function getBaseSubtotalInclTax();

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount();

    /**
     * Returns base_total_refunded
     *
     * @return float
     */
    public function getBaseTotalRefunded();

    /**
     * Returns base_to_global_rate
     *
     * @return float
     */
    public function getBaseToGlobalRate();

    /**
     * Returns base_to_order_rate
     *
     * @return float
     */
    public function getBaseToOrderRate();

    /**
     * Returns billing_address_id
     *
     * @return int
     */
    public function getBillingAddressId();

    /**
     * Returns can_void_flag
     *
     * @return int
     */
    public function getCanVoidFlag();

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Returns discount_description
     *
     * @return string
     */
    public function getDiscountDescription();

    /**
     * Returns email_sent
     *
     * @return int
     */
    public function getEmailSent();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Returns global_currency_code
     *
     * @return string
     */
    public function getGlobalCurrencyCode();

    /**
     * Returns grand_total
     *
     * @return float
     */
    public function getGrandTotal();

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount();

    /**
     * Returns increment_id
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Returns is_used_for_refund
     *
     * @return int
     */
    public function getIsUsedForRefund();

    /**
     * Returns order_currency_code
     *
     * @return string
     */
    public function getOrderCurrencyCode();

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Returns shipping_address_id
     *
     * @return int
     */
    public function getShippingAddressId();

    /**
     * Returns shipping_amount
     *
     * @return float
     */
    public function getShippingAmount();

    /**
     * Returns shipping_hidden_tax_amount
     *
     * @return float
     */
    public function getShippingHiddenTaxAmount();

    /**
     * Returns shipping_incl_tax
     *
     * @return float
     */
    public function getShippingInclTax();

    /**
     * Returns shipping_tax_amount
     *
     * @return float
     */
    public function getShippingTaxAmount();

    /**
     * Returns state
     *
     * @return int
     */
    public function getState();

    /**
     * Returns store_currency_code
     *
     * @return string
     */
    public function getStoreCurrencyCode();

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Returns store_to_base_rate
     *
     * @return float
     */
    public function getStoreToBaseRate();

    /**
     * Returns store_to_order_rate
     *
     * @return float
     */
    public function getStoreToOrderRate();

    /**
     * Returns subtotal
     *
     * @return float
     */
    public function getSubtotal();

    /**
     * Returns subtotal_incl_tax
     *
     * @return float
     */
    public function getSubtotalInclTax();

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount();

    /**
     * Returns total_qty
     *
     * @return float
     */
    public function getTotalQty();

    /**
     * Returns transaction_id
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Returns invoice items
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface[]
     */
    public function getItems();

    /**
     * Return invoice comments
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface[]|null
     */
    public function getComments();
}
