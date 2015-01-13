<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Invoice interface.
 *
 * An invoice is a record of the receipt of payment for an order.
 */
interface InvoiceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
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
     * Base grand total.
     */
    const BASE_GRAND_TOTAL = 'base_grand_total';
    /*
     * Shipping tax amount.
     */
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    /*
     * Tax amount.
     */
    const TAX_AMOUNT = 'tax_amount';
    /*
     * Base tax amount.
     */
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    /*
     * Store-to-order rate.
     */
    const STORE_TO_ORDER_RATE = 'store_to_order_rate';
    /*
     * Base shipping tax amount.
     */
    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
    /*
     * Base discount amount.
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
     * Shipping amount.
     */
    const SHIPPING_AMOUNT = 'shipping_amount';
    /*
     * Subtotal including tax.
     */
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    /*
     * Base subtotal including tax.
     */
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    /*
     * Store-to-base rate.
     */
    const STORE_TO_BASE_RATE = 'store_to_base_rate';
    /*
     * Base shipping amount.
     */
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    /*
     * Total quantity.
     */
    const TOTAL_QTY = 'total_qty';
    /*
     * Base-to-global rate.
     */
    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    /*
     * Subtotal.
     */
    const SUBTOTAL = 'subtotal';
    /*
     * Base subtotal.
     */
    const BASE_SUBTOTAL = 'base_subtotal';
    /*
     * Discount amount.
     */
    const DISCOUNT_AMOUNT = 'discount_amount';
    /*
     * Billing address ID.
     */
    const BILLING_ADDRESS_ID = 'billing_address_id';
    /*
     * Is used for refund.
     */
    const IS_USED_FOR_REFUND = 'is_used_for_refund';
    /*
     * Order ID.
     */
    const ORDER_ID = 'order_id';
    /*
     * Email sent flag.
     */
    const EMAIL_SENT = 'email_sent';
    /*
     * Can void flag.
     */
    const CAN_VOID_FLAG = 'can_void_flag';
    /*
     * State.
     */
    const STATE = 'state';
    /*
     * Shipping address ID.
     */
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    /*
     * Store currency code.
     */
    const STORE_CURRENCY_CODE = 'store_currency_code';
    /*
     * Transaction ID.
     */
    const TRANSACTION_ID = 'transaction_id';
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
     * Base total refunded.
     */
    const BASE_TOTAL_REFUNDED = 'base_total_refunded';
    /*
     * Discount description.
     */
    const DISCOUNT_DESCRIPTION = 'discount_description';
    /*
     * Items.
     */
    const ITEMS = 'items';
    /*
     * Comments.
     */
    const COMMENTS = 'comments';

    /**
     * Gets the base currency code for the invoice.
     *
     * @return string Base currency code.
     */
    public function getBaseCurrencyCode();

    /**
     * Gets the base discount amount for the invoice.
     *
     * @return float Base discount amount.
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base grand total for the invoice.
     *
     * @return float Base grand total.
     */
    public function getBaseGrandTotal();

    /**
     * Gets the base hidden tax amount for the invoice.
     *
     * @return float Base hidden tax amount.
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Gets the base shipping amount for the invoice.
     *
     * @return float Base shipping amount.
     */
    public function getBaseShippingAmount();

    /**
     * Gets the base shipping hidden tax amount for the invoice.
     *
     * @return float Base shipping hidden tax amount.
     */
    public function getBaseShippingHiddenTaxAmnt();

    /**
     * Gets the base shipping including tax for the invoice.
     *
     * @return float Base shipping including tax.
     */
    public function getBaseShippingInclTax();

    /**
     * Gets the base shipping tax amount for the invoice.
     *
     * @return float Base shipping tax amount.
     */
    public function getBaseShippingTaxAmount();

    /**
     * Gets the base subtotal for the invoice.
     *
     * @return float Base subtotal.
     */
    public function getBaseSubtotal();

    /**
     * Gets the base subtotal including tax for the invoice.
     *
     * @return float Base subtotal including tax.
     */
    public function getBaseSubtotalInclTax();

    /**
     * Gets the base tax amount for the invoice.
     *
     * @return float Base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the base total refunded for the invoice.
     *
     * @return float Base total refunded.
     */
    public function getBaseTotalRefunded();

    /**
     * Gets the base-to-global rate for the invoice.
     *
     * @return float Base-to-global rate.
     */
    public function getBaseToGlobalRate();

    /**
     * Gets the base-to-order rate for the invoice.
     *
     * @return float Base-to-order rate.
     */
    public function getBaseToOrderRate();

    /**
     * Gets the billing address ID for the invoice.
     *
     * @return int Billing address ID.
     */
    public function getBillingAddressId();

    /**
     * Gets the can void flag value for the invoice.
     *
     * @return int Can void flag value.
     */
    public function getCanVoidFlag();

    /**
     * Gets the created-at timestamp for the invoice.
     *
     * @return string Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Gets the discount amount for the invoice.
     *
     * @return float Discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the discount description for the invoice.
     *
     * @return string Discount description.
     */
    public function getDiscountDescription();

    /**
     * Gets the email-sent flag value for the invoice.
     *
     * @return int Email-sent flag value.
     */
    public function getEmailSent();

    /**
     * Gets the ID for the invoice.
     *
     * @return int Invoice ID.
     */
    public function getEntityId();

    /**
     * Gets the global currency code for the invoice.
     *
     * @return string Global currency code.
     */
    public function getGlobalCurrencyCode();

    /**
     * Gets the grand total for the invoice.
     *
     * @return float Grand total.
     */
    public function getGrandTotal();

    /**
     * Gets the hidden tax amount for the invoice.
     *
     * @return float Hidden tax amount.
     */
    public function getHiddenTaxAmount();

    /**
     * Gets the increment ID for the invoice.
     *
     * @return string Increment ID.
     */
    public function getIncrementId();

    /**
     * Gets the is-used-for-refund flag value for the invoice.
     *
     * @return int Is-used-for-refund flag value.
     */
    public function getIsUsedForRefund();

    /**
     * Gets the order currency code for the invoice.
     *
     * @return string Order currency code.
     */
    public function getOrderCurrencyCode();

    /**
     * Gets the order ID for the invoice.
     *
     * @return int Order ID.
     */
    public function getOrderId();

    /**
     * Gets the shipping address ID for the invoice.
     *
     * @return int Shipping address ID.
     */
    public function getShippingAddressId();

    /**
     * Gets the shipping amount for the invoice.
     *
     * @return float Shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the shipping hidden tax amount for the invoice.
     *
     * @return float Shipping hidden tax amount.
     */
    public function getShippingHiddenTaxAmount();

    /**
     * Gets the shipping including tax for the invoice.
     *
     * @return float Shipping including tax.
     */
    public function getShippingInclTax();

    /**
     * Gets the shipping tax amount for the invoice.
     *
     * @return float Shipping tax amount.
     */
    public function getShippingTaxAmount();

    /**
     * Gets the state for the invoice.
     *
     * @return int State.
     */
    public function getState();

    /**
     * Gets the store currency code for the invoice.
     *
     * @return string Store currency code.
     */
    public function getStoreCurrencyCode();

    /**
     * Gets the store ID for the invoice.
     *
     * @return int Store ID.
     */
    public function getStoreId();

    /**
     * Gets the store-to-base rate for the invoice.
     *
     * @return float Store-to-base rate.
     */
    public function getStoreToBaseRate();

    /**
     * Gets the store-to-order rate for the invoice.
     *
     * @return float Store-to-order rate.
     */
    public function getStoreToOrderRate();

    /**
     * Gets the subtotal for the invoice.
     *
     * @return float Subtotal.
     */
    public function getSubtotal();

    /**
     * Gets the subtotal including tax for the invoice.
     *
     * @return float Subtotal including tax.
     */
    public function getSubtotalInclTax();

    /**
     * Gets the tax amount for the invoice.
     *
     * @return float Tax amount.
     */
    public function getTaxAmount();

    /**
     * Gets the total quantity for the invoice.
     *
     * @return float Total quantity.
     */
    public function getTotalQty();

    /**
     * Gets the transaction ID for the invoice.
     *
     * @return string Transaction ID.
     */
    public function getTransactionId();

    /**
     * Gets the updated-at timestamp for the invoice.
     *
     * @return string Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the items in the invoice.
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface[] Array of invoice items.
     */
    public function getItems();

    /**
     * Gets the comments, if any, for the invoice.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface[]|null Array of any invoice comments. Otherwise, null.
     */
    public function getComments();
}
