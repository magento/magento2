<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface OrderInterface
 */
interface OrderInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const STATE = 'state';
    const STATUS = 'status';
    const COUPON_CODE = 'coupon_code';
    const PROTECT_CODE = 'protect_code';
    const SHIPPING_DESCRIPTION = 'shipping_description';
    const IS_VIRTUAL = 'is_virtual';
    const STORE_ID = 'store_id';
    const CUSTOMER_ID = 'customer_id';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const BASE_DISCOUNT_CANCELED = 'base_discount_canceled';
    const BASE_DISCOUNT_INVOICED = 'base_discount_invoiced';
    const BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    const BASE_SHIPPING_CANCELED = 'base_shipping_canceled';
    const BASE_SHIPPING_INVOICED = 'base_shipping_invoiced';
    const BASE_SHIPPING_REFUNDED = 'base_shipping_refunded';
    const BASE_SHIPPING_TAX_AMOUNT = 'base_shipping_tax_amount';
    const BASE_SHIPPING_TAX_REFUNDED = 'base_shipping_tax_refunded';
    const BASE_SUBTOTAL = 'base_subtotal';
    const BASE_SUBTOTAL_CANCELED = 'base_subtotal_canceled';
    const BASE_SUBTOTAL_INVOICED = 'base_subtotal_invoiced';
    const BASE_SUBTOTAL_REFUNDED = 'base_subtotal_refunded';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const BASE_TAX_CANCELED = 'base_tax_canceled';
    const BASE_TAX_INVOICED = 'base_tax_invoiced';
    const BASE_TAX_REFUNDED = 'base_tax_refunded';
    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    const BASE_TO_ORDER_RATE = 'base_to_order_rate';
    const BASE_TOTAL_CANCELED = 'base_total_canceled';
    const BASE_TOTAL_INVOICED = 'base_total_invoiced';
    const BASE_TOTAL_INVOICED_COST = 'base_total_invoiced_cost';
    const BASE_TOTAL_OFFLINE_REFUNDED = 'base_total_offline_refunded';
    const BASE_TOTAL_ONLINE_REFUNDED = 'base_total_online_refunded';
    const BASE_TOTAL_PAID = 'base_total_paid';
    const BASE_TOTAL_QTY_ORDERED = 'base_total_qty_ordered';
    const BASE_TOTAL_REFUNDED = 'base_total_refunded';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const DISCOUNT_CANCELED = 'discount_canceled';
    const DISCOUNT_INVOICED = 'discount_invoiced';
    const DISCOUNT_REFUNDED = 'discount_refunded';
    const GRAND_TOTAL = 'grand_total';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const SHIPPING_CANCELED = 'shipping_canceled';
    const SHIPPING_INVOICED = 'shipping_invoiced';
    const SHIPPING_REFUNDED = 'shipping_refunded';
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    const SHIPPING_TAX_REFUNDED = 'shipping_tax_refunded';
    const STORE_TO_BASE_RATE = 'store_to_base_rate';
    const STORE_TO_ORDER_RATE = 'store_to_order_rate';
    const SUBTOTAL = 'subtotal';
    const SUBTOTAL_CANCELED = 'subtotal_canceled';
    const SUBTOTAL_INVOICED = 'subtotal_invoiced';
    const SUBTOTAL_REFUNDED = 'subtotal_refunded';
    const TAX_AMOUNT = 'tax_amount';
    const TAX_CANCELED = 'tax_canceled';
    const TAX_INVOICED = 'tax_invoiced';
    const TAX_REFUNDED = 'tax_refunded';
    const TOTAL_CANCELED = 'total_canceled';
    const TOTAL_INVOICED = 'total_invoiced';
    const TOTAL_OFFLINE_REFUNDED = 'total_offline_refunded';
    const TOTAL_ONLINE_REFUNDED = 'total_online_refunded';
    const TOTAL_PAID = 'total_paid';
    const TOTAL_QTY_ORDERED = 'total_qty_ordered';
    const TOTAL_REFUNDED = 'total_refunded';
    const CAN_SHIP_PARTIALLY = 'can_ship_partially';
    const CAN_SHIP_PARTIALLY_ITEM = 'can_ship_partially_item';
    const CUSTOMER_IS_GUEST = 'customer_is_guest';
    const CUSTOMER_NOTE_NOTIFY = 'customer_note_notify';
    const BILLING_ADDRESS_ID = 'billing_address_id';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const EDIT_INCREMENT = 'edit_increment';
    const EMAIL_SENT = 'email_sent';
    const FORCED_SHIPMENT_WITH_INVOICE = 'forced_shipment_with_invoice';
    const PAYMENT_AUTH_EXPIRATION = 'payment_auth_expiration';
    const QUOTE_ADDRESS_ID = 'quote_address_id';
    const QUOTE_ID = 'quote_id';
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    const ADJUSTMENT_NEGATIVE = 'adjustment_negative';
    const ADJUSTMENT_POSITIVE = 'adjustment_positive';
    const BASE_ADJUSTMENT_NEGATIVE = 'base_adjustment_negative';
    const BASE_ADJUSTMENT_POSITIVE = 'base_adjustment_positive';
    const BASE_SHIPPING_DISCOUNT_AMOUNT = 'base_shipping_discount_amount';
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    const BASE_TOTAL_DUE = 'base_total_due';
    const PAYMENT_AUTHORIZATION_AMOUNT = 'payment_authorization_amount';
    const SHIPPING_DISCOUNT_AMOUNT = 'shipping_discount_amount';
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    const TOTAL_DUE = 'total_due';
    const WEIGHT = 'weight';
    const CUSTOMER_DOB = 'customer_dob';
    const INCREMENT_ID = 'increment_id';
    const APPLIED_RULE_IDS = 'applied_rule_ids';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_FIRSTNAME = 'customer_firstname';
    const CUSTOMER_LASTNAME = 'customer_lastname';
    const CUSTOMER_MIDDLENAME = 'customer_middlename';
    const CUSTOMER_PREFIX = 'customer_prefix';
    const CUSTOMER_SUFFIX = 'customer_suffix';
    const CUSTOMER_TAXVAT = 'customer_taxvat';
    const DISCOUNT_DESCRIPTION = 'discount_description';
    const EXT_CUSTOMER_ID = 'ext_customer_id';
    const EXT_ORDER_ID = 'ext_order_id';
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
    const HOLD_BEFORE_STATE = 'hold_before_state';
    const HOLD_BEFORE_STATUS = 'hold_before_status';
    const ORDER_CURRENCY_CODE = 'order_currency_code';
    const ORIGINAL_INCREMENT_ID = 'original_increment_id';
    const RELATION_CHILD_ID = 'relation_child_id';
    const RELATION_CHILD_REAL_ID = 'relation_child_real_id';
    const RELATION_PARENT_ID = 'relation_parent_id';
    const RELATION_PARENT_REAL_ID = 'relation_parent_real_id';
    const REMOTE_IP = 'remote_ip';
    const SHIPPING_METHOD = 'shipping_method';
    const STORE_CURRENCY_CODE = 'store_currency_code';
    const STORE_NAME = 'store_name';
    const X_FORWARDED_FOR = 'x_forwarded_for';
    const CUSTOMER_NOTE = 'customer_note';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const TOTAL_ITEM_COUNT = 'total_item_count';
    const CUSTOMER_GENDER = 'customer_gender';
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';
    const SHIPPING_HIDDEN_TAX_AMOUNT = 'shipping_hidden_tax_amount';
    const BASE_SHIPPING_HIDDEN_TAX_AMNT = 'base_shipping_hidden_tax_amnt';
    const HIDDEN_TAX_INVOICED = 'hidden_tax_invoiced';
    const BASE_HIDDEN_TAX_INVOICED = 'base_hidden_tax_invoiced';
    const HIDDEN_TAX_REFUNDED = 'hidden_tax_refunded';
    const BASE_HIDDEN_TAX_REFUNDED = 'base_hidden_tax_refunded';
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    const ITEMS = 'items';
    const BILLING_ADDRESS = 'billing_address';
    const SHIPPING_ADDRESS = 'shipping_address';
    const PAYMENTS = 'payments';
    const ADDRESSES = 'addresses';
    const STATUS_HISTORIES = 'status_histories';

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
     * Returns applied_rule_ids
     *
     * @return string
     */
    public function getAppliedRuleIds();

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
     * Returns base_discount_canceled
     *
     * @return float
     */
    public function getBaseDiscountCanceled();

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
     * Returns base_shipping_amount
     *
     * @return float
     */
    public function getBaseShippingAmount();

    /**
     * Returns base_shipping_canceled
     *
     * @return float
     */
    public function getBaseShippingCanceled();

    /**
     * Returns base_shipping_discount_amount
     *
     * @return float
     */
    public function getBaseShippingDiscountAmount();

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
     * Returns base_shipping_invoiced
     *
     * @return float
     */
    public function getBaseShippingInvoiced();

    /**
     * Returns base_shipping_refunded
     *
     * @return float
     */
    public function getBaseShippingRefunded();

    /**
     * Returns base_shipping_tax_amount
     *
     * @return float
     */
    public function getBaseShippingTaxAmount();

    /**
     * Returns base_shipping_tax_refunded
     *
     * @return float
     */
    public function getBaseShippingTaxRefunded();

    /**
     * Returns base_subtotal
     *
     * @return float
     */
    public function getBaseSubtotal();

    /**
     * Returns base_subtotal_canceled
     *
     * @return float
     */
    public function getBaseSubtotalCanceled();

    /**
     * Returns base_subtotal_incl_tax
     *
     * @return float
     */
    public function getBaseSubtotalInclTax();

    /**
     * Returns base_subtotal_invoiced
     *
     * @return float
     */
    public function getBaseSubtotalInvoiced();

    /**
     * Returns base_subtotal_refunded
     *
     * @return float
     */
    public function getBaseSubtotalRefunded();

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount();

    /**
     * Returns base_tax_canceled
     *
     * @return float
     */
    public function getBaseTaxCanceled();

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
     * Returns base_total_canceled
     *
     * @return float
     */
    public function getBaseTotalCanceled();

    /**
     * Returns base_total_due
     *
     * @return float
     */
    public function getBaseTotalDue();

    /**
     * Returns base_total_invoiced
     *
     * @return float
     */
    public function getBaseTotalInvoiced();

    /**
     * Returns base_total_invoiced_cost
     *
     * @return float
     */
    public function getBaseTotalInvoicedCost();

    /**
     * Returns base_total_offline_refunded
     *
     * @return float
     */
    public function getBaseTotalOfflineRefunded();

    /**
     * Returns base_total_online_refunded
     *
     * @return float
     */
    public function getBaseTotalOnlineRefunded();

    /**
     * Returns base_total_paid
     *
     * @return float
     */
    public function getBaseTotalPaid();

    /**
     * Returns base_total_qty_ordered
     *
     * @return float
     */
    public function getBaseTotalQtyOrdered();

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
     * Returns can_ship_partially
     *
     * @return int
     */
    public function getCanShipPartially();

    /**
     * Returns can_ship_partially_item
     *
     * @return int
     */
    public function getCanShipPartiallyItem();

    /**
     * Returns coupon_code
     *
     * @return string
     */
    public function getCouponCode();

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns customer_dob
     *
     * @return string
     */
    public function getCustomerDob();

    /**
     * Returns customer_email
     *
     * @return string
     */
    public function getCustomerEmail();

    /**
     * Returns customer_firstname
     *
     * @return string
     */
    public function getCustomerFirstname();

    /**
     * Returns customer_gender
     *
     * @return int
     */
    public function getCustomerGender();

    /**
     * Returns customer_group_id
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Returns customer_id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Returns customer_is_guest
     *
     * @return int
     */
    public function getCustomerIsGuest();

    /**
     * Returns customer_lastname
     *
     * @return string
     */
    public function getCustomerLastname();

    /**
     * Returns customer_middlename
     *
     * @return string
     */
    public function getCustomerMiddlename();

    /**
     * Returns customer_note
     *
     * @return string
     */
    public function getCustomerNote();

    /**
     * Returns customer_note_notify
     *
     * @return int
     */
    public function getCustomerNoteNotify();

    /**
     * Returns customer_prefix
     *
     * @return string
     */
    public function getCustomerPrefix();

    /**
     * Returns customer_suffix
     *
     * @return string
     */
    public function getCustomerSuffix();

    /**
     * Returns customer_taxvat
     *
     * @return string
     */
    public function getCustomerTaxvat();

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Returns discount_canceled
     *
     * @return float
     */
    public function getDiscountCanceled();

    /**
     * Returns discount_description
     *
     * @return string
     */
    public function getDiscountDescription();

    /**
     * Returns discount_invoiced
     *
     * @return float
     */
    public function getDiscountInvoiced();

    /**
     * Returns discount_refunded
     *
     * @return float
     */
    public function getDiscountRefunded();

    /**
     * Returns edit_increment
     *
     * @return int
     */
    public function getEditIncrement();

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
     * Returns ext_customer_id
     *
     * @return string
     */
    public function getExtCustomerId();

    /**
     * Returns ext_order_id
     *
     * @return string
     */
    public function getExtOrderId();

    /**
     * Returns forced_shipment_with_invoice
     *
     * @return int
     */
    public function getForcedShipmentWithInvoice();

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
     * Returns hold_before_state
     *
     * @return string
     */
    public function getHoldBeforeState();

    /**
     * Returns hold_before_status
     *
     * @return string
     */
    public function getHoldBeforeStatus();

    /**
     * Returns increment_id
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Returns is_virtual
     *
     * @return int
     */
    public function getIsVirtual();

    /**
     * Returns order_currency_code
     *
     * @return string
     */
    public function getOrderCurrencyCode();

    /**
     * Returns original_increment_id
     *
     * @return string
     */
    public function getOriginalIncrementId();

    /**
     * Returns payment_authorization_amount
     *
     * @return float
     */
    public function getPaymentAuthorizationAmount();

    /**
     * Returns payment_auth_expiration
     *
     * @return int
     */
    public function getPaymentAuthExpiration();

    /**
     * Returns protect_code
     *
     * @return string
     */
    public function getProtectCode();

    /**
     * Returns quote_address_id
     *
     * @return int
     */
    public function getQuoteAddressId();

    /**
     * Returns quote_id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Returns relation_child_id
     *
     * @return string
     */
    public function getRelationChildId();

    /**
     * Returns relation_child_real_id
     *
     * @return string
     */
    public function getRelationChildRealId();

    /**
     * Returns relation_parent_id
     *
     * @return string
     */
    public function getRelationParentId();

    /**
     * Returns relation_parent_real_id
     *
     * @return string
     */
    public function getRelationParentRealId();

    /**
     * Returns remote_ip
     *
     * @return string
     */
    public function getRemoteIp();

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
     * Returns shipping_canceled
     *
     * @return float
     */
    public function getShippingCanceled();

    /**
     * Returns shipping_description
     *
     * @return string
     */
    public function getShippingDescription();

    /**
     * Returns shipping_discount_amount
     *
     * @return float
     */
    public function getShippingDiscountAmount();

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
     * Returns shipping_invoiced
     *
     * @return float
     */
    public function getShippingInvoiced();

    /**
     * Returns shipping_method
     *
     * @return string
     */
    public function getShippingMethod();

    /**
     * Returns shipping_refunded
     *
     * @return float
     */
    public function getShippingRefunded();

    /**
     * Returns shipping_tax_amount
     *
     * @return float
     */
    public function getShippingTaxAmount();

    /**
     * Returns shipping_tax_refunded
     *
     * @return float
     */
    public function getShippingTaxRefunded();

    /**
     * Returns state
     *
     * @return string
     */
    public function getState();

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus();

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
     * Returns store_name
     *
     * @return string
     */
    public function getStoreName();

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
     * Returns subtotal_canceled
     *
     * @return float
     */
    public function getSubtotalCanceled();

    /**
     * Returns subtotal_incl_tax
     *
     * @return float
     */
    public function getSubtotalInclTax();

    /**
     * Returns subtotal_invoiced
     *
     * @return float
     */
    public function getSubtotalInvoiced();

    /**
     * Returns subtotal_refunded
     *
     * @return float
     */
    public function getSubtotalRefunded();

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount();

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
     * Returns tax_refunded
     *
     * @return float
     */
    public function getTaxRefunded();

    /**
     * Returns total_canceled
     *
     * @return float
     */
    public function getTotalCanceled();

    /**
     * Returns total_due
     *
     * @return float
     */
    public function getTotalDue();

    /**
     * Returns total_invoiced
     *
     * @return float
     */
    public function getTotalInvoiced();

    /**
     * Returns total_item_count
     *
     * @return int
     */
    public function getTotalItemCount();

    /**
     * Returns total_offline_refunded
     *
     * @return float
     */
    public function getTotalOfflineRefunded();

    /**
     * Returns total_online_refunded
     *
     * @return float
     */
    public function getTotalOnlineRefunded();

    /**
     * Returns total_paid
     *
     * @return float
     */
    public function getTotalPaid();

    /**
     * Returns total_qty_ordered
     *
     * @return float
     */
    public function getTotalQtyOrdered();

    /**
     * Returns total_refunded
     *
     * @return float
     */
    public function getTotalRefunded();

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight();

    /**
     * Returns x_forwarded_for
     *
     * @return string
     */
    public function getXForwardedFor();

    /**
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getItems();

    /**
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null
     */
    public function getBillingAddress();

    /**
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null
     */
    public function getShippingAddress();

    /**
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface[]
     */
    public function getPayments();

    /**
     * @return \Magento\Sales\Api\Data\OrderAddressInterface[]
     */
    public function getAddresses();

    /**
     * @return \Magento\Sales\Api\Data\OrderStatusHistoryInterface[]
     */
    public function getStatusHistories();
}
