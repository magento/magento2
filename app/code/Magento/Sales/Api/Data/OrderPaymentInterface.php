<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface OrderPaymentInterface
 */
interface OrderPaymentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const BASE_SHIPPING_CAPTURED = 'base_shipping_captured';
    const SHIPPING_CAPTURED = 'shipping_captured';
    const AMOUNT_REFUNDED = 'amount_refunded';
    const BASE_AMOUNT_PAID = 'base_amount_paid';
    const AMOUNT_CANCELED = 'amount_canceled';
    const BASE_AMOUNT_AUTHORIZED = 'base_amount_authorized';
    const BASE_AMOUNT_PAID_ONLINE = 'base_amount_paid_online';
    const BASE_AMOUNT_REFUNDED_ONLINE = 'base_amount_refunded_online';
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const AMOUNT_PAID = 'amount_paid';
    const AMOUNT_AUTHORIZED = 'amount_authorized';
    const BASE_AMOUNT_ORDERED = 'base_amount_ordered';
    const BASE_SHIPPING_REFUNDED = 'base_shipping_refunded';
    const SHIPPING_REFUNDED = 'shipping_refunded';
    const BASE_AMOUNT_REFUNDED = 'base_amount_refunded';
    const AMOUNT_ORDERED = 'amount_ordered';
    const BASE_AMOUNT_CANCELED = 'base_amount_canceled';
    const QUOTE_PAYMENT_ID = 'quote_payment_id';
    const ADDITIONAL_DATA = 'additional_data';
    const CC_EXP_MONTH = 'cc_exp_month';
    const CC_SS_START_YEAR = 'cc_ss_start_year';
    const ECHECK_BANK_NAME = 'echeck_bank_name';
    const METHOD = 'method';
    const CC_DEBUG_REQUEST_BODY = 'cc_debug_request_body';
    const CC_SECURE_VERIFY = 'cc_secure_verify';
    const PROTECTION_ELIGIBILITY = 'protection_eligibility';
    const CC_APPROVAL = 'cc_approval';
    const CC_LAST_4 = 'cc_last_4';
    const CC_STATUS_DESCRIPTION = 'cc_status_description';
    const ECHECK_TYPE = 'echeck_type';
    const CC_DEBUG_RESPONSE_SERIALIZED = 'cc_debug_response_serialized';
    const CC_SS_START_MONTH = 'cc_ss_start_month';
    const ECHECK_ACCOUNT_TYPE = 'echeck_account_type';
    const LAST_TRANS_ID = 'last_trans_id';
    const CC_CID_STATUS = 'cc_cid_status';
    const CC_OWNER = 'cc_owner';
    const CC_TYPE = 'cc_type';
    const PO_NUMBER = 'po_number';
    const CC_EXP_YEAR = 'cc_exp_year';
    const CC_STATUS = 'cc_status';
    const ECHECK_ROUTING_NUMBER = 'echeck_routing_number';
    const ACCOUNT_STATUS = 'account_status';
    const ANET_TRANS_METHOD = 'anet_trans_method';
    const CC_DEBUG_RESPONSE_BODY = 'cc_debug_response_body';
    const CC_SS_ISSUE = 'cc_ss_issue';
    const ECHECK_ACCOUNT_NAME = 'echeck_account_name';
    const CC_AVS_STATUS = 'cc_avs_status';
    const CC_NUMBER_ENC = 'cc_number_enc';
    const CC_TRANS_ID = 'cc_trans_id';
    const ADDRESS_STATUS = 'address_status';
    const ADDITIONAL_INFORMATION = 'additional_information';

    /**
     * Returns account_status
     *
     * @return string
     */
    public function getAccountStatus();

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData();

    /**
     * Returns additional_information
     *
     * @return string[]
     */
    public function getAdditionalInformation();

    /**
     * Returns address_status
     *
     * @return string
     */
    public function getAddressStatus();

    /**
     * Returns amount_authorized
     *
     * @return float
     */
    public function getAmountAuthorized();

    /**
     * Returns amount_canceled
     *
     * @return float
     */
    public function getAmountCanceled();

    /**
     * Returns amount_ordered
     *
     * @return float
     */
    public function getAmountOrdered();

    /**
     * Returns amount_paid
     *
     * @return float
     */
    public function getAmountPaid();

    /**
     * Returns amount_refunded
     *
     * @return float
     */
    public function getAmountRefunded();

    /**
     * Returns anet_trans_method
     *
     * @return string
     */
    public function getAnetTransMethod();

    /**
     * Returns base_amount_authorized
     *
     * @return float
     */
    public function getBaseAmountAuthorized();

    /**
     * Returns base_amount_canceled
     *
     * @return float
     */
    public function getBaseAmountCanceled();

    /**
     * Returns base_amount_ordered
     *
     * @return float
     */
    public function getBaseAmountOrdered();

    /**
     * Returns base_amount_paid
     *
     * @return float
     */
    public function getBaseAmountPaid();

    /**
     * Returns base_amount_paid_online
     *
     * @return float
     */
    public function getBaseAmountPaidOnline();

    /**
     * Returns base_amount_refunded
     *
     * @return float
     */
    public function getBaseAmountRefunded();

    /**
     * Returns base_amount_refunded_online
     *
     * @return float
     */
    public function getBaseAmountRefundedOnline();

    /**
     * Returns base_shipping_amount
     *
     * @return float
     */
    public function getBaseShippingAmount();

    /**
     * Returns base_shipping_captured
     *
     * @return float
     */
    public function getBaseShippingCaptured();

    /**
     * Returns base_shipping_refunded
     *
     * @return float
     */
    public function getBaseShippingRefunded();

    /**
     * Returns cc_approval
     *
     * @return string
     */
    public function getCcApproval();

    /**
     * Returns cc_avs_status
     *
     * @return string
     */
    public function getCcAvsStatus();

    /**
     * Returns cc_cid_status
     *
     * @return string
     */
    public function getCcCidStatus();

    /**
     * Returns cc_debug_request_body
     *
     * @return string
     */
    public function getCcDebugRequestBody();

    /**
     * Returns cc_debug_response_body
     *
     * @return string
     */
    public function getCcDebugResponseBody();

    /**
     * Returns cc_debug_response_serialized
     *
     * @return string
     */
    public function getCcDebugResponseSerialized();

    /**
     * Returns cc_exp_month
     *
     * @return string
     */
    public function getCcExpMonth();

    /**
     * Returns cc_exp_year
     *
     * @return string
     */
    public function getCcExpYear();

    /**
     * Returns cc_last_4
     *
     * @return string
     */
    public function getCcLast4();

    /**
     * Returns cc_number_enc
     *
     * @return string
     */
    public function getCcNumberEnc();

    /**
     * Returns cc_owner
     *
     * @return string
     */
    public function getCcOwner();

    /**
     * Returns cc_secure_verify
     *
     * @return string
     */
    public function getCcSecureVerify();

    /**
     * Returns cc_ss_issue
     *
     * @return string
     */
    public function getCcSsIssue();

    /**
     * Returns cc_ss_start_month
     *
     * @return string
     */
    public function getCcSsStartMonth();

    /**
     * Returns cc_ss_start_year
     *
     * @return string
     */
    public function getCcSsStartYear();

    /**
     * Returns cc_status
     *
     * @return string
     */
    public function getCcStatus();

    /**
     * Returns cc_status_description
     *
     * @return string
     */
    public function getCcStatusDescription();

    /**
     * Returns cc_trans_id
     *
     * @return string
     */
    public function getCcTransId();

    /**
     * Returns cc_type
     *
     * @return string
     */
    public function getCcType();

    /**
     * Returns echeck_account_name
     *
     * @return string
     */
    public function getEcheckAccountName();

    /**
     * Returns echeck_account_type
     *
     * @return string
     */
    public function getEcheckAccountType();

    /**
     * Returns echeck_bank_name
     *
     * @return string
     */
    public function getEcheckBankName();

    /**
     * Returns echeck_routing_number
     *
     * @return string
     */
    public function getEcheckRoutingNumber();

    /**
     * Returns echeck_type
     *
     * @return string
     */
    public function getEcheckType();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Returns last_trans_id
     *
     * @return string
     */
    public function getLastTransId();

    /**
     * Returns method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId();

    /**
     * Returns po_number
     *
     * @return string
     */
    public function getPoNumber();

    /**
     * Returns protection_eligibility
     *
     * @return string
     */
    public function getProtectionEligibility();

    /**
     * Returns quote_payment_id
     *
     * @return int
     */
    public function getQuotePaymentId();

    /**
     * Returns shipping_amount
     *
     * @return float
     */
    public function getShippingAmount();

    /**
     * Returns shipping_captured
     *
     * @return float
     */
    public function getShippingCaptured();

    /**
     * Returns shipping_refunded
     *
     * @return float
     */
    public function getShippingRefunded();
}
