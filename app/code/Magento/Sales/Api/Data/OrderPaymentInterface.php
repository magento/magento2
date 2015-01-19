<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order payment interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 */
interface OrderPaymentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Parent ID.
     */
    const PARENT_ID = 'parent_id';
    /*
     * Base shipping captured.
     */
    const BASE_SHIPPING_CAPTURED = 'base_shipping_captured';
    /*
     * Shipping captured.
     */
    const SHIPPING_CAPTURED = 'shipping_captured';
    /*
     * Amount refunded.
     */
    const AMOUNT_REFUNDED = 'amount_refunded';
    /*
     * Base amount paid.
     */
    const BASE_AMOUNT_PAID = 'base_amount_paid';
    /*
     * Amount canceled.
     */
    const AMOUNT_CANCELED = 'amount_canceled';
    /*
     * Base amount authorized.
     */
    const BASE_AMOUNT_AUTHORIZED = 'base_amount_authorized';
    /*
     * Base amount paid online.
     */
    const BASE_AMOUNT_PAID_ONLINE = 'base_amount_paid_online';
    /*
     * Base amount refunded online.
     */
    const BASE_AMOUNT_REFUNDED_ONLINE = 'base_amount_refunded_online';
    /*
     * Base shipping amount.
     */
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    /*
     * Shipping amount.
     */
    const SHIPPING_AMOUNT = 'shipping_amount';
    /*
     * Amount paid.
     */
    const AMOUNT_PAID = 'amount_paid';
    /*
     * Amount authorized.
     */
    const AMOUNT_AUTHORIZED = 'amount_authorized';
    /*
     * Base amount ordered.
     */
    const BASE_AMOUNT_ORDERED = 'base_amount_ordered';
    /*
     * Base shipping refunded.
     */
    const BASE_SHIPPING_REFUNDED = 'base_shipping_refunded';
    /*
     * Shipping refunded.
     */
    const SHIPPING_REFUNDED = 'shipping_refunded';
    /*
     * Base amount refunded.
     */
    const BASE_AMOUNT_REFUNDED = 'base_amount_refunded';
    /*
     * Amount ordered.
     */
    const AMOUNT_ORDERED = 'amount_ordered';
    /*
     * Base amount canceled.
     */
    const BASE_AMOUNT_CANCELED = 'base_amount_canceled';
    /*
     * Quote payment ID.
     */
    const QUOTE_PAYMENT_ID = 'quote_payment_id';
    /*
     * Additional data.
     */
    const ADDITIONAL_DATA = 'additional_data';
    /*
     * Credit card expiration month.
     */
    const CC_EXP_MONTH = 'cc_exp_month';
    /*
     * Credit card SS start year.
     */
    const CC_SS_START_YEAR = 'cc_ss_start_year';
    /*
     * eCheck bank name.
     */
    const ECHECK_BANK_NAME = 'echeck_bank_name';
    /*
     * Payment method.
     */
    const METHOD = 'method';
    /*
     * Credit card debug request body.
     */
    const CC_DEBUG_REQUEST_BODY = 'cc_debug_request_body';
    /*
     * Credit card secure verify.
     */
    const CC_SECURE_VERIFY = 'cc_secure_verify';
    /*
     * Protection eligibility.
     */
    const PROTECTION_ELIGIBILITY = 'protection_eligibility';
    /*
     * Credit card approval.
     */
    const CC_APPROVAL = 'cc_approval';
    /*
     * Last four digits of credit card number.
     */
    const CC_LAST_4 = 'cc_last_4';
    /*
     * Credit card status description.
     */
    const CC_STATUS_DESCRIPTION = 'cc_status_description';
    /*
     * eCheck type.
     */
    const ECHECK_TYPE = 'echeck_type';
    /*
     * Credit card debug response serialized.
     */
    const CC_DEBUG_RESPONSE_SERIALIZED = 'cc_debug_response_serialized';
    /*
     * Credit card SS start month.
     */
    const CC_SS_START_MONTH = 'cc_ss_start_month';
    /*
     * eCheck account type.
     */
    const ECHECK_ACCOUNT_TYPE = 'echeck_account_type';
    /*
     * Last transaction ID.
     */
    const LAST_TRANS_ID = 'last_trans_id';
    /*
     * Credit card CID status.
     */
    const CC_CID_STATUS = 'cc_cid_status';
    /*
     * Credit card owner.
     */
    const CC_OWNER = 'cc_owner';
    /*
     * Credit card type.
     */
    const CC_TYPE = 'cc_type';
    /*
     * PO number.
     */
    const PO_NUMBER = 'po_number';
    /*
     * Credit card expiration year.
     */
    const CC_EXP_YEAR = 'cc_exp_year';
    /*
     * Credit card status.
     */
    const CC_STATUS = 'cc_status';
    /*
     * eCheck routing number.
     */
    const ECHECK_ROUTING_NUMBER = 'echeck_routing_number';
    /*
     * Account status.
     */
    const ACCOUNT_STATUS = 'account_status';
    /*
     * ANET transaction method.
     */
    const ANET_TRANS_METHOD = 'anet_trans_method';
    /*
     * Credit card debug response body.
     */
    const CC_DEBUG_RESPONSE_BODY = 'cc_debug_response_body';
    /*
     * Credit card SS issue.
     */
    const CC_SS_ISSUE = 'cc_ss_issue';
    /*
     * eCheck account name.
     */
    const ECHECK_ACCOUNT_NAME = 'echeck_account_name';
    /*
     * Credit card AVS status.
     */
    const CC_AVS_STATUS = 'cc_avs_status';
    /*
     * Encrypted credit card number.
     */
    const CC_NUMBER_ENC = 'cc_number_enc';
    /*
     * Credit card transaction ID.
     */
    const CC_TRANS_ID = 'cc_trans_id';
    /*
     * Address status.
     */
    const ADDRESS_STATUS = 'address_status';
    /*
     * Additional information.
     */
    const ADDITIONAL_INFORMATION = 'additional_information';

    /**
     * Gets the account status for the order payment.
     *
     * @return string Account status.
     */
    public function getAccountStatus();

    /**
     * Gets the additional data for the order payment.
     *
     * @return string Additional data.
     */
    public function getAdditionalData();

    /**
     * Gets the additional information for the order payment.
     *
     * @return string[] Array of additional information.
     */
    public function getAdditionalInformation();

    /**
     * Gets the address status for the order payment.
     *
     * @return string Address status.
     */
    public function getAddressStatus();

    /**
     * Gets the amount authorized for the order payment.
     *
     * @return float Amount authorized.
     */
    public function getAmountAuthorized();

    /**
     * Gets the amount canceled for the order payment.
     *
     * @return float Amount canceled.
     */
    public function getAmountCanceled();

    /**
     * Gets the amount ordered for the order payment.
     *
     * @return float Amount ordered.
     */
    public function getAmountOrdered();

    /**
     * Gets the amount paid for the order payment.
     *
     * @return float Amount paid.
     */
    public function getAmountPaid();

    /**
     * Gets the amount refunded for the order payment.
     *
     * @return float Amount refunded.
     */
    public function getAmountRefunded();

    /**
     * Gets the anet transaction method for the order payment.
     *
     * @return string Anet transaction method.
     */
    public function getAnetTransMethod();

    /**
     * Gets the base amount authorized for the order payment.
     *
     * @return float Base amount authorized.
     */
    public function getBaseAmountAuthorized();

    /**
     * Gets the base amount canceled for the order payment.
     *
     * @return float Base amount canceled.
     */
    public function getBaseAmountCanceled();

    /**
     * Gets the base amount ordered for the order payment.
     *
     * @return float Base amount ordered.
     */
    public function getBaseAmountOrdered();

    /**
     * Gets the base amount paid for the order payment.
     *
     * @return float Base amount paid.
     */
    public function getBaseAmountPaid();

    /**
     * Gets the base amount paid online for the order payment.
     *
     * @return float Base amount paid online.
     */
    public function getBaseAmountPaidOnline();

    /**
     * Gets the base amount refunded for the order payment.
     *
     * @return float Base amount refunded.
     */
    public function getBaseAmountRefunded();

    /**
     * Gets the base amount refunded online for the order payment.
     *
     * @return float Base amount refunded online.
     */
    public function getBaseAmountRefundedOnline();

    /**
     * Gets the base shipping amount for the order payment.
     *
     * @return float Base shipping amount.
     */
    public function getBaseShippingAmount();

    /**
     * Gets the base shipping captured for the order payment.
     *
     * @return float Base shipping captured amount.
     */
    public function getBaseShippingCaptured();

    /**
     * Gets the base shipping refunded amount for the order payment.
     *
     * @return float Base shipping refunded amount.
     */
    public function getBaseShippingRefunded();

    /**
     * Gets the credit card approval for the order payment.
     *
     * @return string Credit card approval.
     */
    public function getCcApproval();

    /**
     * Gets the credit card avs status for the order payment.
     *
     * @return string Credit card avs status.
     */
    public function getCcAvsStatus();

    /**
     * Gets the credit card cid status for the order payment.
     *
     * @return string Credit card CID status.
     */
    public function getCcCidStatus();

    /**
     * Gets the credit card debug request body for the order payment.
     *
     * @return string Credit card debug request body.
     */
    public function getCcDebugRequestBody();

    /**
     * Gets the credit card debug response body for the order payment.
     *
     * @return string Credit card debug response body.
     */
    public function getCcDebugResponseBody();

    /**
     * Gets the credit card debug response serialized for the order payment.
     *
     * @return string Credit card debug response serialized.
     */
    public function getCcDebugResponseSerialized();

    /**
     * Gets the credit card expiration month for the order payment.
     *
     * @return string Credit card expiration month.
     */
    public function getCcExpMonth();

    /**
     * Gets the credit card expiration year for the order payment.
     *
     * @return string Credit card expiration year.
     */
    public function getCcExpYear();

    /**
     * Gets the last four digits of the credit card for the order payment.
     *
     * @return string Last four digits of the credit card.
     */
    public function getCcLast4();

    /**
     * Gets the encrypted credit card number for the order payment.
     *
     * @return string Encrypted credit card number.
     */
    public function getCcNumberEnc();

    /**
     * Gets the credit card owner for the order payment.
     *
     * @return string Credit card number.
     */
    public function getCcOwner();

    /**
     * Gets the credit card secure verify for the order payment.
     *
     * @return string Credit card secure verify.
     */
    public function getCcSecureVerify();

    /**
     * Gets the credit card SS issue for the order payment.
     *
     * @return string Credit card SS issue.
     */
    public function getCcSsIssue();

    /**
     * Gets the credit card SS start month for the order payment.
     *
     * @return string Credit card SS start month.
     */
    public function getCcSsStartMonth();

    /**
     * Gets the credit card SS start year for the order payment.
     *
     * @return string Credit card SS start year.
     */
    public function getCcSsStartYear();

    /**
     * Gets the credit card status for the order payment.
     *
     * @return string Credit card status.
     */
    public function getCcStatus();

    /**
     * Gets the credit card status description for the order payment.
     *
     * @return string Credit card status description.
     */
    public function getCcStatusDescription();

    /**
     * Gets the credit card transaction id for the order payment.
     *
     * @return string Credit card transaction ID.
     */
    public function getCcTransId();

    /**
     * Gets the credit card type for the order payment.
     *
     * @return string Credit card type.
     */
    public function getCcType();

    /**
     * Gets the eCheck account name for the order payment.
     *
     * @return string eCheck account name.
     */
    public function getEcheckAccountName();

    /**
     * Gets the eCheck account type for the order payment.
     *
     * @return string eCheck account type.
     */
    public function getEcheckAccountType();

    /**
     * Gets the eCheck bank name for the order payment.
     *
     * @return string eCheck bank name.
     */
    public function getEcheckBankName();

    /**
     * Gets the eCheck routing number for the order payment.
     *
     * @return string eCheck routing number.
     */
    public function getEcheckRoutingNumber();

    /**
     * Gets the eCheck type for the order payment.
     *
     * @return string eCheck type.
     */
    public function getEcheckType();

    /**
     * Gets the entity ID for the order payment.
     *
     * @return int Entity ID.
     */
    public function getEntityId();

    /**
     * Gets the last transaction ID for the order payment.
     *
     * @return string Last transaction ID.
     */
    public function getLastTransId();

    /**
     * Gets the method for the order payment.
     *
     * @return string Method.
     */
    public function getMethod();

    /**
     * Gets the parent ID for the order payment.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Gets the PO number for the order payment.
     *
     * @return string PO number.
     */
    public function getPoNumber();

    /**
     * Gets the protection eligibility for the order payment.
     *
     * @return string Protection eligibility.
     */
    public function getProtectionEligibility();

    /**
     * Gets the quote payment ID for the order payment.
     *
     * @return int Quote payment ID.
     */
    public function getQuotePaymentId();

    /**
     * Gets the shipping amount for the order payment.
     *
     * @return float Shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the shipping captured for the order payment.
     *
     * @return float Shipping captured.
     */
    public function getShippingCaptured();

    /**
     * Gets the shipping refunded for the order payment.
     *
     * @return float Shipping refunded.
     */
    public function getShippingRefunded();
}
