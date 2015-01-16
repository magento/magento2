<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Credit memo interface.
 *
 * After a customer places and pays for an order and an invoice has been issued, the merchant can create a credit memo
 * to refund all or part of the amount paid for any returned or undelivered items. The memo restores funds to the
 * customer account so that the customer can make future purchases.
 */
interface CreditmemoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Store ID.
     */
    const STORE_ID = 'store_id';
    /*
     * Positive adjustment.
     */
    const ADJUSTMENT_POSITIVE = 'adjustment_positive';
    /*
     * Base shipping tax amount.
     */
    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
    /*
     * Store-to-order rate.
     */
    const STORE_TO_ORDER_RATE = 'store_to_order_rate';
    /*
     * Base discount rate.
     */
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    /*
     * Base-to-order rate.
     */
    const BASE_TO_ORDER_RATE = 'base_to_order_rate';
    /*
     * Grand total.
     */
    const GRAND_TOTAL = 'grand_total';
    /*
     * Negative base adjustment.
     */
    const BASE_ADJUSTMENT_NEGATIVE = 'base_adjustment_negative';
    /*
     * Base subtotal including tax.
     */
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    /*
     * Shipping amount.
     */
    const SHIPPING_AMOUNT = 'shipping_amount';
    /*
     * Subtotal including tax.
     */
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    /*
     * Negative adjustment.
     */
    const ADJUSTMENT_NEGATIVE = 'adjustment_negative';
    /*
     * Base shipping amount.
     */
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    /*
     * Store-to-base rate.
     */
    const STORE_TO_BASE_RATE = 'store_to_base_rate';
    /*
     * Base-to-global rate.
     */
    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    /*
     * Base adjustment.
     */
    const BASE_ADJUSTMENT = 'base_adjustment';
    /*
     * Base subtotal.
     */
    const BASE_SUBTOTAL = 'base_subtotal';
    /*
     * Discount amount.
     */
    const DISCOUNT_AMOUNT = 'discount_amount';
    /*
     * Subtotal.
     */
    const SUBTOTAL = 'subtotal';
    /*
     * Subtotal.
     */
    const ADJUSTMENT = 'adjustment';
    /*
     * Base grand total.
     */
    const BASE_GRAND_TOTAL = 'base_grand_total';
    /*
     * Positive base adjustment.
     */
    const BASE_ADJUSTMENT_POSITIVE = 'base_adjustment_positive';
    /*
     * Base tax amount.
     */
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    /*
     * Shipping tax amount.
     */
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    /*
     * Tax amount.
     */
    const TAX_AMOUNT = 'tax_amount';
    /*
     * Order ID.
     */
    const ORDER_ID = 'order_id';
    /*
     * Email sent flag.
     */
    const EMAIL_SENT = 'email_sent';
    /*
     * Credit memo status.
     */
    const CREDITMEMO_STATUS = 'creditmemo_status';
    /*
     * Credit memo state.
     */
    const STATE = 'state';
    /*
     * Shipping address ID.
     */
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    /*
     * Billing address ID.
     */
    const BILLING_ADDRESS_ID = 'billing_address_id';
    /*
     * Invoice ID.
     */
    const INVOICE_ID = 'invoice_id';
    /*
     * Store currency code.
     */
    const STORE_CURRENCY_CODE = 'store_currency_code';
    /*
     * Order currency code.
     */
    const ORDER_CURRENCY_CODE = 'order_currency_code';
    /*
     * Base currency code.
     */
    const BASE_CURRENCY_CODE = 'base_currency_code';
    /*
     * Global currency code.
     */
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
    /*
     * Transaction ID.
     */
    const TRANSACTION_ID = 'transaction_id';
    /*
     * Increment ID.
     */
    const INCREMENT_ID = 'increment_id';
    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Updated-at timestamp.
     */
    const UPDATED_AT = 'updated_at';
    /*
     * Hidden tax amount.
     */
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    /*
     * Base hidden tax amount.
     */
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    /*
     * Shipping hidden tax amount.
     */
    const SHIPPING_HIDDEN_TAX_AMOUNT = 'shipping_hidden_tax_amount';
    /*
     * Base shipping hidden tax amount.
     */
    const BASE_SHIPPING_HIDDEN_TAX_AMNT = 'base_shipping_hidden_tax_amnt';
    /*
     * Shipping including tax.
     */
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    /*
     * Base shipping including tax.
     */
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    /*
     * Discount description.
     */
    const DISCOUNT_DESCRIPTION = 'discount_description';
    /*
     * Credit memo items.
     */
    const ITEMS = 'items';
    /*
     * Credit memo comments.
     */
    const COMMENTS = 'comments';

    /**
     * Gets the credit memo adjustment.
     *
     * @return float Credit memo adjustment.
     */
    public function getAdjustment();

    /**
     * Gets the credit memo negative adjustment.
     *
     * @return float Credit memo negative adjustment.
     */
    public function getAdjustmentNegative();

    /**
     * Gets the credit memo positive adjustment.
     *
     * @return float Credit memo positive adjustment.
     */
    public function getAdjustmentPositive();

    /**
     * Gets the credit memo base adjustment.
     *
     * @return float Credit memo base adjustment.
     */
    public function getBaseAdjustment();

    /**
     * Gets the credit memo negative base adjustment.
     *
     * @return float Credit memo negative base adjustment.
     */
    public function getBaseAdjustmentNegative();

    /**
     * Gets the credit memo positive base adjustment.
     *
     * @return float Credit memo positive base adjustment.
     */
    public function getBaseAdjustmentPositive();

    /**
     * Gets the credit memo base currency code.
     *
     * @return string Credit memo base currency code.
     */
    public function getBaseCurrencyCode();

    /**
     * Gets the credit memo base discount amount.
     *
     * @return float Credit memo base discount amount.
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the credit memo base grand total.
     *
     * @return float Credit memo base grand total.
     */
    public function getBaseGrandTotal();

    /**
     * Gets the credit memo base hidden tax amount.
     *
     * @return float Credit memo base hidden tax amount.
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Gets the credit memo base shipping amount.
     *
     * @return float Credit memo base shipping amount.
     */
    public function getBaseShippingAmount();

    /**
     * Gets the credit memo base shipping hidden tax amount.
     *
     * @return float Credit memo base shipping hidden tax amount.
     */
    public function getBaseShippingHiddenTaxAmnt();

    /**
     * Gets the credit memo base shipping including tax.
     *
     * @return float Credit memo base shipping including tax.
     */
    public function getBaseShippingInclTax();

    /**
     * Gets the credit memo base shipping tax amount.
     *
     * @return float Credit memo base shipping tax amount.
     */
    public function getBaseShippingTaxAmount();

    /**
     * Gets the credit memo base subtotal.
     *
     * @return float Credit memo base subtotal.
     */
    public function getBaseSubtotal();

    /**
     * Gets the credit memo base subtotal including tax.
     *
     * @return float Credit memo base subtotal including tax.
     */
    public function getBaseSubtotalInclTax();

    /**
     * Gets the credit memo base tax amount.
     *
     * @return float Credit memo base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the credit memo base-to-global rate.
     *
     * @return float Credit memo base-to-global rate.
     */
    public function getBaseToGlobalRate();

    /**
     * Gets the credit memo base-to-order rate.
     *
     * @return float Credit memo base-to-order rate.
     */
    public function getBaseToOrderRate();

    /**
     * Gets the credit memo billing address ID.
     *
     * @return int Credit memo billing address ID.
     */
    public function getBillingAddressId();

    /**
     * Gets the credit memo created-at timestamp.
     *
     * @return string Credit memo created-at timestamp.
     */
    public function getCreatedAt();
    /**
     * Gets the credit memo status.
     *
     * @return int Credit memo status.
     */
    public function getCreditmemoStatus();

    /**
     * Gets the credit memo discount amount.
     *
     * @return float Credit memo discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the credit memo discount description.
     *
     * @return string Credit memo discount description.
     */
    public function getDiscountDescription();

    /**
     * Gets the credit memo email sent flag value.
     *
     * @return int Credit memo email sent flag value.
     */
    public function getEmailSent();

    /**
     * Gets the credit memo ID.
     *
     * @return int Credit memo ID.
     */
    public function getEntityId();
    /**
     * Gets the credit memo global currency code.
     *
     * @return string Credit memo global currency code.
     */
    public function getGlobalCurrencyCode();

    /**
     * Gets the credit memo grand total.
     *
     * @return float Credit memo grand total.
     */
    public function getGrandTotal();

    /**
     * Gets the credit memo hidden tax amount.
     *
     * @return float Credit memo hidden tax amount.
     */
    public function getHiddenTaxAmount();

    /**
     * Gets the credit memo increment ID.
     *
     * @return string Credit memo increment ID.
     */
    public function getIncrementId();

    /**
     * Gets the credit memo invoice ID.
     *
     * @return int Credit memo invoice ID.
     */
    public function getInvoiceId();

    /**
     * Gets the credit memo order currency code.
     *
     * @return string Credit memo order currency code.
     */
    public function getOrderCurrencyCode();

    /**
     * Gets the credit memo order ID.
     *
     * @return int Credit memo order ID.
     */
    public function getOrderId();

    /**
     * Gets the credit memo shipping address ID.
     *
     * @return int Credit memo shipping address ID.
     */
    public function getShippingAddressId();
    /**
     * Gets the credit memo shipping amount.
     *
     * @return float Credit memo shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the credit memo shipping hidden tax amount.
     *
     * @return float Credit memo shipping hidden tax amount.
     */
    public function getShippingHiddenTaxAmount();

    /**
     * Gets the credit memo shipping including tax.
     *
     * @return float Credit memo shipping including tax.
     */
    public function getShippingInclTax();

    /**
     * Gets the credit memo shipping tax amount.
     *
     * @return float Credit memo shipping tax amount.
     */
    public function getShippingTaxAmount();

    /**
     * Gets the credit memo state.
     *
     * @return int Credit memo state.
     */
    public function getState();

    /**
     * Gets the credit memo store currency code.
     *
     * @return string Credit memo store currency code.
     */
    public function getStoreCurrencyCode();

    /**
     * Gets the credit memo store ID.
     *
     * @return int Credit memo store ID.
     */
    public function getStoreId();

    /**
     * Gets the credit memo store-to-base rate.
     *
     * @return float Credit memo store-to-base rate.
     */
    public function getStoreToBaseRate();

    /**
     * Gets the credit memo store-to-order rate.
     *
     * @return float Credit memo store-to-order rate.
     */
    public function getStoreToOrderRate();

    /**
     * Gets the credit memo subtotal.
     *
     * @return float Credit memo subtotal.
     */
    public function getSubtotal();

    /**
     * Gets the credit memo subtotal including tax.
     *
     * @return float Credit memo subtotal including tax.
     */
    public function getSubtotalInclTax();

    /**
     * Gets the credit memo tax amount.
     *
     * @return float Credit memo tax amount.
     */
    public function getTaxAmount();

    /**
     * Gets the credit memo transaction ID.
     *
     * @return string Credit memo transaction ID.
     */
    public function getTransactionId();

    /**
     * Gets the credit memo updated-at timestamp.
     *
     * @return string Credit memo updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets credit memo items.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface[] Array of credit memo items.
     */
    public function getItems();

    /**
     * Gets credit memo comments.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCommentInterface[]|null Array of any credit memo comments. Otherwise, null.
     */
    public function getComments();
}
