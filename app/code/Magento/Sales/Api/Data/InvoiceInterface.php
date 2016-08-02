<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Invoice interface.
 *
 * An invoice is a record of the receipt of payment for an order.
 * @api
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
     * Discount tax compensation amount.
     */
    const DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
    /*
     * Base discount tax compensation amount.
     */
    const BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'base_discount_tax_compensation_amount';
    /*
     * Shipping discount tax compensation amount.
     */
    const SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'shipping_discount_tax_compensation_amount';
    /*
     * Base shipping discount tax compensation amount.
     */
    const BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT = 'base_shipping_discount_tax_compensation_amnt';
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
     * @return string|null Base currency code.
     */
    public function getBaseCurrencyCode();

    /**
     * Gets the base discount amount for the invoice.
     *
     * @return float|null Base discount amount.
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base grand total for the invoice.
     *
     * @return float|null Base grand total.
     */
    public function getBaseGrandTotal();

    /**
     * Gets the base discount tax compensation amount for the invoice.
     *
     * @return float|null Base discount tax compensation amount.
     */
    public function getBaseDiscountTaxCompensationAmount();

    /**
     * Gets the base shipping amount for the invoice.
     *
     * @return float|null Base shipping amount.
     */
    public function getBaseShippingAmount();

    /**
     * Gets the base shipping discount tax compensation amount for the invoice.
     *
     * @return float|null Base shipping discount tax compensation amount.
     */
    public function getBaseShippingDiscountTaxCompensationAmnt();

    /**
     * Gets the base shipping including tax for the invoice.
     *
     * @return float|null Base shipping including tax.
     */
    public function getBaseShippingInclTax();

    /**
     * Gets the base shipping tax amount for the invoice.
     *
     * @return float|null Base shipping tax amount.
     */
    public function getBaseShippingTaxAmount();

    /**
     * Gets the base subtotal for the invoice.
     *
     * @return float|null Base subtotal.
     */
    public function getBaseSubtotal();

    /**
     * Gets the base subtotal including tax for the invoice.
     *
     * @return float|null Base subtotal including tax.
     */
    public function getBaseSubtotalInclTax();

    /**
     * Gets the base tax amount for the invoice.
     *
     * @return float|null Base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the base total refunded for the invoice.
     *
     * @return float|null Base total refunded.
     */
    public function getBaseTotalRefunded();

    /**
     * Gets the base-to-global rate for the invoice.
     *
     * @return float|null Base-to-global rate.
     */
    public function getBaseToGlobalRate();

    /**
     * Gets the base-to-order rate for the invoice.
     *
     * @return float|null Base-to-order rate.
     */
    public function getBaseToOrderRate();

    /**
     * Gets the billing address ID for the invoice.
     *
     * @return int|null Billing address ID.
     */
    public function getBillingAddressId();

    /**
     * Gets the can void flag value for the invoice.
     *
     * @return int|null Can void flag value.
     */
    public function getCanVoidFlag();

    /**
     * Gets the created-at timestamp for the invoice.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the invoice.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the discount amount for the invoice.
     *
     * @return float|null Discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the discount description for the invoice.
     *
     * @return string|null Discount description.
     */
    public function getDiscountDescription();

    /**
     * Gets the email-sent flag value for the invoice.
     *
     * @return int|null Email-sent flag value.
     */
    public function getEmailSent();

    /**
     * Gets the ID for the invoice.
     *
     * @return int|null Invoice ID.
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Gets the global currency code for the invoice.
     *
     * @return string|null Global currency code.
     */
    public function getGlobalCurrencyCode();

    /**
     * Gets the grand total for the invoice.
     *
     * @return float|null Grand total.
     */
    public function getGrandTotal();

    /**
     * Gets the discount tax compensation amount for the invoice.
     *
     * @return float|null Discount tax compensation amount.
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Gets the increment ID for the invoice.
     *
     * @return string|null Increment ID.
     */
    public function getIncrementId();

    /**
     * Gets the is-used-for-refund flag value for the invoice.
     *
     * @return int|null Is-used-for-refund flag value.
     */
    public function getIsUsedForRefund();

    /**
     * Gets the order currency code for the invoice.
     *
     * @return string|null Order currency code.
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
     * @return int|null Shipping address ID.
     */
    public function getShippingAddressId();

    /**
     * Gets the shipping amount for the invoice.
     *
     * @return float|null Shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the shipping discount tax compensation amount for the invoice.
     *
     * @return float|null Shipping discount tax compensation amount.
     */
    public function getShippingDiscountTaxCompensationAmount();

    /**
     * Gets the shipping including tax for the invoice.
     *
     * @return float|null Shipping including tax.
     */
    public function getShippingInclTax();

    /**
     * Gets the shipping tax amount for the invoice.
     *
     * @return float|null Shipping tax amount.
     */
    public function getShippingTaxAmount();

    /**
     * Gets the state for the invoice.
     *
     * @return int|null State.
     */
    public function getState();

    /**
     * Gets the store currency code for the invoice.
     *
     * @return string|null Store currency code.
     */
    public function getStoreCurrencyCode();

    /**
     * Gets the store ID for the invoice.
     *
     * @return int|null Store ID.
     */
    public function getStoreId();

    /**
     * Gets the store-to-base rate for the invoice.
     *
     * @return float|null Store-to-base rate.
     */
    public function getStoreToBaseRate();

    /**
     * Gets the store-to-order rate for the invoice.
     *
     * @return float|null Store-to-order rate.
     */
    public function getStoreToOrderRate();

    /**
     * Gets the subtotal for the invoice.
     *
     * @return float|null Subtotal.
     */
    public function getSubtotal();

    /**
     * Gets the subtotal including tax for the invoice.
     *
     * @return float|null Subtotal including tax.
     */
    public function getSubtotalInclTax();

    /**
     * Gets the tax amount for the invoice.
     *
     * @return float|null Tax amount.
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
     * @return string|null Transaction ID.
     */
    public function getTransactionId();

    /**
     * Sets the transaction ID for the invoice.
     *
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Gets the updated-at timestamp for the invoice.
     *
     * @return string|null Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the items in the invoice.
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface[] Array of invoice items.
     */
    public function getItems();

    /**
     * Sets the items in the invoice.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Gets the comments, if any, for the invoice.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface[]|null Array of any invoice comments. Otherwise, null.
     */
    public function getComments();

    /**
     * Sets the comments, if any, for the invoice.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterface[] $comments
     * @return $this
     */
    public function setComments($comments);

    /**
     * Sets the updated-at timestamp for the invoice.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setUpdatedAt($timestamp);

    /**
     * Sets the store ID for the invoice.
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id);

    /**
     * Sets the base grand total for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseGrandTotal($amount);

    /**
     * Sets the shipping tax amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingTaxAmount($amount);

    /**
     * Sets the tax amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setTaxAmount($amount);

    /**
     * Sets the base tax amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseTaxAmount($amount);

    /**
     * Sets the store-to-order rate for the invoice.
     *
     * @param float $rate
     * @return $this
     */
    public function setStoreToOrderRate($rate);

    /**
     * Sets the base shipping tax amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingTaxAmount($amount);

    /**
     * Sets the base discount amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseDiscountAmount($amount);

    /**
     * Sets the base-to-order rate for the invoice.
     *
     * @param float $rate
     * @return $this
     */
    public function setBaseToOrderRate($rate);

    /**
     * Sets the grand total for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setGrandTotal($amount);

    /**
     * Sets the shipping amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount);

    /**
     * Sets the subtotal including tax for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setSubtotalInclTax($amount);

    /**
     * Sets the base subtotal including tax for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseSubtotalInclTax($amount);

    /**
     * Sets the store-to-base rate for the invoice.
     *
     * @param float $rate
     * @return $this
     */
    public function setStoreToBaseRate($rate);

    /**
     * Sets the base shipping amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingAmount($amount);

    /**
     * Sets the total quantity for the invoice.
     *
     * @param float $qty
     * @return $this
     */
    public function setTotalQty($qty);

    /**
     * Sets the base-to-global rate for the invoice.
     *
     * @param float $rate
     * @return $this
     */
    public function setBaseToGlobalRate($rate);

    /**
     * Sets the subtotal for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setSubtotal($amount);

    /**
     * Sets the base subtotal for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseSubtotal($amount);

    /**
     * Sets the discount amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setDiscountAmount($amount);

    /**
     * Sets the billing address ID for the invoice.
     *
     * @param int $id
     * @return $this
     */
    public function setBillingAddressId($id);

    /**
     * Sets the is-used-for-refund flag value for the invoice.
     *
     * @param int $isUsedForRefund
     * @return $this
     */
    public function setIsUsedForRefund($isUsedForRefund);

    /**
     * Sets the order ID for the invoice.
     *
     * @param int $id
     * @return $this
     */
    public function setOrderId($id);

    /**
     * Sets the email-sent flag value for the invoice.
     *
     * @param int $emailSent
     * @return $this
     */
    public function setEmailSent($emailSent);

    /**
     * Sets the can void flag value for the invoice.
     *
     * @param int $canVoidFlag
     * @return $this
     */
    public function setCanVoidFlag($canVoidFlag);

    /**
     * Sets the state for the invoice.
     *
     * @param int $state
     * @return $this
     */
    public function setState($state);

    /**
     * Sets the shipping address ID for the invoice.
     *
     * @param int $id
     * @return $this
     */
    public function setShippingAddressId($id);

    /**
     * Sets the store currency code for the invoice.
     *
     * @param string $code
     * @return $this
     */
    public function setStoreCurrencyCode($code);

    /**
     * Sets the order currency code for the invoice.
     *
     * @param string $code
     * @return $this
     */
    public function setOrderCurrencyCode($code);

    /**
     * Sets the base currency code for the invoice.
     *
     * @param string $code
     * @return $this
     */
    public function setBaseCurrencyCode($code);

    /**
     * Sets the global currency code for the invoice.
     *
     * @param string $code
     * @return $this
     */
    public function setGlobalCurrencyCode($code);

    /**
     * Sets the increment ID for the invoice.
     *
     * @param string $id
     * @return $this
     */
    public function setIncrementId($id);

    /**
     * Sets the discount tax compensation amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setDiscountTaxCompensationAmount($amount);

    /**
     * Sets the base discount tax compensation amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseDiscountTaxCompensationAmount($amount);

    /**
     * Sets the shipping discount tax compensation amount for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingDiscountTaxCompensationAmount($amount);

    /**
     * Sets the base shipping discount tax compensation amount for the invoice.
     *
     * @param float $amnt
     * @return $this
     */
    public function setBaseShippingDiscountTaxCompensationAmnt($amnt);

    /**
     * Sets the shipping including tax for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingInclTax($amount);

    /**
     * Sets the base shipping including tax for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingInclTax($amount);

    /**
     * Sets the base total refunded for the invoice.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseTotalRefunded($amount);

    /**
     * Sets the discount description for the invoice.
     *
     * @param string $description
     * @return $this
     */
    public function setDiscountDescription($description);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\InvoiceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\InvoiceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\InvoiceExtensionInterface $extensionAttributes);
}
