<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObject as DataObject;

/**
 * Class Order
 */
class Order extends DataObject
{
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
    /**
     * Returns adjustment_negative
     *
     * @return float
     */
    public function getAdjustmentNegative()
    {
        return $this->_get(self::ADJUSTMENT_NEGATIVE);
    }

    /**
     * Returns adjustment_positive
     *
     * @return float
     */
    public function getAdjustmentPositive()
    {
        return $this->_get(self::ADJUSTMENT_POSITIVE);
    }

    /**
     * Returns applied_rule_ids
     *
     * @return string
     */
    public function getAppliedRuleIds()
    {
        return $this->_get(self::APPLIED_RULE_IDS);
    }

    /**
     * Returns base_adjustment_negative
     *
     * @return float
     */
    public function getBaseAdjustmentNegative()
    {
        return $this->_get(self::BASE_ADJUSTMENT_NEGATIVE);
    }

    /**
     * Returns base_adjustment_positive
     *
     * @return float
     */
    public function getBaseAdjustmentPositive()
    {
        return $this->_get(self::BASE_ADJUSTMENT_POSITIVE);
    }

    /**
     * Returns base_currency_code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_get(self::BASE_CURRENCY_CODE);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount()
    {
        return $this->_get(self::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_discount_canceled
     *
     * @return float
     */
    public function getBaseDiscountCanceled()
    {
        return $this->_get(self::BASE_DISCOUNT_CANCELED);
    }

    /**
     * Returns base_discount_invoiced
     *
     * @return float
     */
    public function getBaseDiscountInvoiced()
    {
        return $this->_get(self::BASE_DISCOUNT_INVOICED);
    }

    /**
     * Returns base_discount_refunded
     *
     * @return float
     */
    public function getBaseDiscountRefunded()
    {
        return $this->_get(self::BASE_DISCOUNT_REFUNDED);
    }

    /**
     * Returns base_grand_total
     *
     * @return float
     */
    public function getBaseGrandTotal()
    {
        return $this->_get(self::BASE_GRAND_TOTAL);
    }

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount()
    {
        return $this->_get(self::BASE_HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns base_hidden_tax_invoiced
     *
     * @return float
     */
    public function getBaseHiddenTaxInvoiced()
    {
        return $this->_get(self::BASE_HIDDEN_TAX_INVOICED);
    }

    /**
     * Returns base_hidden_tax_refunded
     *
     * @return float
     */
    public function getBaseHiddenTaxRefunded()
    {
        return $this->_get(self::BASE_HIDDEN_TAX_REFUNDED);
    }

    /**
     * Returns base_shipping_amount
     *
     * @return float
     */
    public function getBaseShippingAmount()
    {
        return $this->_get(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Returns base_shipping_canceled
     *
     * @return float
     */
    public function getBaseShippingCanceled()
    {
        return $this->_get(self::BASE_SHIPPING_CANCELED);
    }

    /**
     * Returns base_shipping_discount_amount
     *
     * @return float
     */
    public function getBaseShippingDiscountAmount()
    {
        return $this->_get(self::BASE_SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_shipping_hidden_tax_amnt
     *
     * @return float
     */
    public function getBaseShippingHiddenTaxAmnt()
    {
        return $this->_get(self::BASE_SHIPPING_HIDDEN_TAX_AMNT);
    }

    /**
     * Returns base_shipping_incl_tax
     *
     * @return float
     */
    public function getBaseShippingInclTax()
    {
        return $this->_get(self::BASE_SHIPPING_INCL_TAX);
    }

    /**
     * Returns base_shipping_invoiced
     *
     * @return float
     */
    public function getBaseShippingInvoiced()
    {
        return $this->_get(self::BASE_SHIPPING_INVOICED);
    }

    /**
     * Returns base_shipping_refunded
     *
     * @return float
     */
    public function getBaseShippingRefunded()
    {
        return $this->_get(self::BASE_SHIPPING_REFUNDED);
    }

    /**
     * Returns base_shipping_tax_amount
     *
     * @return float
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->_get(self::BASE_SHIPPING_TAX_AMOUNT);
    }

    /**
     * Returns base_shipping_tax_refunded
     *
     * @return float
     */
    public function getBaseShippingTaxRefunded()
    {
        return $this->_get(self::BASE_SHIPPING_TAX_REFUNDED);
    }

    /**
     * Returns base_subtotal
     *
     * @return float
     */
    public function getBaseSubtotal()
    {
        return $this->_get(self::BASE_SUBTOTAL);
    }

    /**
     * Returns base_subtotal_canceled
     *
     * @return float
     */
    public function getBaseSubtotalCanceled()
    {
        return $this->_get(self::BASE_SUBTOTAL_CANCELED);
    }

    /**
     * Returns base_subtotal_incl_tax
     *
     * @return float
     */
    public function getBaseSubtotalInclTax()
    {
        return $this->_get(self::BASE_SUBTOTAL_INCL_TAX);
    }

    /**
     * Returns base_subtotal_invoiced
     *
     * @return float
     */
    public function getBaseSubtotalInvoiced()
    {
        return $this->_get(self::BASE_SUBTOTAL_INVOICED);
    }

    /**
     * Returns base_subtotal_refunded
     *
     * @return float
     */
    public function getBaseSubtotalRefunded()
    {
        return $this->_get(self::BASE_SUBTOTAL_REFUNDED);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount()
    {
        return $this->_get(self::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_tax_canceled
     *
     * @return float
     */
    public function getBaseTaxCanceled()
    {
        return $this->_get(self::BASE_TAX_CANCELED);
    }

    /**
     * Returns base_tax_invoiced
     *
     * @return float
     */
    public function getBaseTaxInvoiced()
    {
        return $this->_get(self::BASE_TAX_INVOICED);
    }

    /**
     * Returns base_tax_refunded
     *
     * @return float
     */
    public function getBaseTaxRefunded()
    {
        return $this->_get(self::BASE_TAX_REFUNDED);
    }

    /**
     * Returns base_total_canceled
     *
     * @return float
     */
    public function getBaseTotalCanceled()
    {
        return $this->_get(self::BASE_TOTAL_CANCELED);
    }

    /**
     * Returns base_total_due
     *
     * @return float
     */
    public function getBaseTotalDue()
    {
        return $this->_get(self::BASE_TOTAL_DUE);
    }

    /**
     * Returns base_total_invoiced
     *
     * @return float
     */
    public function getBaseTotalInvoiced()
    {
        return $this->_get(self::BASE_TOTAL_INVOICED);
    }

    /**
     * Returns base_total_invoiced_cost
     *
     * @return float
     */
    public function getBaseTotalInvoicedCost()
    {
        return $this->_get(self::BASE_TOTAL_INVOICED_COST);
    }

    /**
     * Returns base_total_offline_refunded
     *
     * @return float
     */
    public function getBaseTotalOfflineRefunded()
    {
        return $this->_get(self::BASE_TOTAL_OFFLINE_REFUNDED);
    }

    /**
     * Returns base_total_online_refunded
     *
     * @return float
     */
    public function getBaseTotalOnlineRefunded()
    {
        return $this->_get(self::BASE_TOTAL_ONLINE_REFUNDED);
    }

    /**
     * Returns base_total_paid
     *
     * @return float
     */
    public function getBaseTotalPaid()
    {
        return $this->_get(self::BASE_TOTAL_PAID);
    }

    /**
     * Returns base_total_qty_ordered
     *
     * @return float
     */
    public function getBaseTotalQtyOrdered()
    {
        return $this->_get(self::BASE_TOTAL_QTY_ORDERED);
    }

    /**
     * Returns base_total_refunded
     *
     * @return float
     */
    public function getBaseTotalRefunded()
    {
        return $this->_get(self::BASE_TOTAL_REFUNDED);
    }

    /**
     * Returns base_to_global_rate
     *
     * @return float
     */
    public function getBaseToGlobalRate()
    {
        return $this->_get(self::BASE_TO_GLOBAL_RATE);
    }

    /**
     * Returns base_to_order_rate
     *
     * @return float
     */
    public function getBaseToOrderRate()
    {
        return $this->_get(self::BASE_TO_ORDER_RATE);
    }

    /**
     * Returns billing_address_id
     *
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->_get(self::BILLING_ADDRESS_ID);
    }

    /**
     * Returns can_ship_partially
     *
     * @return int
     */
    public function getCanShipPartially()
    {
        return $this->_get(self::CAN_SHIP_PARTIALLY);
    }

    /**
     * Returns can_ship_partially_item
     *
     * @return int
     */
    public function getCanShipPartiallyItem()
    {
        return $this->_get(self::CAN_SHIP_PARTIALLY_ITEM);
    }

    /**
     * Returns coupon_code
     *
     * @return string
     */
    public function getCouponCode()
    {
        return $this->_get(self::COUPON_CODE);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Returns customer_dob
     *
     * @return string
     */
    public function getCustomerDob()
    {
        return $this->_get(self::CUSTOMER_DOB);
    }

    /**
     * Returns customer_email
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->_get(self::CUSTOMER_EMAIL);
    }

    /**
     * Returns customer_firstname
     *
     * @return string
     */
    public function getCustomerFirstname()
    {
        return $this->_get(self::CUSTOMER_FIRSTNAME);
    }

    /**
     * Returns customer_gender
     *
     * @return int
     */
    public function getCustomerGender()
    {
        return $this->_get(self::CUSTOMER_GENDER);
    }

    /**
     * Returns customer_group_id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->_get(self::CUSTOMER_GROUP_ID);
    }

    /**
     * Returns customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Returns customer_is_guest
     *
     * @return int
     */
    public function getCustomerIsGuest()
    {
        return $this->_get(self::CUSTOMER_IS_GUEST);
    }

    /**
     * Returns customer_lastname
     *
     * @return string
     */
    public function getCustomerLastname()
    {
        return $this->_get(self::CUSTOMER_LASTNAME);
    }

    /**
     * Returns customer_middlename
     *
     * @return string
     */
    public function getCustomerMiddlename()
    {
        return $this->_get(self::CUSTOMER_MIDDLENAME);
    }

    /**
     * Returns customer_note
     *
     * @return string
     */
    public function getCustomerNote()
    {
        return $this->_get(self::CUSTOMER_NOTE);
    }

    /**
     * Returns customer_note_notify
     *
     * @return int
     */
    public function getCustomerNoteNotify()
    {
        return $this->_get(self::CUSTOMER_NOTE_NOTIFY);
    }

    /**
     * Returns customer_prefix
     *
     * @return string
     */
    public function getCustomerPrefix()
    {
        return $this->_get(self::CUSTOMER_PREFIX);
    }

    /**
     * Returns customer_suffix
     *
     * @return string
     */
    public function getCustomerSuffix()
    {
        return $this->_get(self::CUSTOMER_SUFFIX);
    }

    /**
     * Returns customer_taxvat
     *
     * @return string
     */
    public function getCustomerTaxvat()
    {
        return $this->_get(self::CUSTOMER_TAXVAT);
    }

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->_get(self::DISCOUNT_AMOUNT);
    }

    /**
     * Returns discount_canceled
     *
     * @return float
     */
    public function getDiscountCanceled()
    {
        return $this->_get(self::DISCOUNT_CANCELED);
    }

    /**
     * Returns discount_description
     *
     * @return string
     */
    public function getDiscountDescription()
    {
        return $this->_get(self::DISCOUNT_DESCRIPTION);
    }

    /**
     * Returns discount_invoiced
     *
     * @return float
     */
    public function getDiscountInvoiced()
    {
        return $this->_get(self::DISCOUNT_INVOICED);
    }

    /**
     * Returns discount_refunded
     *
     * @return float
     */
    public function getDiscountRefunded()
    {
        return $this->_get(self::DISCOUNT_REFUNDED);
    }

    /**
     * Returns edit_increment
     *
     * @return int
     */
    public function getEditIncrement()
    {
        return $this->_get(self::EDIT_INCREMENT);
    }

    /**
     * Returns email_sent
     *
     * @return int
     */
    public function getEmailSent()
    {
        return $this->_get(self::EMAIL_SENT);
    }

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Returns ext_customer_id
     *
     * @return string
     */
    public function getExtCustomerId()
    {
        return $this->_get(self::EXT_CUSTOMER_ID);
    }

    /**
     * Returns ext_order_id
     *
     * @return string
     */
    public function getExtOrderId()
    {
        return $this->_get(self::EXT_ORDER_ID);
    }

    /**
     * Returns forced_shipment_with_invoice
     *
     * @return int
     */
    public function getForcedShipmentWithInvoice()
    {
        return $this->_get(self::FORCED_SHIPMENT_WITH_INVOICE);
    }

    /**
     * Returns global_currency_code
     *
     * @return string
     */
    public function getGlobalCurrencyCode()
    {
        return $this->_get(self::GLOBAL_CURRENCY_CODE);
    }

    /**
     * Returns grand_total
     *
     * @return float
     */
    public function getGrandTotal()
    {
        return $this->_get(self::GRAND_TOTAL);
    }

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount()
    {
        return $this->_get(self::HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns hidden_tax_invoiced
     *
     * @return float
     */
    public function getHiddenTaxInvoiced()
    {
        return $this->_get(self::HIDDEN_TAX_INVOICED);
    }

    /**
     * Returns hidden_tax_refunded
     *
     * @return float
     */
    public function getHiddenTaxRefunded()
    {
        return $this->_get(self::HIDDEN_TAX_REFUNDED);
    }

    /**
     * Returns hold_before_state
     *
     * @return string
     */
    public function getHoldBeforeState()
    {
        return $this->_get(self::HOLD_BEFORE_STATE);
    }

    /**
     * Returns hold_before_status
     *
     * @return string
     */
    public function getHoldBeforeStatus()
    {
        return $this->_get(self::HOLD_BEFORE_STATUS);
    }

    /**
     * Returns increment_id
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->_get(self::INCREMENT_ID);
    }

    /**
     * Returns is_virtual
     *
     * @return int
     */
    public function getIsVirtual()
    {
        return $this->_get(self::IS_VIRTUAL);
    }

    /**
     * Returns order_currency_code
     *
     * @return string
     */
    public function getOrderCurrencyCode()
    {
        return $this->_get(self::ORDER_CURRENCY_CODE);
    }

    /**
     * Returns original_increment_id
     *
     * @return string
     */
    public function getOriginalIncrementId()
    {
        return $this->_get(self::ORIGINAL_INCREMENT_ID);
    }

    /**
     * Returns payment_authorization_amount
     *
     * @return float
     */
    public function getPaymentAuthorizationAmount()
    {
        return $this->_get(self::PAYMENT_AUTHORIZATION_AMOUNT);
    }

    /**
     * Returns payment_auth_expiration
     *
     * @return int
     */
    public function getPaymentAuthExpiration()
    {
        return $this->_get(self::PAYMENT_AUTH_EXPIRATION);
    }

    /**
     * Returns protect_code
     *
     * @return string
     */
    public function getProtectCode()
    {
        return $this->_get(self::PROTECT_CODE);
    }

    /**
     * Returns quote_address_id
     *
     * @return int
     */
    public function getQuoteAddressId()
    {
        return $this->_get(self::QUOTE_ADDRESS_ID);
    }

    /**
     * Returns quote_id
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->_get(self::QUOTE_ID);
    }

    /**
     * Returns relation_child_id
     *
     * @return string
     */
    public function getRelationChildId()
    {
        return $this->_get(self::RELATION_CHILD_ID);
    }

    /**
     * Returns relation_child_real_id
     *
     * @return string
     */
    public function getRelationChildRealId()
    {
        return $this->_get(self::RELATION_CHILD_REAL_ID);
    }

    /**
     * Returns relation_parent_id
     *
     * @return string
     */
    public function getRelationParentId()
    {
        return $this->_get(self::RELATION_PARENT_ID);
    }

    /**
     * Returns relation_parent_real_id
     *
     * @return string
     */
    public function getRelationParentRealId()
    {
        return $this->_get(self::RELATION_PARENT_REAL_ID);
    }

    /**
     * Returns remote_ip
     *
     * @return string
     */
    public function getRemoteIp()
    {
        return $this->_get(self::REMOTE_IP);
    }

    /**
     * Returns shipping_address_id
     *
     * @return int
     */
    public function getShippingAddressId()
    {
        return $this->_get(self::SHIPPING_ADDRESS_ID);
    }

    /**
     * Returns shipping_amount
     *
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->_get(self::SHIPPING_AMOUNT);
    }

    /**
     * Returns shipping_canceled
     *
     * @return float
     */
    public function getShippingCanceled()
    {
        return $this->_get(self::SHIPPING_CANCELED);
    }

    /**
     * Returns shipping_description
     *
     * @return string
     */
    public function getShippingDescription()
    {
        return $this->_get(self::SHIPPING_DESCRIPTION);
    }

    /**
     * Returns shipping_discount_amount
     *
     * @return float
     */
    public function getShippingDiscountAmount()
    {
        return $this->_get(self::SHIPPING_DISCOUNT_AMOUNT);
    }

    /**
     * Returns shipping_hidden_tax_amount
     *
     * @return float
     */
    public function getShippingHiddenTaxAmount()
    {
        return $this->_get(self::SHIPPING_HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns shipping_incl_tax
     *
     * @return float
     */
    public function getShippingInclTax()
    {
        return $this->_get(self::SHIPPING_INCL_TAX);
    }

    /**
     * Returns shipping_invoiced
     *
     * @return float
     */
    public function getShippingInvoiced()
    {
        return $this->_get(self::SHIPPING_INVOICED);
    }

    /**
     * Returns shipping_method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->_get(self::SHIPPING_METHOD);
    }

    /**
     * Returns shipping_refunded
     *
     * @return float
     */
    public function getShippingRefunded()
    {
        return $this->_get(self::SHIPPING_REFUNDED);
    }

    /**
     * Returns shipping_tax_amount
     *
     * @return float
     */
    public function getShippingTaxAmount()
    {
        return $this->_get(self::SHIPPING_TAX_AMOUNT);
    }

    /**
     * Returns shipping_tax_refunded
     *
     * @return float
     */
    public function getShippingTaxRefunded()
    {
        return $this->_get(self::SHIPPING_TAX_REFUNDED);
    }

    /**
     * Returns state
     *
     * @return string
     */
    public function getState()
    {
        return $this->_get(self::STATE);
    }

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Returns store_currency_code
     *
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        return $this->_get(self::STORE_CURRENCY_CODE);
    }

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Returns store_name
     *
     * @return string
     */
    public function getStoreName()
    {
        return $this->_get(self::STORE_NAME);
    }

    /**
     * Returns store_to_base_rate
     *
     * @return float
     */
    public function getStoreToBaseRate()
    {
        return $this->_get(self::STORE_TO_BASE_RATE);
    }

    /**
     * Returns store_to_order_rate
     *
     * @return float
     */
    public function getStoreToOrderRate()
    {
        return $this->_get(self::STORE_TO_ORDER_RATE);
    }

    /**
     * Returns subtotal
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->_get(self::SUBTOTAL);
    }

    /**
     * Returns subtotal_canceled
     *
     * @return float
     */
    public function getSubtotalCanceled()
    {
        return $this->_get(self::SUBTOTAL_CANCELED);
    }

    /**
     * Returns subtotal_incl_tax
     *
     * @return float
     */
    public function getSubtotalInclTax()
    {
        return $this->_get(self::SUBTOTAL_INCL_TAX);
    }

    /**
     * Returns subtotal_invoiced
     *
     * @return float
     */
    public function getSubtotalInvoiced()
    {
        return $this->_get(self::SUBTOTAL_INVOICED);
    }

    /**
     * Returns subtotal_refunded
     *
     * @return float
     */
    public function getSubtotalRefunded()
    {
        return $this->_get(self::SUBTOTAL_REFUNDED);
    }

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->_get(self::TAX_AMOUNT);
    }

    /**
     * Returns tax_canceled
     *
     * @return float
     */
    public function getTaxCanceled()
    {
        return $this->_get(self::TAX_CANCELED);
    }

    /**
     * Returns tax_invoiced
     *
     * @return float
     */
    public function getTaxInvoiced()
    {
        return $this->_get(self::TAX_INVOICED);
    }

    /**
     * Returns tax_refunded
     *
     * @return float
     */
    public function getTaxRefunded()
    {
        return $this->_get(self::TAX_REFUNDED);
    }

    /**
     * Returns total_canceled
     *
     * @return float
     */
    public function getTotalCanceled()
    {
        return $this->_get(self::TOTAL_CANCELED);
    }

    /**
     * Returns total_due
     *
     * @return float
     */
    public function getTotalDue()
    {
        return $this->_get(self::TOTAL_DUE);
    }

    /**
     * Returns total_invoiced
     *
     * @return float
     */
    public function getTotalInvoiced()
    {
        return $this->_get(self::TOTAL_INVOICED);
    }

    /**
     * Returns total_item_count
     *
     * @return int
     */
    public function getTotalItemCount()
    {
        return $this->_get(self::TOTAL_ITEM_COUNT);
    }

    /**
     * Returns total_offline_refunded
     *
     * @return float
     */
    public function getTotalOfflineRefunded()
    {
        return $this->_get(self::TOTAL_OFFLINE_REFUNDED);
    }

    /**
     * Returns total_online_refunded
     *
     * @return float
     */
    public function getTotalOnlineRefunded()
    {
        return $this->_get(self::TOTAL_ONLINE_REFUNDED);
    }

    /**
     * Returns total_paid
     *
     * @return float
     */
    public function getTotalPaid()
    {
        return $this->_get(self::TOTAL_PAID);
    }

    /**
     * Returns total_qty_ordered
     *
     * @return float
     */
    public function getTotalQtyOrdered()
    {
        return $this->_get(self::TOTAL_QTY_ORDERED);
    }

    /**
     * Returns total_refunded
     *
     * @return float
     */
    public function getTotalRefunded()
    {
        return $this->_get(self::TOTAL_REFUNDED);
    }

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->_get(self::WEIGHT);
    }

    /**
     * Returns x_forwarded_for
     *
     * @return string
     */
    public function getXForwardedFor()
    {
        return $this->_get(self::X_FORWARDED_FOR);
    }

    /**
     * @return \Magento\Sales\Service\V1\Data\OrderItem[]
     */
    public function getItems()
    {
        return $this->_get(self::ITEMS);
    }

    /**
     * @return \Magento\Sales\Service\V1\Data\OrderAddress
     */
    public function getBillingAddress()
    {
        return $this->_get(self::BILLING_ADDRESS);
    }

    /**
     * @return \Magento\Sales\Service\V1\Data\OrderAddress
     */
    public function getShippingAddress()
    {
        return $this->_get(self::SHIPPING_ADDRESS);
    }

    /**
     * @return \Magento\Sales\Service\V1\Data\OrderPayment[]
     */
    public function getPayments()
    {
        return $this->_get(self::PAYMENTS);
    }
}
