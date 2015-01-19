<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
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
     * Shipping address ID.
     */
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
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
     * Shipping method.
     */
    const SHIPPING_METHOD = 'shipping_method';
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
     * Shipping address.
     */
    const SHIPPING_ADDRESS = 'shipping_address';
    /*
     * Payments.
     */
    const PAYMENTS = 'payments';
    /*
     * Addresses.
     */
    const ADDRESSES = 'addresses';
    /*
     * Status histories.
     */
    const STATUS_HISTORIES = 'status_histories';

    /**
     * Gets the negative adjustment value for the order.
     *
     * @return float Negative adjustment value.
     */
    public function getAdjustmentNegative();

    /**
     * Gets the positive adjustment value for the order.
     *
     * @return float Positive adjustment value.
     */
    public function getAdjustmentPositive();

    /**
     * Gets the applied rule IDs for the order.
     *
     * @return string Applied rule IDs.
     */
    public function getAppliedRuleIds();

    /**
     * Gets the base negative adjustment value for the order.
     *
     * @return float Base negative adjustment value.
     */
    public function getBaseAdjustmentNegative();

    /**
     * Gets the base positive adjustment value for the order.
     *
     * @return float Base positive adjustment value.
     */
    public function getBaseAdjustmentPositive();

    /**
     * Gets the base currency code for the order.
     *
     * @return string Base currency code.
     */
    public function getBaseCurrencyCode();

    /**
     * Gets the base discount amount for the order.
     *
     * @return float Base discount amount.
     */
    public function getBaseDiscountAmount();

    /**
     * Gets the base discount canceled for the order.
     *
     * @return float Base discount canceled.
     */
    public function getBaseDiscountCanceled();

    /**
     * Gets the base discount invoiced amount for the order.
     *
     * @return float Base discount invoiced.
     */
    public function getBaseDiscountInvoiced();

    /**
     * Gets the base discount refunded amount for the order.
     *
     * @return float Base discount refunded.
     */
    public function getBaseDiscountRefunded();

    /**
     * Gets the base grand total for the order.
     *
     * @return float Base grand total.
     */
    public function getBaseGrandTotal();

    /**
     * Gets the base hidden tax amount for the order.
     *
     * @return float Base hidden tax amount.
     */
    public function getBaseHiddenTaxAmount();

    /**
     * Gets the base hidden tax invoiced amount for the order.
     *
     * @return float Base hidden tax invoiced.
     */
    public function getBaseHiddenTaxInvoiced();

    /**
     * Gets the base hidden tax refunded amount for the order.
     *
     * @return float Base hidden tax refunded.
     */
    public function getBaseHiddenTaxRefunded();

    /**
     * Gets the base shipping amount for the order.
     *
     * @return float Base shipping amount.
     */
    public function getBaseShippingAmount();

    /**
     * Gets the base shipping canceled for the order.
     *
     * @return float Base shipping canceled.
     */
    public function getBaseShippingCanceled();

    /**
     * Gets the base shipping discount amount for the order.
     *
     * @return float Base shipping discount amount.
     */
    public function getBaseShippingDiscountAmount();

    /**
     * Gets the base shipping hidden tax amount for the order.
     *
     * @return float Base shipping hidden tax amount.
     */
    public function getBaseShippingHiddenTaxAmnt();

    /**
     * Gets the base shipping including tax for the order.
     *
     * @return float Base shipping including tax.
     */
    public function getBaseShippingInclTax();

    /**
     * Gets the base shipping invoiced amount for the order.
     *
     * @return float Base shipping invoiced.
     */
    public function getBaseShippingInvoiced();

    /**
     * Gets the base shipping refunded amount for the order.
     *
     * @return float Base shipping refunded.
     */
    public function getBaseShippingRefunded();

    /**
     * Gets the base shipping tax amount for the order.
     *
     * @return float Base shipping tax amount.
     */
    public function getBaseShippingTaxAmount();

    /**
     * Gets the base shipping tax refunded amount for the order.
     *
     * @return float Base shipping tax refunded.
     */
    public function getBaseShippingTaxRefunded();

    /**
     * Gets the base subtotal for the order.
     *
     * @return float Base subtotal.
     */
    public function getBaseSubtotal();

    /**
     * Gets the base subtotal canceled for the order.
     *
     * @return float Base subtotal canceled.
     */
    public function getBaseSubtotalCanceled();

    /**
     * Gets the base subtotal including tax for the order.
     *
     * @return float Base subtotal including tax.
     */
    public function getBaseSubtotalInclTax();

    /**
     * Gets the base subtotal invoiced amount for the order.
     *
     * @return float Base subtotal invoiced.
     */
    public function getBaseSubtotalInvoiced();

    /**
     * Gets the base subtotal refunded amount for the order.
     *
     * @return float Base subtotal refunded.
     */
    public function getBaseSubtotalRefunded();

    /**
     * Gets the base tax amount for the order.
     *
     * @return float Base tax amount.
     */
    public function getBaseTaxAmount();

    /**
     * Gets the base tax canceled for the order.
     *
     * @return float Base tax canceled.
     */
    public function getBaseTaxCanceled();

    /**
     * Gets the base tax invoiced amount for the order.
     *
     * @return float Base tax invoiced.
     */
    public function getBaseTaxInvoiced();

    /**
     * Gets the base tax refunded amount for the order.
     *
     * @return float Base tax refunded.
     */
    public function getBaseTaxRefunded();

    /**
     * Gets the base total canceled for the order.
     *
     * @return float Base total canceled.
     */
    public function getBaseTotalCanceled();

    /**
     * Gets the base total due for the order.
     *
     * @return float Base total due.
     */
    public function getBaseTotalDue();

    /**
     * Gets the base total invoiced amount for the order.
     *
     * @return float Base total invoiced.
     */
    public function getBaseTotalInvoiced();

    /**
     * Gets the base total invoiced cost for the order.
     *
     * @return float Base total invoiced cost.
     */
    public function getBaseTotalInvoicedCost();

    /**
     * Gets the base total offline refunded amount for the order.
     *
     * @return float Base total offline refunded.
     */
    public function getBaseTotalOfflineRefunded();

    /**
     * Gets the base total online refunded amount for the order.
     *
     * @return float Base total online refunded.
     */
    public function getBaseTotalOnlineRefunded();

    /**
     * Gets the base total paid for the order.
     *
     * @return float Base total paid.
     */
    public function getBaseTotalPaid();

    /**
     * Gets the base total quantity ordered for the order.
     *
     * @return float Base total quantity ordered.
     */
    public function getBaseTotalQtyOrdered();

    /**
     * Gets the base total refunded amount for the order.
     *
     * @return float Base total refunded.
     */
    public function getBaseTotalRefunded();

    /**
     * Gets the base-to-global rate for the order.
     *
     * @return float Base-to-global rate.
     */
    public function getBaseToGlobalRate();

    /**
     * Gets the base-to-order rate for the order.
     *
     * @return float Base-to-order rate.
     */
    public function getBaseToOrderRate();

    /**
     * Gets the billing address ID for the order.
     *
     * @return int Billing address ID.
     */
    public function getBillingAddressId();

    /**
     * Gets the can-ship-partially flag value for the order.
     *
     * @return int Can-ship-partially flag value.
     */
    public function getCanShipPartially();

    /**
     * Gets the can-ship-partially-item flag value for the order.
     *
     * @return int Can-ship-partially-item flag value.
     */
    public function getCanShipPartiallyItem();

    /**
     * Gets the coupon code for the order.
     *
     * @return string Coupon code.
     */
    public function getCouponCode();

    /**
     * Gets the created-at timestamp for the order.
     *
     * @return string Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Gets the customer date-of-birth (DOB) for the order.
     *
     * @return string Customer date-of-birth (DOB).
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
     * @return string Customer first name.
     */
    public function getCustomerFirstname();

    /**
     * Gets the customer gender for the order.
     *
     * @return int Customer gender.
     */
    public function getCustomerGender();

    /**
     * Gets the customer group ID for the order.
     *
     * @return int Customer group ID.
     */
    public function getCustomerGroupId();

    /**
     * Gets the customer ID for the order.
     *
     * @return int Customer ID.
     */
    public function getCustomerId();

    /**
     * Gets the customer-is-guest flag value for the order.
     *
     * @return int Customer-is-guest flag value.
     */
    public function getCustomerIsGuest();

    /**
     * Gets the customer last name for the order.
     *
     * @return string Customer last name.
     */
    public function getCustomerLastname();

    /**
     * Gets the customer middle name for the order.
     *
     * @return string Customer middle name.
     */
    public function getCustomerMiddlename();

    /**
     * Gets the customer note for the order.
     *
     * @return string Customer note.
     */
    public function getCustomerNote();

    /**
     * Gets the customer-note-notify flag value for the order.
     *
     * @return int Customer-note-notify flag value.
     */
    public function getCustomerNoteNotify();

    /**
     * Gets the customer prefix for the order.
     *
     * @return string Customer prefix.
     */
    public function getCustomerPrefix();

    /**
     * Gets the customer suffix for the order.
     *
     * @return string Customer suffix.
     */
    public function getCustomerSuffix();

    /**
     * Gets the customer value-added tax (VAT) for the order.
     *
     * @return string Customer value-added tax (VAT).
     */
    public function getCustomerTaxvat();

    /**
     * Gets the discount amount for the order.
     *
     * @return float Discount amount.
     */
    public function getDiscountAmount();

    /**
     * Gets the discount canceled for the order.
     *
     * @return float Discount canceled.
     */
    public function getDiscountCanceled();

    /**
     * Gets the discount description for the order.
     *
     * @return string Discount description.
     */
    public function getDiscountDescription();

    /**
     * Gets the discount invoiced amount for the order.
     *
     * @return float Discount invoiced.
     */
    public function getDiscountInvoiced();

    /**
     * Gets the discount refunded amount for the order.
     *
     * @return float Discount refunded amount.
     */
    public function getDiscountRefunded();

    /**
     * Gets the edit increment value for the order.
     *
     * @return int Edit increment value.
     */
    public function getEditIncrement();

    /**
     * Gets the email-sent flag value for the order.
     *
     * @return int Email-sent flag value.
     */
    public function getEmailSent();

    /**
     * Gets the ID for the order.
     *
     * @return int Order ID.
     */
    public function getEntityId();

    /**
     * Gets the external customer ID for the order.
     *
     * @return string External customer ID.
     */
    public function getExtCustomerId();

    /**
     * Gets the external order ID for the order.
     *
     * @return string External order ID.
     */
    public function getExtOrderId();

    /**
     * Gets the forced-shipment-with-invoice flag value for the order.
     *
     * @return int Forced-shipment-with-invoice flag value.
     */
    public function getForcedShipmentWithInvoice();

    /**
     * Gets the global currency code for the order.
     *
     * @return string Global currency code.
     */
    public function getGlobalCurrencyCode();

    /**
     * Gets the grand total for the order.
     *
     * @return float Grand total.
     */
    public function getGrandTotal();

    /**
     * Gets the hidden tax amount for the order.
     *
     * @return float Hidden tax amount.
     */
    public function getHiddenTaxAmount();

    /**
     * Gets the hidden tax invoiced amount for the order.
     *
     * @return float Hidden tax invoiced amount.
     */
    public function getHiddenTaxInvoiced();

    /**
     * Gets the hidden tax refunded amount for the order.
     *
     * @return float Hidden tax refunded amount.
     */
    public function getHiddenTaxRefunded();

    /**
     * Gets the hold before state for the order.
     *
     * @return string Hold before state.
     */
    public function getHoldBeforeState();

    /**
     * Gets the hold before status for the order.
     *
     * @return string Hold before status.
     */
    public function getHoldBeforeStatus();

    /**
     * Gets the increment ID for the order.
     *
     * @return string Increment ID.
     */
    public function getIncrementId();

    /**
     * Gets the is-virtual flag value for the order.
     *
     * @return int Is-virtual flag value.
     */
    public function getIsVirtual();

    /**
     * Gets the order currency code for the order.
     *
     * @return string Order currency code.
     */
    public function getOrderCurrencyCode();

    /**
     * Gets the original increment ID for the order.
     *
     * @return string Original increment ID.
     */
    public function getOriginalIncrementId();

    /**
     * Gets the payment authorization amount for the order.
     *
     * @return float Payment authorization amount.
     */
    public function getPaymentAuthorizationAmount();

    /**
     * Gets the payment authorization expiration date for the order.
     *
     * @return int Payment authorization expiration date.
     */
    public function getPaymentAuthExpiration();

    /**
     * Gets the protect code for the order.
     *
     * @return string Protect code.
     */
    public function getProtectCode();

    /**
     * Gets the quote address ID for the order.
     *
     * @return int Quote address ID.
     */
    public function getQuoteAddressId();

    /**
     * Gets the quote ID for the order.
     *
     * @return int Quote ID.
     */
    public function getQuoteId();

    /**
     * Gets the relation child ID for the order.
     *
     * @return string Relation child ID.
     */
    public function getRelationChildId();

    /**
     * Gets the relation child real ID for the order.
     *
     * @return string Relation child real ID.
     */
    public function getRelationChildRealId();

    /**
     * Gets the relation parent ID for the order.
     *
     * @return string Relation parent ID.
     */
    public function getRelationParentId();

    /**
     * Gets the relation parent real ID for the order.
     *
     * @return string Relation parent real ID.
     */
    public function getRelationParentRealId();

    /**
     * Gets the remote IP address for the order.
     *
     * @return string Remote IP address.
     */
    public function getRemoteIp();

    /**
     * Gets the shipping address ID for the order.
     *
     * @return int Shipping address ID.
     */
    public function getShippingAddressId();

    /**
     * Gets the shipping amount for the order.
     *
     * @return float Shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the shipping canceled amount for the order.
     *
     * @return float Shipping canceled amount.
     */
    public function getShippingCanceled();

    /**
     * Gets the shipping description for the order.
     *
     * @return string Shipping description.
     */
    public function getShippingDescription();

    /**
     * Gets the shipping discount amount for the order.
     *
     * @return float Shipping discount amount.
     */
    public function getShippingDiscountAmount();

    /**
     * Gets the shipping hidden tax amount for the order.
     *
     * @return float Shipping hidden tax amount.
     */
    public function getShippingHiddenTaxAmount();

    /**
     * Gets the shipping including tax amount for the order.
     *
     * @return float Shipping including tax amount.
     */
    public function getShippingInclTax();

    /**
     * Gets the shipping invoiced amount for the order.
     *
     * @return float Shipping invoiced amount.
     */
    public function getShippingInvoiced();

    /**
     * Gets the shipping method for the order.
     *
     * @return string Shipping method.
     */
    public function getShippingMethod();

    /**
     * Gets the shipping refunded amount for the order.
     *
     * @return float Shipping refunded amount.
     */
    public function getShippingRefunded();

    /**
     * Gets the shipping tax amount for the order.
     *
     * @return float Shipping tax amount.
     */
    public function getShippingTaxAmount();

    /**
     * Gets the shipping tax refunded amount for the order.
     *
     * @return float Shipping tax refunded amount.
     */
    public function getShippingTaxRefunded();

    /**
     * Gets the state for the order.
     *
     * @return string State.
     */
    public function getState();

    /**
     * Gets the status for the order.
     *
     * @return string Status.
     */
    public function getStatus();

    /**
     * Gets the store currency code for the order.
     *
     * @return string Store currency code.
     */
    public function getStoreCurrencyCode();

    /**
     * Gets the store ID for the order.
     *
     * @return int Store ID.
     */
    public function getStoreId();

    /**
     * Gets the store name for the order.
     *
     * @return string Store name.
     */
    public function getStoreName();

    /**
     * Gets the store-to-base rate for the order.
     *
     * @return float Store-to-base rate.
     */
    public function getStoreToBaseRate();

    /**
     * Gets the store-to-order rate for the order.
     *
     * @return float Store-to-order rate.
     */
    public function getStoreToOrderRate();

    /**
     * Gets the subtotal for the order.
     *
     * @return float Subtotal.
     */
    public function getSubtotal();

    /**
     * Gets the subtotal canceled amount for the order.
     *
     * @return float Subtotal canceled amount.
     */
    public function getSubtotalCanceled();

    /**
     * Gets the subtotal including tax amount for the order.
     *
     * @return float Subtotal including tax amount.
     */
    public function getSubtotalInclTax();

    /**
     * Gets the subtotal invoiced amount for the order.
     *
     * @return float Subtotal invoiced amount.
     */
    public function getSubtotalInvoiced();

    /**
     * Gets the subtotal refunded amount for the order.
     *
     * @return float Subtotal refunded amount.
     */
    public function getSubtotalRefunded();

    /**
     * Gets the tax amount for the order.
     *
     * @return float Tax amount.
     */
    public function getTaxAmount();

    /**
     * Gets the tax canceled amount for the order.
     *
     * @return float Tax canceled amount.
     */
    public function getTaxCanceled();

    /**
     * Gets the tax invoiced amount for the order.
     *
     * @return float Tax invoiced amount.
     */
    public function getTaxInvoiced();

    /**
     * Gets the tax refunded amount for the order.
     *
     * @return float Tax refunded amount.
     */
    public function getTaxRefunded();

    /**
     * Gets the total canceled for the order.
     *
     * @return float Total canceled.
     */
    public function getTotalCanceled();

    /**
     * Gets the total due for the order.
     *
     * @return float Total due.
     */
    public function getTotalDue();

    /**
     * Gets the total invoiced amount for the order.
     *
     * @return float Total invoiced amount.
     */
    public function getTotalInvoiced();

    /**
     * Gets the total item count for the order.
     *
     * @return int Total item count.
     */
    public function getTotalItemCount();

    /**
     * Gets the total offline refunded amount for the order.
     *
     * @return float Total offline refunded amount.
     */
    public function getTotalOfflineRefunded();

    /**
     * Gets the total online refunded amount for the order.
     *
     * @return float Total online refunded amount.
     */
    public function getTotalOnlineRefunded();

    /**
     * Gets the total paid for the order.
     *
     * @return float Total paid.
     */
    public function getTotalPaid();

    /**
     * Gets the total quantity ordered for the order.
     *
     * @return float Total quantity ordered.
     */
    public function getTotalQtyOrdered();

    /**
     * Gets the total amount refunded amount for the order.
     *
     * @return float Total amount refunded.
     */
    public function getTotalRefunded();

    /**
     * Gets the updated-at timestamp for the order.
     *
     * @return string Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the weight for the order.
     *
     * @return float Weight.
     */
    public function getWeight();

    /**
     * Gets the X-Forwarded-For HTTP header field for the order.
     * 
     * This field identifies the originating IP address of a client 
     * connecting to a web server through an HTTP proxy or load balancer.
     *
     * @return string X-Forwarded-For field value.
     */
    public function getXForwardedFor();

    /**
     * Gets items for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[] Array of items.
     */
    public function getItems();

    /**
     * Gets the billing address, if any, for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null Billing address. Otherwise, null.
     */
    public function getBillingAddress();

    /**
     * Gets the shipping address, if any, for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null Shipping address. Otherwise, null.
     */
    public function getShippingAddress();

    /**
     * Gets the payments for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface[] Array of payments.
     */
    public function getPayments();

    /**
     * Gets addresses for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface[] Array of addresses.
     */
    public function getAddresses();

    /**
     * Gets status histories for the order.
     *
     * @return \Magento\Sales\Api\Data\OrderStatusHistoryInterface[] Array of status histories.
     */
    public function getStatusHistories();
}
