<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 100.0.2
 */
interface OrderInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * State.
     */
    const STATE = 'state';
    /*
     * Status.
     */
    const STATUS = 'status';
    /*
     * Coupon code.
     */
    const COUPON_CODE = 'coupon_code';
    /*
     * Protect code.
     */
    const PROTECT_CODE = 'protect_code';
    /*
     * Shipping description.
     */
    const SHIPPING_DESCRIPTION = 'shipping_description';
    /*
     * Is-virtual flag.
     */
    const IS_VIRTUAL = 'is_virtual';
    /*
     * Store ID.
     */
    const STORE_ID = 'store_id';
    /*
     * Customer ID.
     */
    const CUSTOMER_ID = 'customer_id';
    /*
     * Base discount amount.
     */
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    /*
     * Base discount canceled.
     */
    const BASE_DISCOUNT_CANCELED = 'base_discount_canceled';
    /*
     * Base discount invoiced.
     */
    const BASE_DISCOUNT_INVOICED = 'base_discount_invoiced';
    /*
     * Base discount refunded.
     */
    const BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    /*
     * Base grand total.
     */
    const BASE_GRAND_TOTAL = 'base_grand_total';
    /*
     * Base shipping amount.
     */
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    /*
     * Base shipping canceled.
     */
    const BASE_SHIPPING_CANCELED = 'base_shipping_canceled';
    /*
     * Base shipping invoiced.
     */
    const BASE_SHIPPING_INVOICED = 'base_shipping_invoiced';
    /*
     * Base shipping refunded.
     */
    const BASE_SHIPPING_REFUNDED = 'base_shipping_refunded';
    /*
     * Base shipping tax amount.
     */
    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
    /*
     * Base shipping tax refunded.
     */
    const BASE_SHIPPING_TAX_REFUNDED = 'base_shipping_tax_refunded';
    /*
     * Base subtotal.
     */
    const BASE_SUBTOTAL = 'base_subtotal';
    /*
     * Base subtotal canceled.
     */
    const BASE_SUBTOTAL_CANCELED = 'base_subtotal_canceled';
    /*
     * Base subtotal invoiced.
     */
    const BASE_SUBTOTAL_INVOICED = 'base_subtotal_invoiced';
    /*
     * Base subtotal refunded.
     */
    const BASE_SUBTOTAL_REFUNDED = 'base_subtotal_refunded';
    /*
     * Base tax amount.
     */
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    /*
     * Base tax canceled.
     */
    const BASE_TAX_CANCELED = 'base_tax_canceled';
    /*
     * Base tax invoiced.
     */
    const BASE_TAX_INVOICED = 'base_tax_invoiced';
    /*
     * Base tax refunded.
     */
    const BASE_TAX_REFUNDED = 'base_tax_refunded';
    /*
     * Base-to-global rate.
     */
    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    /*
     * Base-to-order rate.
     */
    const BASE_TO_ORDER_RATE = 'base_to_order_rate';
    /*
     * Base total canceled.
     */
    const BASE_TOTAL_CANCELED = 'base_total_canceled';
    /*
     * Base total invoiced.
     */
    const BASE_TOTAL_INVOICED = 'base_total_invoiced';
    /*
     * Base total invoiced cost.
     */
    const BASE_TOTAL_INVOICED_COST = 'base_total_invoiced_cost';
    /*
     * Base total offline refunded.
     */
    const BASE_TOTAL_OFFLINE_REFUNDED = 'base_total_offline_refunded';
    /*
     * Base total online refunded.
     */
    const BASE_TOTAL_ONLINE_REFUNDED = 'base_total_online_refunded';
    /*
     * Base total paid.
     */
    const BASE_TOTAL_PAID = 'base_total_paid';
    /*
     * Base total quantity ordered.
     */
    const BASE_TOTAL_QTY_ORDERED = 'base_total_qty_ordered';
    /*
     * Base total refunded.
     */
    const BASE_TOTAL_REFUNDED = 'base_total_refunded';
    /*
     * Discount amount.
     */
    const DISCOUNT_AMOUNT = 'discount_amount';
    /*
     * Discount canceled.
     */
    const DISCOUNT_CANCELED = 'discount_canceled';
    /*
     * Discount invoiced.
     */
    const DISCOUNT_INVOICED = 'discount_invoiced';
    /*
     * Discount refunded.
     */
    const DISCOUNT_REFUNDED = 'discount_refunded';
    /*
     * Grand total.
     */
    const GRAND_TOTAL = 'grand_total';
    /*
     * Shipping amount.
     */
    const SHIPPING_AMOUNT = 'shipping_amount';
    /*
     * Shipping canceled.
     */
    const SHIPPING_CANCELED = 'shipping_canceled';
    /*
     * Shipping invoiced.
     */
    const SHIPPING_INVOICED = 'shipping_invoiced';
    /*
     * Shipping refunded.
     */
    const SHIPPING_REFUNDED = 'shipping_refunded';
    /*
     * Shipping tax amount.
     */
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    /*
     * Shipping tax refunded.
     */
    const SHIPPING_TAX_REFUNDED = 'shipping_tax_refunded';
    /*
     * Store-to-base rate.
     */
    const STORE_TO_BASE_RATE = 'store_to_base_rate';
    /*
     * Store-to-order rate.
     */
    const STORE_TO_ORDER_RATE = 'store_to_order_rate';
    /*
     * Subtotal.
     */
    const SUBTOTAL = 'subtotal';
    /*
     * Subtotal canceled.
     */
    const SUBTOTAL_CANCELED = 'subtotal_canceled';
    /*
     * Subtotal invoiced.
     */
    const SUBTOTAL_INVOICED = 'subtotal_invoiced';
    /*
     * Subtotal refunded.
     */
    const SUBTOTAL_REFUNDED = 'subtotal_refunded';
    /*
     * Tax amount.
     */
    const TAX_AMOUNT = 'tax_amount';
    /*
     * Tax canceled.
     */
    const TAX_CANCELED = 'tax_canceled';
    /*
     * Tax invoiced.
     */
    const TAX_INVOICED = 'tax_invoiced';
    /*
     * Tax refunded.
     */
    const TAX_REFUNDED = 'tax_refunded';
    /*
     * Total canceled.
     */
    const TOTAL_CANCELED = 'total_canceled';
    /*
     * Total invoiced.
     */
    const TOTAL_INVOICED = 'total_invoiced';
    /*
     * Total refunded offline.
     */
    const TOTAL_OFFLINE_REFUNDED = 'total_offline_refunded';
    /*
     * Total refunded online.
     */
    const TOTAL_ONLINE_REFUNDED = 'total_online_refunded';
    /*
     * Total paid.
     */
    const TOTAL_PAID = 'total_paid';
    /*
     * Total quantity ordered.
     */
    const TOTAL_QTY_ORDERED = 'total_qty_ordered';
    /*
     * Total refunded.
     */
    const TOTAL_REFUNDED = 'total_refunded';
    /*
     * Can-ship-partially flag.
     */
    const CAN_SHIP_PARTIALLY = 'can_ship_partially';
    /*
     * Can-ship-item-partially flag.
     */
    const CAN_SHIP_PARTIALLY_ITEM = 'can_ship_partially_item';
    /*
     * Customer-is-guest flag.
     */
    const CUSTOMER_IS_GUEST = 'customer_is_guest';
    /*
     * Customer-note-notify flag.
     */
    const CUSTOMER_NOTE_NOTIFY = 'customer_note_notify';
    /*
     * Billing address ID.
     */
    const BILLING_ADDRESS_ID = 'billing_address_id';
    /*
     * Customer group ID.
     */
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    /*
     * Edit increment value.
     */
    const EDIT_INCREMENT = 'edit_increment';
    /*
     * Email-sent flag.
     */
    const EMAIL_SENT = 'email_sent';
    /*
     * Forced-shipment-with-invoice flag.
     */
    const FORCED_SHIPMENT_WITH_INVOICE = 'forced_shipment_with_invoice';
    /*
     * Payment authorization expiration date.
     */
    const PAYMENT_AUTH_EXPIRATION = 'payment_auth_expiration';
    /*
     * Quote address ID.
     */
    const QUOTE_ADDRESS_ID = 'quote_address_id';
    /*
     * Quote ID.
     */
    const QUOTE_ID = 'quote_id';
    /*
     * Negative adjustment.
     */
    const ADJUSTMENT_NEGATIVE = 'adjustment_negative';
    /*
     * Positive adjustment.
     */
    const ADJUSTMENT_POSITIVE = 'adjustment_positive';
    /*
     * Base negative adjustment.
     */
    const BASE_ADJUSTMENT_NEGATIVE = 'base_adjustment_negative';
    /*
     * Base positive adjustment.
     */
    const BASE_ADJUSTMENT_POSITIVE = 'base_adjustment_positive';
    /*
     * Base shipping discount amount.
     */
    const BASE_SHIPPING_DISCOUNT_AMOUNT = 'base_shipping_discount_amount';
    /*
     * Base subtotal including tax.
     */
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    /*
     * Base total due.
     */
    const BASE_TOTAL_DUE = 'base_total_due';
    /*
     * Payment authorization amount.
     */
    const PAYMENT_AUTHORIZATION_AMOUNT = 'payment_authorization_amount';
    /*
     * Shipping discount amount.
     */
    const SHIPPING_DISCOUNT_AMOUNT = 'shipping_discount_amount';
    /*
     * Subtotal including tax.
     */
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    /*
     * Total due.
     */
    const TOTAL_DUE = 'total_due';
    /*
     * Weight.
     */
    const WEIGHT = 'weight';
    /*
     * Customer date-of-birth (DOB).
     */
    const CUSTOMER_DOB = 'customer_dob';
    /*
     * Increment ID.
     */
    const INCREMENT_ID = 'increment_id';
    /*
     * Applied rule IDs.
     */
    const APPLIED_RULE_IDS = 'applied_rule_ids';
    /*
     * Base currency code.
     */
    const BASE_CURRENCY_CODE = 'base_currency_code';
    /*
     * Customer email address.
     */
    const CUSTOMER_EMAIL = 'customer_email';
    /*
     * Customer first name.
     */
    const CUSTOMER_FIRSTNAME = 'customer_firstname';
    /*
     * Customer last name.
     */
    const CUSTOMER_LASTNAME = 'customer_lastname';
    /*
     * Customer middle name.
     */
    const CUSTOMER_MIDDLENAME = 'customer_middlename';
    /*
     * Customer prefix.
     */
    const CUSTOMER_PREFIX = 'customer_prefix';
    /*
     * Customer suffix.
     */
    const CUSTOMER_SUFFIX = 'customer_suffix';
    /*
     * Customer value-added tax (VAT).
     */
    const CUSTOMER_TAXVAT = 'customer_taxvat';
    /*
     * Discount description.
     */
    const DISCOUNT_DESCRIPTION = 'discount_description';
    /*
     * External customer ID.
     */
    const EXT_CUSTOMER_ID = 'ext_customer_id';
    /*
     * External order ID.
     */
    const EXT_ORDER_ID = 'ext_order_id';
    /*
     * Global currency code.
     */
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
    /*
     * Hold before state.
     */
    const HOLD_BEFORE_STATE = 'hold_before_state';
    /*
     * Hold before status.
     */
    const HOLD_BEFORE_STATUS = 'hold_before_status';
    /*
     * Order currency code.
     */
    const ORDER_CURRENCY_CODE = 'order_currency_code';
    /*
     * Original increment ID.
     */
    const ORIGINAL_INCREMENT_ID = 'original_increment_id';
    /*
     * Relation child ID.
     */
    const RELATION_CHILD_ID = 'relation_child_id';
    /*
     * Relation child real ID.
     */
    const RELATION_CHILD_REAL_ID = 'relation_child_real_id';
    /*
     * Relation parent ID.
     */
    const RELATION_PARENT_ID = 'relation_parent_id';
    /*
     * Relation parent real ID.
     */
    const RELATION_PARENT_REAL_ID = 'relation_parent_real_id';
    /*
     * Remote IP address.
     */
    const REMOTE_IP = 'remote_ip';
    /*
     * Store currency code.
     */
    const STORE_CURRENCY_CODE = 'store_currency_code';
    /*
     * Store name.
     */
    const STORE_NAME = 'store_name';
    /*
     * X-Forwarded-For HTTP header field.
     */
    const X_FORWARDED_FOR = 'x_forwarded_for';
    /*
     * Customer note.
     */
    const CUSTOMER_NOTE = 'customer_note';
    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Updated-at timestamp.
     */
    const UPDATED_AT = 'updated_at';
    /*
     * Total item count.
     */
    const TOTAL_ITEM_COUNT = 'total_item_count';
    /*
     * Customer gender.
     */
    const CUSTOMER_GENDER = 'customer_gender';
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
     * Shipping including tax.
     */
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    /*
     * Base shipping including tax.
     */
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    /*
     * Items.
     */
    const ITEMS = 'items';
    /*
     * Billing address.
     */
    const BILLING_ADDRESS = 'billing_address';
    /*
     * Payment.
     */
    const PAYMENT = 'payment';
    /*
     * Status histories.
     */
    const STATUS_HISTORIES = 'status_histories';

    /**
     * Gets the negative adjustment value for the order.
     *
     * @return float|null Negative adjustment value.
     */
    public function getAdjustmentNegative();

    /**
     * Gets the positive adjustment value for the order.
     *
     * @return float|null Positive adjustment value.
     */
    public function getAdjustmentPositive();

    /**
     * Gets the applied rule IDs for the order.
     *
     * @return string|null Applied rule IDs.
     */
    public function getAppliedRuleIds();

    /**
     * Gets the base negative adjustment value for the order.
     *
     * @return float|null Base negative adjustment value.
     */
    public function getBaseAdjustmentNegative();

    /**
     * Gets the base positive adjustment value for the order.
     *
     * @return float|null Base positive adjustment value.
     */
    public function getBaseAdjustmentPositive();

    /**
     * Gets the base currency code for the order.
     *
     * @return string|null Base currency code.
     */
    public function getBaseCurrencyCode();

    /**
     * Gets the base discount amount for the order.
     *
     * @return float|null Base discount amount.
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base discount canceled for the order.
     *
     * @return float|null Base discount canceled.
     */
    public function getBaseDiscountCanceled();

    /**
     * Gets the base discount invoiced amount for the order.
     *
     * @return float|null Base discount invoiced.
     */
    public function getBaseDiscountInvoiced();

    /**
     * Gets the base discount refunded amount for the order.
     *
     * @return float|null Base discount refunded.
     */
    public function getBaseDiscountRefunded();

    /**
     * Gets the base grand total for the order.
     *
     * @return float Base grand total.
     */
    public function getBaseGrandTotal();

    /**
     * Gets the base discount tax compensation amount for the order.
     *
     * @return float|null Base discount tax compensation amount.
     */
    public function getBaseDiscountTaxCompensationAmount();

    /**
     * Gets the base discount tax compensation invoiced amount for the order.
     *
     * @return float|null Base discount tax compensation invoiced.
     */
    public function getBaseDiscountTaxCompensationInvoiced();

    /**
     * Gets the base discount tax compensation refunded amount for the order.
     *
     * @return float|null Base discount tax compensation refunded.
     */
    public function getBaseDiscountTaxCompensationRefunded();

    /**
     * Gets the base shipping amount for the order.
     *
     * @return float|null Base shipping amount.
     */
    public function getBaseShippingAmount();

    /**
     * Gets the base shipping canceled for the order.
     *
     * @return float|null Base shipping canceled.
     */
    public function getBaseShippingCanceled();

    /**
     * Gets the base shipping discount amount for the order.
     *
     * @return float|null Base shipping discount amount.
     */
    public function getBaseShippingDiscountAmount();

    /**
     * Gets the base shipping discount tax compensation amount for the order.
     *
     * @return float|null Base shipping discount tax compensation amount.
     */
    public function getBaseShippingDiscountTaxCompensationAmnt();

    /**
     * Gets the base shipping including tax for the order.
     *
     * @return float|null Base shipping including tax.
     */
    public function getBaseShippingInclTax();

    /**
     * Gets the base shipping invoiced amount for the order.
     *
     * @return float|null Base shipping invoiced.
     */
    public function getBaseShippingInvoiced();

    /**
     * Gets the base shipping refunded amount for the order.
     *
     * @return float|null Base shipping refunded.
     */
    public function getBaseShippingRefunded();

    /**
     * Gets the base shipping tax amount for the order.
     *
     * @return float|null Base shipping tax amount.
     */
    public function getBaseShippingTaxAmount();

    /**
     * Gets the base shipping tax refunded amount for the order.
     *
     * @return float|null Base shipping tax refunded.
     */
    public function getBaseShippingTaxRefunded();

    /**
     * Gets the base subtotal for the order.
     *
     * @return float|null Base subtotal.
     */
    public function getBaseSubtotal();

    /**
     * Gets the base subtotal canceled for the order.
     *
     * @return float|null Base subtotal canceled.
     */
    public function getBaseSubtotalCanceled();

    /**
     * Gets the base subtotal including tax for the order.
     *
     * @return float|null Base subtotal including tax.
     */
    public function getBaseSubtotalInclTax();

    /**
     * Gets the base subtotal invoiced amount for the order.
     *
     * @return float|null Base subtotal invoiced.
     */
    public function getBaseSubtotalInvoiced();

    /**
     * Gets the base subtotal refunded amount for the order.
     *
     * @return float|null Base subtotal refunded.
     */
    public function getBaseSubtotalRefunded();

    /**
     * Gets the base tax amount for the order.
     *
     * @return float|null Base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the base tax canceled for the order.
     *
     * @return float|null Base tax canceled.
     */
    public function getBaseTaxCanceled();

    /**
     * Gets the base tax invoiced amount for the order.
     *
     * @return float|null Base tax invoiced.
     */
    public function getBaseTaxInvoiced();

    /**
     * Gets the base tax refunded amount for the order.
     *
     * @return float|null Base tax refunded.
     */
    public function getBaseTaxRefunded();

    /**
     * Gets the base total canceled for the order.
     *
     * @return float|null Base total canceled.
     */
    public function getBaseTotalCanceled();

    /**
     * Gets the base total due for the order.
     *
     * @return float|null Base total due.
     */
    public function getBaseTotalDue();

    /**
     * Gets the base total invoiced amount for the order.
     *
     * @return float|null Base total invoiced.
     */
    public function getBaseTotalInvoiced();

    /**
     * Gets the base total invoiced cost for the order.
     *
     * @return float|null Base total invoiced cost.
     */
    public function getBaseTotalInvoicedCost();

    /**
     * Gets the base total offline refunded amount for the order.
     *
     * @return float|null Base total offline refunded.
     */
    public function getBaseTotalOfflineRefunded();

    /**
     * Gets the base total online refunded amount for the order.
     *
     * @return float|null Base total online refunded.
     */
    public function getBaseTotalOnlineRefunded();

    /**
     * Gets the base total paid for the order.
     *
     * @return float|null Base total paid.
     */
    public function getBaseTotalPaid();

    /**
     * Gets the base total quantity ordered for the order.
     *
     * @return float|null Base total quantity ordered.
     */
    public function getBaseTotalQtyOrdered();

    /**
     * Gets the base total refunded amount for the order.
     *
     * @return float|null Base total refunded.
     */
    public function getBaseTotalRefunded();

    /**
     * Gets the base-to-global rate for the order.
     *
     * @return float|null Base-to-global rate.
     */
    public function getBaseToGlobalRate();

    /**
     * Gets the base-to-order rate for the order.
     *
     * @return float|null Base-to-order rate.
     */
    public function getBaseToOrderRate();

    /**
     * Gets the billing address ID for the order.
     *
     * @return int|null Billing address ID.
     */
    public function getBillingAddressId();

    /**
     * Gets the can-ship-partially flag value for the order.
     *
     * @return int|null Can-ship-partially flag value.
     */
    public function getCanShipPartially();

    /**
     * Gets the can-ship-partially-item flag value for the order.
     *
     * @return int|null Can-ship-partially-item flag value.
     */
    public function getCanShipPartiallyItem();

    /**
     * Gets the coupon code for the order.
     *
     * @return string|null Coupon code.
     */
    public function getCouponCode();

    /**
     * Gets the created-at timestamp for the order.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the order.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the customer date-of-birth (DOB) for the order.
     *
     * @return string|null Customer date-of-birth (DOB).
     */
    public function getCustomerDob();

    /**
     * Gets the customer email address for the order.
     *
     * @return string Customer email address.
     */
    public function getCustomerEmail();

    /**
     * Gets the customer first name for the order.
     *
     * @return string|null Customer first name.
     */
    public function getCustomerFirstname();

    /**
     * Gets the customer gender for the order.
     *
     * @return int|null Customer gender.
     */
    public function getCustomerGender();

    /**
     * Gets the customer group ID for the order.
     *
     * @return int|null Customer group ID.
     */
    public function getCustomerGroupId();

    /**
     * Gets the customer ID for the order.
     *
     * @return int|null Customer ID.
     */
    public function getCustomerId();

    /**
     * Gets the customer-is-guest flag value for the order.
     *
     * @return int|null Customer-is-guest flag value.
     */
    public function getCustomerIsGuest();

    /**
     * Gets the customer last name for the order.
     *
     * @return string|null Customer last name.
     */
    public function getCustomerLastname();

    /**
     * Gets the customer middle name for the order.
     *
     * @return string|null Customer middle name.
     */
    public function getCustomerMiddlename();

    /**
     * Gets the customer note for the order.
     *
     * @return string|null Customer note.
     */
    public function getCustomerNote();

    /**
     * Gets the customer-note-notify flag value for the order.
     *
     * @return int|null Customer-note-notify flag value.
     */
    public function getCustomerNoteNotify();

    /**
     * Gets the customer prefix for the order.
     *
     * @return string|null Customer prefix.
     */
    public function getCustomerPrefix();

    /**
     * Gets the customer suffix for the order.
     *
     * @return string|null Customer suffix.
     */
    public function getCustomerSuffix();

    /**
     * Gets the customer value-added tax (VAT) for the order.
     *
     * @return string|null Customer value-added tax (VAT).
     */
    public function getCustomerTaxvat();

    /**
     * Gets the discount amount for the order.
     *
     * @return float|null Discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the discount canceled for the order.
     *
     * @return float|null Discount canceled.
     */
    public function getDiscountCanceled();

    /**
     * Gets the discount description for the order.
     *
     * @return string|null Discount description.
     */
    public function getDiscountDescription();

    /**
     * Gets the discount invoiced amount for the order.
     *
     * @return float|null Discount invoiced.
     */
    public function getDiscountInvoiced();

    /**
     * Gets the discount refunded amount for the order.
     *
     * @return float|null Discount refunded amount.
     */
    public function getDiscountRefunded();

    /**
     * Gets the edit increment value for the order.
     *
     * @return int|null Edit increment value.
     */
    public function getEditIncrement();

    /**
     * Gets the email-sent flag value for the order.
     *
     * @return int|null Email-sent flag value.
     */
    public function getEmailSent();

    /**
     * Gets the ID for the order.
     *
     * @return int|null Order ID.
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
     * Gets the external customer ID for the order.
     *
     * @return string|null External customer ID.
     */
    public function getExtCustomerId();

    /**
     * Gets the external order ID for the order.
     *
     * @return string|null External order ID.
     */
    public function getExtOrderId();

    /**
     * Gets the forced-shipment-with-invoice flag value for the order.
     *
     * @return int|null Forced-shipment-with-invoice flag value.
     */
    public function getForcedShipmentWithInvoice();

    /**
     * Gets the global currency code for the order.
     *
     * @return string|null Global currency code.
     */
    public function getGlobalCurrencyCode();

    /**
     * Gets the grand total for the order.
     *
     * @return float Grand total.
     */
    public function getGrandTotal();

    /**
     * Gets the discount tax compensation amount for the order.
     *
     * @return float|null Discount tax compensation amount.
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Gets the discount tax compensation invoiced amount for the order.
     *
     * @return float|null Discount tax compensation invoiced amount.
     */
    public function getDiscountTaxCompensationInvoiced();

    /**
     * Gets the discount tax compensation refunded amount for the order.
     *
     * @return float|null Discount tax compensation refunded amount.
     */
    public function getDiscountTaxCompensationRefunded();

    /**
     * Gets the hold before state for the order.
     *
     * @return string|null Hold before state.
     */
    public function getHoldBeforeState();

    /**
     * Gets the hold before status for the order.
     *
     * @return string|null Hold before status.
     */
    public function getHoldBeforeStatus();

    /**
     * Gets the increment ID for the order.
     *
     * @return string|null Increment ID.
     */
    public function getIncrementId();

    /**
     * Gets the is-virtual flag value for the order.
     *
     * @return int|null Is-virtual flag value.
     */
    public function getIsVirtual();

    /**
     * Gets the order currency code for the order.
     *
     * @return string|null Order currency code.
     */
    public function getOrderCurrencyCode();

    /**
     * Gets the original increment ID for the order.
     *
     * @return string|null Original increment ID.
     */
    public function getOriginalIncrementId();

    /**
     * Gets the payment authorization amount for the order.
     *
     * @return float|null Payment authorization amount.
     */
    public function getPaymentAuthorizationAmount();

    /**
     * Gets the payment authorization expiration date for the order.
     *
     * @return int|null Payment authorization expiration date.
     */
    public function getPaymentAuthExpiration();

    /**
     * Gets the protect code for the order.
     *
     * @return string|null Protect code.
     */
    public function getProtectCode();

    /**
     * Gets the quote address ID for the order.
     *
     * @return int|null Quote address ID.
     */
    public function getQuoteAddressId();

    /**
     * Gets the quote ID for the order.
     *
     * @return int|null Quote ID.
     */
    public function getQuoteId();

    /**
     * Gets the relation child ID for the order.
     *
     * @return string|null Relation child ID.
     */
    public function getRelationChildId();

    /**
     * Gets the relation child real ID for the order.
     *
     * @return string|null Relation child real ID.
     */
    public function getRelationChildRealId();

    /**
     * Gets the relation parent ID for the order.
     *
     * @return string|null Relation parent ID.
     */
    public function getRelationParentId();

    /**
     * Gets the relation parent real ID for the order.
     *
     * @return string|null Relation parent real ID.
     */
    public function getRelationParentRealId();

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp();

    /**
     * Gets the shipping amount for the order.
     *
     * @return float|null Shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the shipping canceled amount for the order.
     *
     * @return float|null Shipping canceled amount.
     */
    public function getShippingCanceled();

    /**
     * Gets the shipping description for the order.
     *
     * @return string|null Shipping description.
     */
    public function getShippingDescription();

    /**
     * Gets the shipping discount amount for the order.
     *
     * @return float|null Shipping discount amount.
     */
    public function getShippingDiscountAmount();

    /**
     * Gets the shipping discount tax compensation amount for the order.
     *
     * @return float|null Shipping discount tax compensation amount.
     */
    public function getShippingDiscountTaxCompensationAmount();

    /**
     * Gets the shipping including tax amount for the order.
     *
     * @return float|null Shipping including tax amount.
     */
    public function getShippingInclTax();

    /**
     * Gets the shipping invoiced amount for the order.
     *
     * @return float|null Shipping invoiced amount.
     */
    public function getShippingInvoiced();

    /**
     * Gets the shipping refunded amount for the order.
     *
     * @return float|null Shipping refunded amount.
     */
    public function getShippingRefunded();

    /**
     * Gets the shipping tax amount for the order.
     *
     * @return float|null Shipping tax amount.
     */
    public function getShippingTaxAmount();

    /**
     * Gets the shipping tax refunded amount for the order.
     *
     * @return float|null Shipping tax refunded amount.
     */
    public function getShippingTaxRefunded();

    /**
     * Gets the state for the order.
     *
     * @return string|null State.
     */
    public function getState();

    /**
     * Gets the status for the order.
     *
     * @return string|null Status.
     */
    public function getStatus();

    /**
     * Gets the store currency code for the order.
     *
     * @return string|null Store currency code.
     */
    public function getStoreCurrencyCode();

    /**
     * Gets the store ID for the order.
     *
     * @return int|null Store ID.
     */
    public function getStoreId();

    /**
     * Gets the store name for the order.
     *
     * @return string|null Store name.
     */
    public function getStoreName();

    /**
     * Gets the store-to-base rate for the order.
     *
     * @return float|null Store-to-base rate.
     */
    public function getStoreToBaseRate();

    /**
     * Gets the store-to-order rate for the order.
     *
     * @return float|null Store-to-order rate.
     */
    public function getStoreToOrderRate();

    /**
     * Gets the subtotal for the order.
     *
     * @return float|null Subtotal.
     */
    public function getSubtotal();

    /**
     * Gets the subtotal canceled amount for the order.
     *
     * @return float|null Subtotal canceled amount.
     */
    public function getSubtotalCanceled();

    /**
     * Gets the subtotal including tax amount for the order.
     *
     * @return float|null Subtotal including tax amount.
     */
    public function getSubtotalInclTax();

    /**
     * Gets the subtotal invoiced amount for the order.
     *
     * @return float|null Subtotal invoiced amount.
     */
    public function getSubtotalInvoiced();

    /**
     * Gets the subtotal refunded amount for the order.
     *
     * @return float|null Subtotal refunded amount.
     */
    public function getSubtotalRefunded();

    /**
     * Gets the tax amount for the order.
     *
     * @return float|null Tax amount.
     */
    public function getTaxAmount();

    /**
     * Gets the tax canceled amount for the order.
     *
     * @return float|null Tax canceled amount.
     */
    public function getTaxCanceled();

    /**
     * Gets the tax invoiced amount for the order.
     *
     * @return float|null Tax invoiced amount.
     */
    public function getTaxInvoiced();

    /**
     * Gets the tax refunded amount for the order.
     *
     * @return float|null Tax refunded amount.
     */
    public function getTaxRefunded();

    /**
     * Gets the total canceled for the order.
     *
     * @return float|null Total canceled.
     */
    public function getTotalCanceled();

    /**
     * Gets the total due for the order.
     *
     * @return float|null Total due.
     */
    public function getTotalDue();

    /**
     * Gets the total invoiced amount for the order.
     *
     * @return float|null Total invoiced amount.
     */
    public function getTotalInvoiced();

    /**
     * Gets the total item count for the order.
     *
     * @return int|null Total item count.
     */
    public function getTotalItemCount();

    /**
     * Gets the total offline refunded amount for the order.
     *
     * @return float|null Total offline refunded amount.
     */
    public function getTotalOfflineRefunded();

    /**
     * Gets the total online refunded amount for the order.
     *
     * @return float|null Total online refunded amount.
     */
    public function getTotalOnlineRefunded();

    /**
     * Gets the total paid for the order.
     *
     * @return float|null Total paid.
     */
    public function getTotalPaid();

    /**
     * Gets the total quantity ordered for the order.
     *
     * @return float|null Total quantity ordered.
     */
    public function getTotalQtyOrdered();

    /**
     * Gets the total amount refunded amount for the order.
     *
     * @return float|null Total amount refunded.
     */
    public function getTotalRefunded();

    /**
     * Gets the updated-at timestamp for the order.
     *
     * @return string|null Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the weight for the order.
     *
     * @return float|null Weight.
     */
    public function getWeight();

    /**
     * Gets the X-Forwarded-For HTTP header field for the order.
     *
     * This field identifies the originating IP address of a client
     * connecting to a web server through an HTTP proxy or load balancer.
     *
     * @return string|null X-Forwarded-For field value.
     */
    public function getXForwardedFor();

    /**
     * Gets items for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[] Array of items.
     */
    public function getItems();

    /**
     * Sets items for the order.
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Gets the billing address, if any, for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null Billing address. Otherwise, null.
     */
    public function getBillingAddress();

    /**
     * Sets the billing address, if any, for the order.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress
     * @return $this
     */
    public function setBillingAddress(\Magento\Sales\Api\Data\OrderAddressInterface $billingAddress = null);

    /**
     * Gets order payment
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface|null
     */
    public function getPayment();

    /**
     * Sets order payment
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|null $payment
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    public function setPayment(\Magento\Sales\Api\Data\OrderPaymentInterface $payment = null);

    /**
     * Gets status histories for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderStatusHistoryInterface[]|null Array of status histories.
     */
    public function getStatusHistories();

    /**
     * Sets status histories for the order.
     *
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterface[] $statusHistories
     * @return $this
     */
    public function setStatusHistories(array $statusHistories = null);

    /**
     * Sets the state for the order.
     *
     * @param string $state
     * @return $this
     */
    public function setState($state);

    /**
     * Sets the status for the order.
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Sets the coupon code for the order.
     *
     * @param string $code
     * @return $this
     */
    public function setCouponCode($code);

    /**
     * Sets the protect code for the order.
     *
     * @param string $code
     * @return $this
     */
    public function setProtectCode($code);

    /**
     * Sets the shipping description for the order.
     *
     * @param string $description
     * @return $this
     */
    public function setShippingDescription($description);

    /**
     * Sets the is-virtual flag value for the order.
     *
     * @param int $isVirtual
     * @return $this
     */
    public function setIsVirtual($isVirtual);

    /**
     * Sets the store ID for the order.
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id);

    /**
     * Sets the customer ID for the order.
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId($id);

    /**
     * Sets the base discount amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseDiscountAmount($amount);

    /**
     * Sets the base discount canceled for the order.
     *
     * @param float $baseDiscountCanceled
     * @return $this
     */
    public function setBaseDiscountCanceled($baseDiscountCanceled);

    /**
     * Sets the base discount invoiced amount for the order.
     *
     * @param float $baseDiscountInvoiced
     * @return $this
     */
    public function setBaseDiscountInvoiced($baseDiscountInvoiced);

    /**
     * Sets the base discount refunded amount for the order.
     *
     * @param float $baseDiscountRefunded
     * @return $this
     */
    public function setBaseDiscountRefunded($baseDiscountRefunded);

    /**
     * Sets the base grand total for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseGrandTotal($amount);

    /**
     * Sets the base shipping amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingAmount($amount);

    /**
     * Sets the base shipping canceled for the order.
     *
     * @param float $baseShippingCanceled
     * @return $this
     */
    public function setBaseShippingCanceled($baseShippingCanceled);

    /**
     * Sets the base shipping invoiced amount for the order.
     *
     * @param float $baseShippingInvoiced
     * @return $this
     */
    public function setBaseShippingInvoiced($baseShippingInvoiced);

    /**
     * Sets the base shipping refunded amount for the order.
     *
     * @param float $baseShippingRefunded
     * @return $this
     */
    public function setBaseShippingRefunded($baseShippingRefunded);

    /**
     * Sets the base shipping tax amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingTaxAmount($amount);

    /**
     * Sets the base shipping tax refunded amount for the order.
     *
     * @param float $baseShippingTaxRefunded
     * @return $this
     */
    public function setBaseShippingTaxRefunded($baseShippingTaxRefunded);

    /**
     * Sets the base subtotal for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseSubtotal($amount);

    /**
     * Sets the base subtotal canceled for the order.
     *
     * @param float $baseSubtotalCanceled
     * @return $this
     */
    public function setBaseSubtotalCanceled($baseSubtotalCanceled);

    /**
     * Sets the base subtotal invoiced amount for the order.
     *
     * @param float $baseSubtotalInvoiced
     * @return $this
     */
    public function setBaseSubtotalInvoiced($baseSubtotalInvoiced);

    /**
     * Sets the base subtotal refunded amount for the order.
     *
     * @param float $baseSubtotalRefunded
     * @return $this
     */
    public function setBaseSubtotalRefunded($baseSubtotalRefunded);

    /**
     * Sets the base tax amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseTaxAmount($amount);

    /**
     * Sets the base tax canceled for the order.
     *
     * @param float $baseTaxCanceled
     * @return $this
     */
    public function setBaseTaxCanceled($baseTaxCanceled);

    /**
     * Sets the base tax invoiced amount for the order.
     *
     * @param float $baseTaxInvoiced
     * @return $this
     */
    public function setBaseTaxInvoiced($baseTaxInvoiced);

    /**
     * Sets the base tax refunded amount for the order.
     *
     * @param float $baseTaxRefunded
     * @return $this
     */
    public function setBaseTaxRefunded($baseTaxRefunded);

    /**
     * Sets the base-to-global rate for the order.
     *
     * @param float $rate
     * @return $this
     */
    public function setBaseToGlobalRate($rate);

    /**
     * Sets the base-to-order rate for the order.
     *
     * @param float $rate
     * @return $this
     */
    public function setBaseToOrderRate($rate);

    /**
     * Sets the base total canceled for the order.
     *
     * @param float $baseTotalCanceled
     * @return $this
     */
    public function setBaseTotalCanceled($baseTotalCanceled);

    /**
     * Sets the base total invoiced amount for the order.
     *
     * @param float $baseTotalInvoiced
     * @return $this
     */
    public function setBaseTotalInvoiced($baseTotalInvoiced);

    /**
     * Sets the base total invoiced cost for the order.
     *
     * @param float $baseTotalInvoicedCost
     * @return $this
     */
    public function setBaseTotalInvoicedCost($baseTotalInvoicedCost);

    /**
     * Sets the base total offline refunded amount for the order.
     *
     * @param float $baseTotalOfflineRefunded
     * @return $this
     */
    public function setBaseTotalOfflineRefunded($baseTotalOfflineRefunded);

    /**
     * Sets the base total online refunded amount for the order.
     *
     * @param float $baseTotalOnlineRefunded
     * @return $this
     */
    public function setBaseTotalOnlineRefunded($baseTotalOnlineRefunded);

    /**
     * Sets the base total paid for the order.
     *
     * @param float $baseTotalPaid
     * @return $this
     */
    public function setBaseTotalPaid($baseTotalPaid);

    /**
     * Sets the base total quantity ordered for the order.
     *
     * @param float $baseTotalQtyOrdered
     * @return $this
     */
    public function setBaseTotalQtyOrdered($baseTotalQtyOrdered);

    /**
     * Sets the base total refunded amount for the order.
     *
     * @param float $baseTotalRefunded
     * @return $this
     */
    public function setBaseTotalRefunded($baseTotalRefunded);

    /**
     * Sets the discount amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setDiscountAmount($amount);

    /**
     * Sets the discount canceled for the order.
     *
     * @param float $discountCanceled
     * @return $this
     */
    public function setDiscountCanceled($discountCanceled);

    /**
     * Sets the discount invoiced amount for the order.
     *
     * @param float $discountInvoiced
     * @return $this
     */
    public function setDiscountInvoiced($discountInvoiced);

    /**
     * Sets the discount refunded amount for the order.
     *
     * @param float $discountRefunded
     * @return $this
     */
    public function setDiscountRefunded($discountRefunded);

    /**
     * Sets the grand total for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setGrandTotal($amount);

    /**
     * Sets the shipping amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount);

    /**
     * Sets the shipping canceled amount for the order.
     *
     * @param float $shippingCanceled
     * @return $this
     */
    public function setShippingCanceled($shippingCanceled);

    /**
     * Sets the shipping invoiced amount for the order.
     *
     * @param float $shippingInvoiced
     * @return $this
     */
    public function setShippingInvoiced($shippingInvoiced);

    /**
     * Sets the shipping refunded amount for the order.
     *
     * @param float $shippingRefunded
     * @return $this
     */
    public function setShippingRefunded($shippingRefunded);

    /**
     * Sets the shipping tax amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingTaxAmount($amount);

    /**
     * Sets the shipping tax refunded amount for the order.
     *
     * @param float $shippingTaxRefunded
     * @return $this
     */
    public function setShippingTaxRefunded($shippingTaxRefunded);

    /**
     * Sets the store-to-base rate for the order.
     *
     * @param float $rate
     * @return $this
     */
    public function setStoreToBaseRate($rate);

    /**
     * Sets the store-to-order rate for the order.
     *
     * @param float $rate
     * @return $this
     */
    public function setStoreToOrderRate($rate);

    /**
     * Sets the subtotal for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setSubtotal($amount);

    /**
     * Sets the subtotal canceled amount for the order.
     *
     * @param float $subtotalCanceled
     * @return $this
     */
    public function setSubtotalCanceled($subtotalCanceled);

    /**
     * Sets the subtotal invoiced amount for the order.
     *
     * @param float $subtotalInvoiced
     * @return $this
     */
    public function setSubtotalInvoiced($subtotalInvoiced);

    /**
     * Sets the subtotal refunded amount for the order.
     *
     * @param float $subtotalRefunded
     * @return $this
     */
    public function setSubtotalRefunded($subtotalRefunded);

    /**
     * Sets the tax amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setTaxAmount($amount);

    /**
     * Sets the tax canceled amount for the order.
     *
     * @param float $taxCanceled
     * @return $this
     */
    public function setTaxCanceled($taxCanceled);

    /**
     * Sets the tax invoiced amount for the order.
     *
     * @param float $taxInvoiced
     * @return $this
     */
    public function setTaxInvoiced($taxInvoiced);

    /**
     * Sets the tax refunded amount for the order.
     *
     * @param float $taxRefunded
     * @return $this
     */
    public function setTaxRefunded($taxRefunded);

    /**
     * Sets the total canceled for the order.
     *
     * @param float $totalCanceled
     * @return $this
     */
    public function setTotalCanceled($totalCanceled);

    /**
     * Sets the total invoiced amount for the order.
     *
     * @param float $totalInvoiced
     * @return $this
     */
    public function setTotalInvoiced($totalInvoiced);

    /**
     * Sets the total offline refunded amount for the order.
     *
     * @param float $totalOfflineRefunded
     * @return $this
     */
    public function setTotalOfflineRefunded($totalOfflineRefunded);

    /**
     * Sets the total online refunded amount for the order.
     *
     * @param float $totalOnlineRefunded
     * @return $this
     */
    public function setTotalOnlineRefunded($totalOnlineRefunded);

    /**
     * Sets the total paid for the order.
     *
     * @param float $totalPaid
     * @return $this
     */
    public function setTotalPaid($totalPaid);

    /**
     * Sets the total quantity ordered for the order.
     *
     * @param float $totalQtyOrdered
     * @return $this
     */
    public function setTotalQtyOrdered($totalQtyOrdered);

    /**
     * Sets the total amount refunded amount for the order.
     *
     * @param float $totalRefunded
     * @return $this
     */
    public function setTotalRefunded($totalRefunded);

    /**
     * Sets the can-ship-partially flag value for the order.
     *
     * @param int $flag
     * @return $this
     */
    public function setCanShipPartially($flag);

    /**
     * Sets the can-ship-partially-item flag value for the order.
     *
     * @param int $flag
     * @return $this
     */
    public function setCanShipPartiallyItem($flag);

    /**
     * Sets the customer-is-guest flag value for the order.
     *
     * @param int $customerIsGuest
     * @return $this
     */
    public function setCustomerIsGuest($customerIsGuest);

    /**
     * Sets the customer-note-notify flag value for the order.
     *
     * @param int $customerNoteNotify
     * @return $this
     */
    public function setCustomerNoteNotify($customerNoteNotify);

    /**
     * Sets the billing address ID for the order.
     *
     * @param int $id
     * @return $this
     */
    public function setBillingAddressId($id);

    /**
     * Sets the customer group ID for the order.
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerGroupId($id);

    /**
     * Sets the edit increment value for the order.
     *
     * @param int $editIncrement
     * @return $this
     */
    public function setEditIncrement($editIncrement);

    /**
     * Sets the email-sent flag value for the order.
     *
     * @param int $emailSent
     * @return $this
     */
    public function setEmailSent($emailSent);

    /**
     * Sets the forced-shipment-with-invoice flag value for the order.
     *
     * @param int $forcedShipmentWithInvoice
     * @return $this
     */
    public function setForcedShipmentWithInvoice($forcedShipmentWithInvoice);

    /**
     * Sets the payment authorization expiration date for the order.
     *
     * @param int $paymentAuthExpiration
     * @return $this
     */
    public function setPaymentAuthExpiration($paymentAuthExpiration);

    /**
     * Sets the quote address ID for the order.
     *
     * @param int $id
     * @return $this
     */
    public function setQuoteAddressId($id);

    /**
     * Sets the quote ID for the order.
     *
     * @param int $id
     * @return $this
     */
    public function setQuoteId($id);

    /**
     * Sets the negative adjustment value for the order.
     *
     * @param float $adjustmentNegative
     * @return $this
     */
    public function setAdjustmentNegative($adjustmentNegative);

    /**
     * Sets the positive adjustment value for the order.
     *
     * @param float $adjustmentPositive
     * @return $this
     */
    public function setAdjustmentPositive($adjustmentPositive);

    /**
     * Sets the base negative adjustment value for the order.
     *
     * @param float $baseAdjustmentNegative
     * @return $this
     */
    public function setBaseAdjustmentNegative($baseAdjustmentNegative);

    /**
     * Sets the base positive adjustment value for the order.
     *
     * @param float $baseAdjustmentPositive
     * @return $this
     */
    public function setBaseAdjustmentPositive($baseAdjustmentPositive);

    /**
     * Sets the base shipping discount amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingDiscountAmount($amount);

    /**
     * Sets the base subtotal including tax for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseSubtotalInclTax($amount);

    /**
     * Sets the base total due for the order.
     *
     * @param float $baseTotalDue
     * @return $this
     */
    public function setBaseTotalDue($baseTotalDue);

    /**
     * Sets the payment authorization amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setPaymentAuthorizationAmount($amount);

    /**
     * Sets the shipping discount amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingDiscountAmount($amount);

    /**
     * Sets the subtotal including tax amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setSubtotalInclTax($amount);

    /**
     * Sets the total due for the order.
     *
     * @param float $totalDue
     * @return $this
     */
    public function setTotalDue($totalDue);

    /**
     * Sets the weight for the order.
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Sets the customer date-of-birth (DOB) for the order.
     *
     * @param string $customerDob
     * @return $this
     */
    public function setCustomerDob($customerDob);

    /**
     * Sets the increment ID for the order.
     *
     * @param string $id
     * @return $this
     */
    public function setIncrementId($id);

    /**
     * Sets the applied rule IDs for the order.
     *
     * @param string $appliedRuleIds
     * @return $this
     */
    public function setAppliedRuleIds($appliedRuleIds);

    /**
     * Sets the base currency code for the order.
     *
     * @param string $code
     * @return $this
     */
    public function setBaseCurrencyCode($code);

    /**
     * Sets the customer email address for the order.
     *
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Sets the customer first name for the order.
     *
     * @param string $customerFirstname
     * @return $this
     */
    public function setCustomerFirstname($customerFirstname);

    /**
     * Sets the customer last name for the order.
     *
     * @param string $customerLastname
     * @return $this
     */
    public function setCustomerLastname($customerLastname);

    /**
     * Sets the customer middle name for the order.
     *
     * @param string $customerMiddlename
     * @return $this
     */
    public function setCustomerMiddlename($customerMiddlename);

    /**
     * Sets the customer prefix for the order.
     *
     * @param string $customerPrefix
     * @return $this
     */
    public function setCustomerPrefix($customerPrefix);

    /**
     * Sets the customer suffix for the order.
     *
     * @param string $customerSuffix
     * @return $this
     */
    public function setCustomerSuffix($customerSuffix);

    /**
     * Sets the customer value-added tax (VAT) for the order.
     *
     * @param string $customerTaxvat
     * @return $this
     */
    public function setCustomerTaxvat($customerTaxvat);

    /**
     * Sets the discount description for the order.
     *
     * @param string $description
     * @return $this
     */
    public function setDiscountDescription($description);

    /**
     * Sets the external customer ID for the order.
     *
     * @param string $id
     * @return $this
     */
    public function setExtCustomerId($id);

    /**
     * Sets the external order ID for the order.
     *
     * @param string $id
     * @return $this
     */
    public function setExtOrderId($id);

    /**
     * Sets the global currency code for the order.
     *
     * @param string $code
     * @return $this
     */
    public function setGlobalCurrencyCode($code);

    /**
     * Sets the hold before state for the order.
     *
     * @param string $holdBeforeState
     * @return $this
     */
    public function setHoldBeforeState($holdBeforeState);

    /**
     * Sets the hold before status for the order.
     *
     * @param string $holdBeforeStatus
     * @return $this
     */
    public function setHoldBeforeStatus($holdBeforeStatus);

    /**
     * Sets the order currency code for the order.
     *
     * @param string $code
     * @return $this
     */
    public function setOrderCurrencyCode($code);

    /**
     * Sets the original increment ID for the order.
     *
     * @param string $id
     * @return $this
     */
    public function setOriginalIncrementId($id);

    /**
     * Sets the relation child ID for the order.
     *
     * @param string $id
     * @return $this
     */
    public function setRelationChildId($id);

    /**
     * Sets the relation child real ID for the order.
     *
     * @param string $realId
     * @return $this
     */
    public function setRelationChildRealId($realId);

    /**
     * Sets the relation parent ID for the order.
     *
     * @param string $id
     * @return $this
     */
    public function setRelationParentId($id);

    /**
     * Sets the relation parent real ID for the order.
     *
     * @param string $realId
     * @return $this
     */
    public function setRelationParentRealId($realId);

    /**
     * Sets the remote IP address for the order.
     *
     * @param string $remoteIp
     * @return $this
     */
    public function setRemoteIp($remoteIp);

    /**
     * Sets the store currency code for the order.
     *
     * @param string $code
     * @return $this
     */
    public function setStoreCurrencyCode($code);

    /**
     * Sets the store name for the order.
     *
     * @param string $storeName
     * @return $this
     */
    public function setStoreName($storeName);

    /**
     * Sets the X-Forwarded-For HTTP header field for the order.
     *
     * @param string $xForwardedFor
     * @return $this
     */
    public function setXForwardedFor($xForwardedFor);

    /**
     * Sets the customer note for the order.
     *
     * @param string $customerNote
     * @return $this
     */
    public function setCustomerNote($customerNote);

    /**
     * Sets the updated-at timestamp for the order.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setUpdatedAt($timestamp);

    /**
     * Sets the total item count for the order.
     *
     * @param int $totalItemCount
     * @return $this
     */
    public function setTotalItemCount($totalItemCount);

    /**
     * Sets the customer gender for the order.
     *
     * @param int $customerGender
     * @return $this
     */
    public function setCustomerGender($customerGender);

    /**
     * Sets the discount tax compensation amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setDiscountTaxCompensationAmount($amount);

    /**
     * Sets the base discount tax compensation amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseDiscountTaxCompensationAmount($amount);

    /**
     * Sets the shipping discount tax compensation amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingDiscountTaxCompensationAmount($amount);

    /**
     * Sets the base shipping discount tax compensation amount for the order.
     *
     * @param float $amnt
     * @return $this
     */
    public function setBaseShippingDiscountTaxCompensationAmnt($amnt);

    /**
     * Sets the discount tax compensation invoiced amount for the order.
     *
     * @param float $discountTaxCompensationInvoiced
     * @return $this
     */
    public function setDiscountTaxCompensationInvoiced($discountTaxCompensationInvoiced);

    /**
     * Sets the base discount tax compensation invoiced amount for the order.
     *
     * @param float $baseDiscountTaxCompensationInvoiced
     * @return $this
     */
    public function setBaseDiscountTaxCompensationInvoiced($baseDiscountTaxCompensationInvoiced);

    /**
     * Sets the discount tax compensation refunded amount for the order.
     *
     * @param float $discountTaxCompensationRefunded
     * @return $this
     */
    public function setDiscountTaxCompensationRefunded($discountTaxCompensationRefunded);

    /**
     * Sets the base discount tax compensation refunded amount for the order.
     *
     * @param float $baseDiscountTaxCompensationRefunded
     * @return $this
     */
    public function setBaseDiscountTaxCompensationRefunded($baseDiscountTaxCompensationRefunded);

    /**
     * Sets the shipping including tax amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingInclTax($amount);

    /**
     * Sets the base shipping including tax for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseShippingInclTax($amount);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\OrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\OrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\OrderExtensionInterface $extensionAttributes);
}
