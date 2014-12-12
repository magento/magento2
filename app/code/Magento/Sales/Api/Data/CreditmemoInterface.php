<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface CreditmemoInterface
 */
interface CreditmemoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const STORE_ID = 'store_id';
    const ADJUSTMENT_POSITIVE = 'adjustment_positive';
    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
    const STORE_TO_ORDER_RATE = 'store_to_order_rate';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const BASE_TO_ORDER_RATE = 'base_to_order_rate';
    const GRAND_TOTAL = 'grand_total';
    const BASE_ADJUSTMENT_NEGATIVE = 'base_adjustment_negative';
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    const ADJUSTMENT_NEGATIVE = 'adjustment_negative';
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    const STORE_TO_BASE_RATE = 'store_to_base_rate';
    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    const BASE_ADJUSTMENT = 'base_adjustment';
    const BASE_SUBTOTAL = 'base_subtotal';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const SUBTOTAL = 'subtotal';
    const ADJUSTMENT = 'adjustment';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const BASE_ADJUSTMENT_POSITIVE = 'base_adjustment_positive';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    const TAX_AMOUNT = 'tax_amount';
    const ORDER_ID = 'order_id';
    const EMAIL_SENT = 'email_sent';
    const CREDITMEMO_STATUS = 'creditmemo_status';
    const STATE = 'state';
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    const BILLING_ADDRESS_ID = 'billing_address_id';
    const INVOICE_ID = 'invoice_id';
    const STORE_CURRENCY_CODE = 'store_currency_code';
    const ORDER_CURRENCY_CODE = 'order_currency_code';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
    const TRANSACTION_ID = 'transaction_id';
    const INCREMENT_ID = 'increment_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    const SHIPPING_HIDDEN_TAX_AMOUNT = 'shipping_hidden_tax_amount';
    const BASE_SHIPPING_HIDDEN_TAX_AMNT = 'base_shipping_hidden_tax_amnt';
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    const DISCOUNT_DESCRIPTION = 'discount_description';
    const ITEMS = 'items';
    const COMMENTS = 'comments';

    /**
     * Returns adjustment
     *
     * @return float
     */
    public function getAdjustment();

    /**
     * Returns adjustment_negative
     *
     * @return float
     */
    public function getAdjustmentNegative();

    /**
     * Returns adjustment_positive
     *
     * @return float
     */
    public function getAdjustmentPositive();

    /**
     * Returns base_adjustment
     *
     * @return float
     */
    public function getBaseAdjustment();

    /**
     * Returns base_adjustment_negative
     *
     * @return float
     */
    public function getBaseAdjustmentNegative();

    /**
     * Returns base_adjustment_positive
     *
     * @return float
     */
    public function getBaseAdjustmentPositive();

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
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt();
    /**
     * Returns creditmemo_status
     *
     * @return int
     */
    public function getCreditmemoStatus();

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
     * Returns invoice_id
     *
     * @return int
     */
    public function getInvoiceId();

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
     * Return creditmemo items
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface[]
     */
    public function getItems();

    /**
     * Return creditmemo comments
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCommentInterface[]|null
     */
    public function getComments();
}
