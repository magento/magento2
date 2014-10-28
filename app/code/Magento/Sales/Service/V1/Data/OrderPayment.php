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
 * Class OrderPayment
 */
class OrderPayment extends DataObject
{
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
    const CC_LAST4 = 'cc_last4';
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
    public function getAccountStatus()
    {
        return $this->_get(self::ACCOUNT_STATUS);
    }

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->_get(self::ADDITIONAL_DATA);
    }

    /**
     * Returns additional_information
     *
     * @return string[]
     */
    public function getAdditionalInformation()
    {
        return $this->_get(self::ADDITIONAL_INFORMATION);
    }

    /**
     * Returns address_status
     *
     * @return string
     */
    public function getAddressStatus()
    {
        return $this->_get(self::ADDRESS_STATUS);
    }

    /**
     * Returns amount_authorized
     *
     * @return float
     */
    public function getAmountAuthorized()
    {
        return $this->_get(self::AMOUNT_AUTHORIZED);
    }

    /**
     * Returns amount_canceled
     *
     * @return float
     */
    public function getAmountCanceled()
    {
        return $this->_get(self::AMOUNT_CANCELED);
    }

    /**
     * Returns amount_ordered
     *
     * @return float
     */
    public function getAmountOrdered()
    {
        return $this->_get(self::AMOUNT_ORDERED);
    }

    /**
     * Returns amount_paid
     *
     * @return float
     */
    public function getAmountPaid()
    {
        return $this->_get(self::AMOUNT_PAID);
    }

    /**
     * Returns amount_refunded
     *
     * @return float
     */
    public function getAmountRefunded()
    {
        return $this->_get(self::AMOUNT_REFUNDED);
    }

    /**
     * Returns anet_trans_method
     *
     * @return string
     */
    public function getAnetTransMethod()
    {
        return $this->_get(self::ANET_TRANS_METHOD);
    }

    /**
     * Returns base_amount_authorized
     *
     * @return float
     */
    public function getBaseAmountAuthorized()
    {
        return $this->_get(self::BASE_AMOUNT_AUTHORIZED);
    }

    /**
     * Returns base_amount_canceled
     *
     * @return float
     */
    public function getBaseAmountCanceled()
    {
        return $this->_get(self::BASE_AMOUNT_CANCELED);
    }

    /**
     * Returns base_amount_ordered
     *
     * @return float
     */
    public function getBaseAmountOrdered()
    {
        return $this->_get(self::BASE_AMOUNT_ORDERED);
    }

    /**
     * Returns base_amount_paid
     *
     * @return float
     */
    public function getBaseAmountPaid()
    {
        return $this->_get(self::BASE_AMOUNT_PAID);
    }

    /**
     * Returns base_amount_paid_online
     *
     * @return float
     */
    public function getBaseAmountPaidOnline()
    {
        return $this->_get(self::BASE_AMOUNT_PAID_ONLINE);
    }

    /**
     * Returns base_amount_refunded
     *
     * @return float
     */
    public function getBaseAmountRefunded()
    {
        return $this->_get(self::BASE_AMOUNT_REFUNDED);
    }

    /**
     * Returns base_amount_refunded_online
     *
     * @return float
     */
    public function getBaseAmountRefundedOnline()
    {
        return $this->_get(self::BASE_AMOUNT_REFUNDED_ONLINE);
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
     * Returns base_shipping_captured
     *
     * @return float
     */
    public function getBaseShippingCaptured()
    {
        return $this->_get(self::BASE_SHIPPING_CAPTURED);
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
     * Returns cc_approval
     *
     * @return string
     */
    public function getCcApproval()
    {
        return $this->_get(self::CC_APPROVAL);
    }

    /**
     * Returns cc_avs_status
     *
     * @return string
     */
    public function getCcAvsStatus()
    {
        return $this->_get(self::CC_AVS_STATUS);
    }

    /**
     * Returns cc_cid_status
     *
     * @return string
     */
    public function getCcCidStatus()
    {
        return $this->_get(self::CC_CID_STATUS);
    }

    /**
     * Returns cc_debug_request_body
     *
     * @return string
     */
    public function getCcDebugRequestBody()
    {
        return $this->_get(self::CC_DEBUG_REQUEST_BODY);
    }

    /**
     * Returns cc_debug_response_body
     *
     * @return string
     */
    public function getCcDebugResponseBody()
    {
        return $this->_get(self::CC_DEBUG_RESPONSE_BODY);
    }

    /**
     * Returns cc_debug_response_serialized
     *
     * @return string
     */
    public function getCcDebugResponseSerialized()
    {
        return $this->_get(self::CC_DEBUG_RESPONSE_SERIALIZED);
    }

    /**
     * Returns cc_exp_month
     *
     * @return string
     */
    public function getCcExpMonth()
    {
        return $this->_get(self::CC_EXP_MONTH);
    }

    /**
     * Returns cc_exp_year
     *
     * @return string
     */
    public function getCcExpYear()
    {
        return $this->_get(self::CC_EXP_YEAR);
    }

    /**
     * Returns cc_last4
     *
     * @return string
     */
    public function getCcLast4()
    {
        return $this->_get(self::CC_LAST4);
    }

    /**
     * Returns cc_number_enc
     *
     * @return string
     */
    public function getCcNumberEnc()
    {
        return $this->_get(self::CC_NUMBER_ENC);
    }

    /**
     * Returns cc_owner
     *
     * @return string
     */
    public function getCcOwner()
    {
        return $this->_get(self::CC_OWNER);
    }

    /**
     * Returns cc_secure_verify
     *
     * @return string
     */
    public function getCcSecureVerify()
    {
        return $this->_get(self::CC_SECURE_VERIFY);
    }

    /**
     * Returns cc_ss_issue
     *
     * @return string
     */
    public function getCcSsIssue()
    {
        return $this->_get(self::CC_SS_ISSUE);
    }

    /**
     * Returns cc_ss_start_month
     *
     * @return string
     */
    public function getCcSsStartMonth()
    {
        return $this->_get(self::CC_SS_START_MONTH);
    }

    /**
     * Returns cc_ss_start_year
     *
     * @return string
     */
    public function getCcSsStartYear()
    {
        return $this->_get(self::CC_SS_START_YEAR);
    }

    /**
     * Returns cc_status
     *
     * @return string
     */
    public function getCcStatus()
    {
        return $this->_get(self::CC_STATUS);
    }

    /**
     * Returns cc_status_description
     *
     * @return string
     */
    public function getCcStatusDescription()
    {
        return $this->_get(self::CC_STATUS_DESCRIPTION);
    }

    /**
     * Returns cc_trans_id
     *
     * @return string
     */
    public function getCcTransId()
    {
        return $this->_get(self::CC_TRANS_ID);
    }

    /**
     * Returns cc_type
     *
     * @return string
     */
    public function getCcType()
    {
        return $this->_get(self::CC_TYPE);
    }

    /**
     * Returns echeck_account_name
     *
     * @return string
     */
    public function getEcheckAccountName()
    {
        return $this->_get(self::ECHECK_ACCOUNT_NAME);
    }

    /**
     * Returns echeck_account_type
     *
     * @return string
     */
    public function getEcheckAccountType()
    {
        return $this->_get(self::ECHECK_ACCOUNT_TYPE);
    }

    /**
     * Returns echeck_bank_name
     *
     * @return string
     */
    public function getEcheckBankName()
    {
        return $this->_get(self::ECHECK_BANK_NAME);
    }

    /**
     * Returns echeck_routing_number
     *
     * @return string
     */
    public function getEcheckRoutingNumber()
    {
        return $this->_get(self::ECHECK_ROUTING_NUMBER);
    }

    /**
     * Returns echeck_type
     *
     * @return string
     */
    public function getEcheckType()
    {
        return $this->_get(self::ECHECK_TYPE);
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
     * Returns last_trans_id
     *
     * @return string
     */
    public function getLastTransId()
    {
        return $this->_get(self::LAST_TRANS_ID);
    }

    /**
     * Returns method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_get(self::METHOD);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->_get(self::PARENT_ID);
    }

    /**
     * Returns po_number
     *
     * @return string
     */
    public function getPoNumber()
    {
        return $this->_get(self::PO_NUMBER);
    }

    /**
     * Returns protection_eligibility
     *
     * @return string
     */
    public function getProtectionEligibility()
    {
        return $this->_get(self::PROTECTION_ELIGIBILITY);
    }

    /**
     * Returns quote_payment_id
     *
     * @return int
     */
    public function getQuotePaymentId()
    {
        return $this->_get(self::QUOTE_PAYMENT_ID);
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
     * Returns shipping_captured
     *
     * @return float
     */
    public function getShippingCaptured()
    {
        return $this->_get(self::SHIPPING_CAPTURED);
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
}
