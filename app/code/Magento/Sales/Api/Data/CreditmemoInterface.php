<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Api\Data;

/**
 * Credit memo interface.
 *
 * After a customer places and pays for an order and an invoice has been issued, the merchant can create a credit memo
 * to refund all or part of the amount paid for any returned or undelivered items. The memo restores funds to the
 * customer account so that the customer can make future purchases.
 * @api
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
     * @return float|null Credit memo adjustment.
     */
    public function getAdjustment();

    /**
     * Gets the credit memo negative adjustment.
     *
     * @return float|null Credit memo negative adjustment.
     */
    public function getAdjustmentNegative();

    /**
     * Gets the credit memo positive adjustment.
     *
     * @return float|null Credit memo positive adjustment.
     */
    public function getAdjustmentPositive();

    /**
     * Gets the credit memo base adjustment.
     *
     * @return float|null Credit memo base adjustment.
     */
    public function getBaseAdjustment();

    /**
     * Gets the credit memo negative base adjustment.
     *
     * @return float|null Credit memo negative base adjustment.
     */
    public function getBaseAdjustmentNegative();

    /**
     * Sets the credit memo negative base adjustment.
     *
     * @param float $baseAdjustmentNegative
     * @return $this
     */
    public function setBaseAdjustmentNegative($baseAdjustmentNegative);

    /**
     * Gets the credit memo positive base adjustment.
     *
     * @return float|null Credit memo positive base adjustment.
     */
    public function getBaseAdjustmentPositive();

    /**
     * Sets the credit memo positive base adjustment.
     *
     * @param float $baseAdjustmentPositive
     * @return $this
     */
    public function setBaseAdjustmentPositive($baseAdjustmentPositive);

    /**
     * Gets the credit memo base currency code.
     *
     * @return string|null Credit memo base currency code.
     */
    public function getBaseCurrencyCode();

    /**
     * Gets the credit memo base discount amount.
     *
     * @return float|null Credit memo base discount amount.
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the credit memo base grand total.
     *
     * @return float|null Credit memo base grand total.
     */
    public function getBaseGrandTotal();

    /**
     * Gets the credit memo base discount tax compensation amount.
     *
     * @return float|null Credit memo base discount tax compensation amount.
     */
    public function getBaseDiscountTaxCompensationAmount();

    /**
     * Gets the credit memo base shipping amount.
     *
     * @return float|null Credit memo base shipping amount.
     */
    public function getBaseShippingAmount();

    /**
     * Gets the credit memo base shipping discount tax compensation amount.
     *
     * @return float|null Credit memo base shipping discount tax compensation amount.
     */
    public function getBaseShippingDiscountTaxCompensationAmnt();

    /**
     * Gets the credit memo base shipping including tax.
     *
     * @return float|null Credit memo base shipping including tax.
     */
    public function getBaseShippingInclTax();

    /**
     * Gets the credit memo base shipping tax amount.
     *
     * @return float|null Credit memo base shipping tax amount.
     */
    public function getBaseShippingTaxAmount();

    /**
     * Gets the credit memo base subtotal.
     *
     * @return float|null Credit memo base subtotal.
     */
    public function getBaseSubtotal();

    /**
     * Gets the credit memo base subtotal including tax.
     *
     * @return float|null Credit memo base subtotal including tax.
     */
    public function getBaseSubtotalInclTax();

    /**
     * Gets the credit memo base tax amount.
     *
     * @return float|null Credit memo base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the credit memo base-to-global rate.
     *
     * @return float|null Credit memo base-to-global rate.
     */
    public function getBaseToGlobalRate();

    /**
     * Gets the credit memo base-to-order rate.
     *
     * @return float|null Credit memo base-to-order rate.
     */
    public function getBaseToOrderRate();

    /**
     * Gets the credit memo billing address ID.
     *
     * @return int|null Credit memo billing address ID.
     */
    public function getBillingAddressId();

    /**
     * Gets the credit memo created-at timestamp.
     *
     * @return string|null Credit memo created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the credit memo created-at timestamp.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the credit memo status.
     *
     * @return int|null Credit memo status.
     */
    public function getCreditmemoStatus();

    /**
     * Gets the credit memo discount amount.
     *
     * @return float|null Credit memo discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the credit memo discount description.
     *
     * @return string|null Credit memo discount description.
     */
    public function getDiscountDescription();

    /**
     * Gets the credit memo email sent flag value.
     *
     * @return int|null Credit memo email sent flag value.
     */
    public function getEmailSent();

    /**
     * Gets the credit memo ID.
     *
     * @return int|null Credit memo ID.
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
     * Gets the credit memo global currency code.
     *
     * @return string|null Credit memo global currency code.
     */
    public function getGlobalCurrencyCode();

    /**
     * Gets the credit memo grand total.
     *
     * @return float|null Credit memo grand total.
     */
    public function getGrandTotal();

    /**
     * Gets the credit memo discount tax compensation amount.
     *
     * @return float|null Credit memo discount tax compensation amount.
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Gets the credit memo increment ID.
     *
     * @return string|null Credit memo increment ID.
     */
    public function getIncrementId();

    /**
     * Gets the credit memo invoice ID.
     *
     * @return int|null Credit memo invoice ID.
     */
    public function getInvoiceId();

    /**
     * Gets the credit memo order currency code.
     *
     * @return string|null Credit memo order currency code.
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
     * @return int|null Credit memo shipping address ID.
     */
    public function getShippingAddressId();
    /**
     * Gets the credit memo shipping amount.
     *
     * @return float|null Credit memo shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the credit memo shipping discount tax compensation amount.
     *
     * @return float|null Credit memo shipping discount tax compensation amount.
     */
    public function getShippingDiscountTaxCompensationAmount();

    /**
     * Gets the credit memo shipping including tax.
     *
     * @return float|null Credit memo shipping including tax.
     */
    public function getShippingInclTax();

    /**
     * Gets the credit memo shipping tax amount.
     *
     * @return float|null Credit memo shipping tax amount.
     */
    public function getShippingTaxAmount();

    /**
     * Gets the credit memo state.
     *
     * @return int|null Credit memo state.
     */
    public function getState();

    /**
     * Gets the credit memo store currency code.
     *
     * @return string|null Credit memo store currency code.
     */
    public function getStoreCurrencyCode();

    /**
     * Gets the credit memo store ID.
     *
     * @return int|null Credit memo store ID.
     */
    public function getStoreId();

    /**
     * Gets the credit memo store-to-base rate.
     *
     * @return float|null Credit memo store-to-base rate.
     */
    public function getStoreToBaseRate();

    /**
     * Gets the credit memo store-to-order rate.
     *
     * @return float|null Credit memo store-to-order rate.
     */
    public function getStoreToOrderRate();

    /**
     * Gets the credit memo subtotal.
     *
     * @return float|null Credit memo subtotal.
     */
    public function getSubtotal();

    /**
     * Gets the credit memo subtotal including tax.
     *
     * @return float|null Credit memo subtotal including tax.
     */
    public function getSubtotalInclTax();

    /**
     * Gets the credit memo tax amount.
     *
     * @return float|null Credit memo tax amount.
     */
    public function getTaxAmount();

    /**
     * Gets the credit memo transaction ID.
     *
     * @return string|null Credit memo transaction ID.
     */
    public function getTransactionId();

    /**
     * Sets the credit memo transaction ID.
     *
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Gets the credit memo updated-at timestamp.
     *
     * @return string|null Credit memo updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets credit memo items.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface[] Array of credit memo items.
     */
    public function getItems();

    /**
     * Sets credit memo items.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Gets credit memo comments.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCommentInterface[]|null Array of any credit memo comments. Otherwise, null.
     */
    public function getComments();

    /**
     * Sets credit memo comments.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoCommentInterface[] $comments
     * @return $this
     */
    public function setComments($comments);

    /**
     * Sets the credit memo store ID.
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id);

    /**
     * Sets the credit memo positive adjustment.
     *
     * @param float $adjustmentPositive
     * @return $this
     */
    public function setAdjustmentPositive($adjustmentPositive);

    /**
     * Sets the credit memo base shipping tax amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingTaxAmount($amount);

    /**
     * Sets the credit memo store-to-order rate.
     *
     * @param float $rate
     * @return $this
     */
    public function setStoreToOrderRate($rate);

    /**
     * Sets the credit memo base discount amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseDiscountAmount($amount);

    /**
     * Sets the credit memo base-to-order rate.
     *
     * @param float $rate
     * @return $this
     */
    public function setBaseToOrderRate($rate);

    /**
     * Sets the credit memo grand total.
     *
     * @param float $amount
     * @return $this
     */
    public function setGrandTotal($amount);

    /**
     * Sets the credit memo base subtotal including tax.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseSubtotalInclTax($amount);

    /**
     * Sets the credit memo shipping amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount);

    /**
     * Sets the credit memo subtotal including tax.
     *
     * @param float $amount
     * @return $this
     */
    public function setSubtotalInclTax($amount);

    /**
     * Sets the credit memo negative adjustment.
     *
     * @param float $adjustmentNegative
     * @return $this
     */
    public function setAdjustmentNegative($adjustmentNegative);

    /**
     * Sets the credit memo base shipping amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingAmount($amount);

    /**
     * Sets the credit memo store-to-base rate.
     *
     * @param float $rate
     * @return $this
     */
    public function setStoreToBaseRate($rate);

    /**
     * Sets the credit memo base-to-global rate.
     *
     * @param float $rate
     * @return $this
     */
    public function setBaseToGlobalRate($rate);

    /**
     * Sets the credit memo base adjustment.
     *
     * @param float $baseAdjustment
     * @return $this
     */
    public function setBaseAdjustment($baseAdjustment);

    /**
     * Sets the credit memo base subtotal.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseSubtotal($amount);

    /**
     * Sets the credit memo discount amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setDiscountAmount($amount);

    /**
     * Sets the credit memo subtotal.
     *
     * @param float $amount
     * @return $this
     */
    public function setSubtotal($amount);

    /**
     * Sets the credit memo adjustment.
     *
     * @param float $adjustment
     * @return $this
     */
    public function setAdjustment($adjustment);

    /**
     * Sets the credit memo base grand total.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseGrandTotal($amount);

    /**
     * Sets the credit memo base tax amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseTaxAmount($amount);

    /**
     * Sets the credit memo shipping tax amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingTaxAmount($amount);

    /**
     * Sets the credit memo tax amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setTaxAmount($amount);

    /**
     * Sets the credit memo order ID.
     *
     * @param int $id
     * @return $this
     */
    public function setOrderId($id);

    /**
     * Sets the credit memo email sent flag value.
     *
     * @param int $emailSent
     * @return $this
     */
    public function setEmailSent($emailSent);

    /**
     * Sets the credit memo status.
     *
     * @param int $creditmemoStatus
     * @return $this
     */
    public function setCreditmemoStatus($creditmemoStatus);

    /**
     * Sets the credit memo state.
     *
     * @param int $state
     * @return $this
     */
    public function setState($state);

    /**
     * Sets the credit memo shipping address ID.
     *
     * @param int $id
     * @return $this
     */
    public function setShippingAddressId($id);

    /**
     * Sets the credit memo billing address ID.
     *
     * @param int $id
     * @return $this
     */
    public function setBillingAddressId($id);

    /**
     * Sets the credit memo invoice ID.
     *
     * @param int $id
     * @return $this
     */
    public function setInvoiceId($id);

    /**
     * Sets the credit memo store currency code.
     *
     * @param string $code
     * @return $this
     */
    public function setStoreCurrencyCode($code);

    /**
     * Sets the credit memo order currency code.
     *
     * @param string $code
     * @return $this
     */
    public function setOrderCurrencyCode($code);

    /**
     * Sets the credit memo base currency code.
     *
     * @param string $code
     * @return $this
     */
    public function setBaseCurrencyCode($code);

    /**
     * Sets the credit memo global currency code.
     *
     * @param string $code
     * @return $this
     */
    public function setGlobalCurrencyCode($code);

    /**
     * Sets the credit memo increment ID.
     *
     * @param string $id
     * @return $this
     */
    public function setIncrementId($id);

    /**
     * Sets the credit memo updated-at timestamp.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setUpdatedAt($timestamp);

    /**
     * Sets the credit memo discount tax compensation amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setDiscountTaxCompensationAmount($amount);

    /**
     * Sets the credit memo base discount tax compensation amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseDiscountTaxCompensationAmount($amount);

    /**
     * Sets the credit memo shipping discount tax compensation amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingDiscountTaxCompensationAmount($amount);

    /**
     * Sets the credit memo base shipping discount tax compensation amount.
     *
     * @param float $amnt
     * @return $this
     */
    public function setBaseShippingDiscountTaxCompensationAmnt($amnt);

    /**
     * Sets the credit memo shipping including tax.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingInclTax($amount);

    /**
     * Sets the credit memo base shipping including tax.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingInclTax($amount);

    /**
     * Sets the credit memo discount description.
     *
     * @param string $description
     * @return $this
     */
    public function setDiscountDescription($description);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\CreditmemoExtensionInterface $extensionAttributes);
}
