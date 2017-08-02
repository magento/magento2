<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Model;

/**
 * PayPal payment information model
 *
 * Aware of all PayPal payment methods
 * Collects and provides access to PayPal-specific payment data
 * Provides business logic information about payment flow
 * @since 2.0.0
 */
class Info
{
    /**
     * Cross-models public exchange keys
     *
     * @var string
     */
    const PAYER_ID = 'payer_id';

    const PAYER_EMAIL = 'email';

    const PAYER_STATUS = 'payer_status';

    const ADDRESS_ID = 'address_id';

    const ADDRESS_STATUS = 'address_status';

    const PROTECTION_EL = 'protection_eligibility';

    const FRAUD_FILTERS = 'collected_fraud_filters';

    const CORRELATION_ID = 'correlation_id';

    const AVS_CODE = 'avs_result';

    const AVSADDR = 'avsaddr';

    const AVSZIP = 'avszip';

    const IAVS = 'iavs';

    const CVV2MATCH = 'cvv2match';

    const CVV_2_MATCH = 'cvv_2_check_result';

    // Next two fields are required for Brazil
    const BUYER_TAX_ID = 'buyer_tax_id';

    const BUYER_TAX_ID_TYPE = 'buyer_tax_id_type';

    const PAYMENT_STATUS = 'payment_status';

    const PENDING_REASON = 'pending_reason';

    const IS_FRAUD = 'is_fraud_detected';

    const PAYMENT_STATUS_GLOBAL = 'paypal_payment_status';

    const PENDING_REASON_GLOBAL = 'paypal_pending_reason';

    const IS_FRAUD_GLOBAL = 'paypal_is_fraud_detected';

    /**
     * Possible buyer's tax id types (Brazil only)
     */
    const BUYER_TAX_ID_TYPE_CPF = 'BR_CPF';

    const BUYER_TAX_ID_TYPE_CNPJ = 'BR_CNPJ';

    /**
     * All payment information map
     *
     * @var array
     * @since 2.0.0
     */
    protected $_paymentMap = [
        self::PAYER_ID => self::PAYPAL_PAYER_ID,
        self::PAYER_EMAIL => self::PAYPAL_PAYER_EMAIL,
        self::PAYER_STATUS => self::PAYPAL_PAYER_STATUS,
        self::ADDRESS_ID => self::PAYPAL_ADDRESS_ID,
        self::ADDRESS_STATUS => self::PAYPAL_ADDRESS_STATUS,
        self::PROTECTION_EL => self::PAYPAL_PROTECTION_ELIGIBILITY,
        self::FRAUD_FILTERS => self::PAYPAL_FRAUD_FILTERS,
        self::CORRELATION_ID => self::PAYPAL_CORRELATION_ID,
        self::AVS_CODE => self::PAYPAL_AVS_CODE,
        self::CVV_2_MATCH => self::PAYPAL_CVV_2_MATCH,
        self::BUYER_TAX_ID => self::BUYER_TAX_ID,
        self::BUYER_TAX_ID_TYPE => self::BUYER_TAX_ID_TYPE,
        self::AVSADDR => self::PAYPAL_AVSADDR,
        self::AVSZIP => self::PAYPAL_AVSZIP,
        self::IAVS => self::PAYPAL_IAVS,
        self::CVV2MATCH => self::PAYPAL_CVV2MATCH
    ];

    /**
     * System information map
     *
     * @var array
     * @since 2.0.0
     */
    protected $_systemMap = [
        self::PAYMENT_STATUS => self::PAYMENT_STATUS_GLOBAL,
        self::PENDING_REASON => self::PENDING_REASON_GLOBAL,
        self::IS_FRAUD => self::IS_FRAUD_GLOBAL,
    ];

    /**
     * PayPal payment status possible values
     *
     * @var string
     */
    const PAYMENTSTATUS_NONE = 'none';

    const PAYMENTSTATUS_COMPLETED = 'completed';

    const PAYMENTSTATUS_DENIED = 'denied';

    const PAYMENTSTATUS_EXPIRED = 'expired';

    const PAYMENTSTATUS_FAILED = 'failed';

    const PAYMENTSTATUS_INPROGRESS = 'in_progress';

    const PAYMENTSTATUS_PENDING = 'pending';

    const PAYMENTSTATUS_REFUNDED = 'refunded';

    const PAYMENTSTATUS_REFUNDEDPART = 'partially_refunded';

    const PAYMENTSTATUS_REVERSED = 'reversed';

    const PAYMENTSTATUS_UNREVERSED = 'canceled_reversal';

    const PAYMENTSTATUS_PROCESSED = 'processed';

    const PAYMENTSTATUS_VOIDED = 'voided';

    const PAYMENTSTATUS_REVIEW = 'paymentreview';

    /**
     * PayPal payment transaction type
     */
    const TXN_TYPE_ADJUSTMENT = 'adjustment';

    const TXN_TYPE_NEW_CASE = 'new_case';

    /**
     * PayPal payment reason code when payment_status is Reversed, Refunded, or Canceled_Reversal.
     */
    const PAYMENT_REASON_CODE_REFUND = 'refund';

    /**
     * PayPal order status for Reverse payment status
     */
    const ORDER_STATUS_REVERSED = 'paypal_reversed';

    /**
     * PayPal order status for Canceled Reversal payment status
     */
    const ORDER_STATUS_CANCELED_REVERSAL = 'paypal_canceled_reversal';

    /**
     * Map of payment information available to customer
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_paymentPublicMap = ['paypal_payer_email', self::BUYER_TAX_ID, self::BUYER_TAX_ID_TYPE];

    /**
     * Rendered payment map cache
     *
     * @var array
     * @since 2.0.0
     */
    protected $_paymentMapFull = [];

    /**
     * Cache for storing label translations
     *
     * @var array
     * @since 2.0.0
     */
    protected $_labelCodesCache = [];

    /**
     * Paypal payer id code key
     */
    const PAYPAL_PAYER_ID = 'paypal_payer_id';

    /**
     * Paypal payer email code key
     */
    const PAYPAL_PAYER_EMAIL = 'paypal_payer_email';

    /**
     * Paypal payer status code key
     */
    const PAYPAL_PAYER_STATUS = 'paypal_payer_status';

    /**
     * Paypal address id code key
     */
    const PAYPAL_ADDRESS_ID = 'paypal_address_id';

    /**
     * Paypal address status code key
     */
    const PAYPAL_ADDRESS_STATUS = 'paypal_address_status';

    /**
     * Paypal protection eligibility code key
     */
    const PAYPAL_PROTECTION_ELIGIBILITY = 'paypal_protection_eligibility';

    /**
     * Paypal fraud filters code key
     */
    const PAYPAL_FRAUD_FILTERS = 'paypal_fraud_filters';

    /**
     * Paypal correlation id code key
     */
    const PAYPAL_CORRELATION_ID = 'paypal_correlation_id';

    /**
     * Paypal avs code key
     */
    const PAYPAL_AVS_CODE = 'paypal_avs_code';

    /**
     * Paypal cvv2 code key
     */
    const PAYPAL_CVV_2_MATCH = 'paypal_cvv_2_match';

    /**
     * Item labels key for label codes cache
     */
    const ITEM_LABELS = 'item labels';

    /**
     * Paypal avs street code key
     */
    const PAYPAL_AVSADDR = 'avsaddr';

    /**
     * Paypal avs zip code key
     */
    const PAYPAL_AVSZIP = 'avszip';

    /**
     * Paypal avs international code key
     */
    const PAYPAL_IAVS = 'iavs';

    /**
     * Paypal cvv2 code key
     */
    const PAYPAL_CVV2MATCH = 'cvv2match';

    /**
     * All available payment info getter
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param bool $labelValuesOnly
     * @return array
     * @since 2.0.0
     */
    public function getPaymentInfo(\Magento\Payment\Model\InfoInterface $payment, $labelValuesOnly = false)
    {
        // collect paypal-specific info
        $result = $this->_getFullInfo(array_values($this->_paymentMap), $payment, $labelValuesOnly);

        // add last_trans_id
        $label = __('Last Transaction ID');
        $value = $payment->getLastTransId();
        if ($labelValuesOnly) {
            $result[(string)$label] = $value;
        } else {
            $result['last_trans_id'] = ['label' => $label, 'value' => $value];
        }

        return $result;
    }

    /**
     * Public payment info getter
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param bool $labelValuesOnly
     * @return array
     * @since 2.0.0
     */
    public function getPublicPaymentInfo(\Magento\Payment\Model\InfoInterface $payment, $labelValuesOnly = false)
    {
        return $this->_getFullInfo($this->_paymentPublicMap, $payment, $labelValuesOnly);
    }

    /**
     * Grab data from source and map it into payment
     *
     * @param array|\Magento\Framework\DataObject|callback $from
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return void
     * @since 2.0.0
     */
    public function importToPayment($from, \Magento\Payment\Model\InfoInterface $payment)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        if (is_object($from)) {
            $from = [$from, 'getDataUsingMethod'];
        }
        \Magento\Framework\DataObject\Mapper::accumulateByMap($from, [$payment, 'setAdditionalInformation'], $fullMap);
    }

    /**
     * Grab data from payment and map it into target
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param array|\Magento\Framework\DataObject|callback $to
     * @param array|null $map
     * @return array|\Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function &exportFromPayment(\Magento\Payment\Model\InfoInterface $payment, $to, array $map = null)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        \Magento\Framework\DataObject\Mapper::accumulateByMap(
            [$payment, 'getAdditionalInformation'],
            $to,
            $map ? $map : array_flip($fullMap)
        );
        return $to;
    }

    /**
     * Check whether the payment is in review state
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     * @since 2.0.0
     */
    public static function isPaymentReviewRequired(\Magento\Payment\Model\InfoInterface $payment)
    {
        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        if (self::PAYMENTSTATUS_PENDING === $paymentStatus) {
            $pendingReason = $payment->getAdditionalInformation(self::PENDING_REASON_GLOBAL);
            return !in_array($pendingReason, ['authorization', 'order']);
        }
        return false;
    }

    /**
     * Check whether fraud order review detected and can be reviewed
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     * @since 2.0.0
     */
    public static function isFraudReviewAllowed(\Magento\Payment\Model\InfoInterface $payment)
    {
        return self::isPaymentReviewRequired(
            $payment
        ) && 1 == $payment->getAdditionalInformation(
            self::IS_FRAUD_GLOBAL
        );
    }

    /**
     * Check whether the payment is completed
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     * @since 2.0.0
     */
    public static function isPaymentCompleted(\Magento\Payment\Model\InfoInterface $payment)
    {
        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        return self::PAYMENTSTATUS_COMPLETED === $paymentStatus;
    }

    /**
     * Check whether the payment was processed successfully
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     * @since 2.0.0
     */
    public static function isPaymentSuccessful(\Magento\Payment\Model\InfoInterface $payment)
    {
        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        if (in_array(
            $paymentStatus,
            [
                self::PAYMENTSTATUS_COMPLETED,
                self::PAYMENTSTATUS_INPROGRESS,
                self::PAYMENTSTATUS_REFUNDED,
                self::PAYMENTSTATUS_REFUNDEDPART,
                self::PAYMENTSTATUS_UNREVERSED,
                self::PAYMENTSTATUS_PROCESSED
            ]
        )
        ) {
            return true;
        }
        $pendingReason = $payment->getAdditionalInformation(self::PENDING_REASON_GLOBAL);
        return self::PAYMENTSTATUS_PENDING === $paymentStatus && in_array(
            $pendingReason,
            ['authorization', 'order']
        );
    }

    /**
     * Check whether the payment was processed unsuccessfully or failed
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     * @since 2.0.0
     */
    public static function isPaymentFailed(\Magento\Payment\Model\InfoInterface $payment)
    {
        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        return in_array(
            $paymentStatus,
            [
                self::PAYMENTSTATUS_DENIED,
                self::PAYMENTSTATUS_EXPIRED,
                self::PAYMENTSTATUS_FAILED,
                self::PAYMENTSTATUS_REVERSED,
                self::PAYMENTSTATUS_VOIDED
            ]
        );
    }

    /**
     * Explain pending payment reason code
     *
     * @param string $code
     * @return \Magento\Framework\Phrase
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetTransactionDetails
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public static function explainPendingReason($code)
    {
        switch ($code) {
            case 'address':
                return __('This customer didn\'t include a confirmed address.');
            case 'authorization':
            case 'order':
                return __('The payment is authorized but not settled.');
            case 'echeck':
                return __('The payment eCheck is not cleared.');
            case 'intl':
                return __('The merchant holds a non-U.S. account and doesn\'t have a withdrawal mechanism.');
            case 'multi-currency':
                // break is intentionally omitted
            case 'multi_currency':
                // break is intentionally omitted
            case 'multicurrency':
                return __('The payment currency doesn\'t match any of the merchant\'s balances currency.');
            case 'paymentreview':
                return __('The payment is pending while it is being reviewed by PayPal for risk.');
            case 'unilateral':
                return __(
                    'The payment is pending because it was made to an email address that is not yet registered or confirmed.'
                );
            case 'verify':
                return __('The merchant account is not yet verified.');
            case 'upgrade':
                return __(
                    'The payment was made via credit card. In order to receive funds merchant must upgrade account to Business or Premier status.'
                );
            case 'none':
                // break is intentionally omitted
            case 'other':
                // break is intentionally omitted
            default:
                return __('Sorry, but something went wrong. Please contact PayPal customer service.');
        }
    }

    /**
     * Explain the refund or chargeback reason code
     *
     * @param string $code
     * @return string
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetTransactionDetails
     * @since 2.0.0
     */
    public static function explainReasonCode($code)
    {
        $comments = [
            'chargeback' => __('A reversal has occurred on this transaction due to a chargeback by your customer.'),
            'guarantee' => __(
                'A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.'
            ),
            'buyer-complaint' => __(
                'A reversal has occurred on this transaction due to a complaint about the transaction from your customer.'
            ),
            'buyer_complaint' => __(
                'A reversal has occurred on this transaction due to a complaint about the transaction from your customer.'
            ),
            'refund' => __(
                'A reversal has occurred on this transaction because you have given the customer a refund.'
            ),
            'adjustment_reversal' => __('Reversal of an adjustment.'),
            'admin_fraud_reversal' => __('Transaction reversal due to fraud detected by PayPal administrators.'),
            'admin_reversal' => __('Transaction reversal by PayPal administrators.'),
            'chargeback_reimbursement' => __('Reimbursement for a chargeback.'),
            'chargeback_settlement' => __('Settlement of a chargeback.'),
            'unauthorized_spoof' => __(
                'A reversal has occurred on this transaction because of a customer dispute suspecting unauthorized spoof.'
            ),
            'non_receipt' => __('Buyer claims that he did not receive goods or service.'),
            'not_as_described' => __(
                'Buyer claims that the goods or service received differ from merchant’s description of the goods or service.'
            ),
            'unauthorized' => __('Buyer claims that he/she did not authorize transaction.'),
            'adjustment_reimburse' => __('A case that has been resolved and close requires a reimbursement.'),
            'duplicate' => __('Buyer claims that a possible duplicate payment was made to the merchant.'),
            'merchandise' => __('Buyer claims that the received merchandise is unsatisfactory, defective, or damaged.'),
        ];
        return isset($comments[$code])
            ? $comments[$code]
            : __('Unknown reason. Please contact PayPal customer service.');
    }

    /**
     * Whether a reversal/refund can be disputed with PayPal
     *
     * @param string $code
     * @return bool;
     * @since 2.0.0
     */
    public static function isReversalDisputable($code)
    {
        $listOfDisputeCodes = [
            'none' => true,
            'other' => true,
            'chargeback' => true,
            'buyer-complaint' => true,
            'adjustment_reversal' => true,
            'guarantee' => false,
            'refund' => false,
            'chargeback_reimbursement' => false,
            'chargeback_settlement' => false,
        ];
        return isset($listOfDisputeCodes[$code]) ? $listOfDisputeCodes[$code] : false;
    }

    /**
     * Render info item
     *
     * @param array $keys
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param bool $labelValuesOnly
     * @return array
     * @since 2.0.0
     */
    protected function _getFullInfo(array $keys, \Magento\Payment\Model\InfoInterface $payment, $labelValuesOnly)
    {
        $result = [];
        foreach ($keys as $key) {
            if (!isset($this->_paymentMapFull[$key])) {
                $this->_paymentMapFull[$key] = [];
            }
            if (!isset($this->_paymentMapFull[$key]['label'])) {
                if (!$payment->hasAdditionalInformation($key)) {
                    $this->_paymentMapFull[$key]['label'] = false;
                    $this->_paymentMapFull[$key]['value'] = false;
                } else {
                    $value = $payment->getAdditionalInformation($key);
                    $this->_paymentMapFull[$key]['label'] = (string)$this->_getLabel($key);
                    $this->_paymentMapFull[$key]['value'] = $this->_getValue($value, $key);
                }
            }
            if (!empty($this->_paymentMapFull[$key]['value'])) {
                if ($labelValuesOnly) {
                    $value = $this->_paymentMapFull[$key]['value'];
                    $value = is_array($value) ? array_map('__', $value) : __($value);
                    $result[$this->_paymentMapFull[$key]['label']] = $value;
                } else {
                    $result[$key] = $this->_paymentMapFull[$key];
                }
            }
        }
        return $result;
    }

    /**
     * Render info item labels
     *
     * @param string $key
     * @return string
     * @since 2.0.0
     */
    protected function _getLabel($key)
    {
        if (!isset($this->_labelCodesCache[self::ITEM_LABELS])) {
            $this->_labelCodesCache[self::ITEM_LABELS] = [
                self::PAYPAL_PAYER_ID => __('Payer ID'),
                self::PAYPAL_PAYER_EMAIL => __('Payer Email'),
                self::PAYPAL_PAYER_STATUS => __('Payer Status'),
                self::PAYPAL_ADDRESS_ID => __('Payer Address ID'),
                self::PAYPAL_ADDRESS_STATUS => __('Payer Address Status'),
                self::PAYPAL_PROTECTION_ELIGIBILITY => __('Merchant Protection Eligibility'),
                self::PAYPAL_FRAUD_FILTERS => __('Triggered Fraud Filters'),
                self::PAYPAL_CORRELATION_ID => __('Last Correlation ID'),
                self::PAYPAL_AVS_CODE => __('Address Verification System Response'),
                self::PAYPAL_CVV_2_MATCH => __('CVV2 Check Result by PayPal'),
                self::PAYPAL_CVV2MATCH => __('CVV2 Check Result by PayPal'),
                self::PAYPAL_AVSADDR => __('AVS Street Match'),
                self::PAYPAL_AVSZIP => __('AVS zip'),
                self::PAYPAL_IAVS => __('International AVS response'),
                self::BUYER_TAX_ID => __('Buyer\'s Tax ID'),
                self::BUYER_TAX_ID_TYPE => __('Buyer\'s Tax ID Type'),
            ];
        }
        return isset($this->_labelCodesCache[self::ITEM_LABELS][$key])
            ? $this->_labelCodesCache[self::ITEM_LABELS][$key]
            : '';
    }

    /**
     * Get case type label
     *
     * @param string $key
     * @return string
     * @since 2.0.0
     */
    public static function getCaseTypeLabel($key)
    {
        $labels = [
            'chargeback' => __('Chargeback'),
            'complaint' => __('Complaint'),
            'dispute' => __('Dispute'),
        ];
        $value = isset($labels[$key]) ? $labels[$key] : '';
        return $value;
    }

    /**
     * Apply a filter upon value getting
     *
     * @param string $value
     * @param string $key
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _getValue($value, $key)
    {
        $label = '';
        $outputValue = implode(', ', (array) $value);
        switch ($key) {
            case self::PAYPAL_IAVS:
            case self::PAYPAL_AVSZIP:
            case self::PAYPAL_AVSADDR:
            case self::PAYPAL_AVS_CODE:
                $label = $this->_getAvsLabel($outputValue);
                break;
            case self::PAYPAL_CVV2MATCH:
            case self::PAYPAL_CVV_2_MATCH:
                $label = $this->_getCvv2Label($outputValue);
                break;
            case self::PAYPAL_FRAUD_FILTERS:
                if (is_array($value)) {
                    return $value;
                }
                break;
            case self::BUYER_TAX_ID_TYPE:
                $outputValue = $this->_getBuyerIdTypeValue($outputValue);
                // fall-through intentional
            default:
                return $outputValue;
        }
        return sprintf('#%s%s', $outputValue, $outputValue == $label ? '' : ': ' . $label);
    }

    /**
     * Attempt to convert AVS check result code into label
     *
     * @param string $value
     * @return string
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_AVSResponseCodes
     * @since 2.0.0
     */
    protected function _getAvsLabel($value)
    {
        if (!isset($this->_labelCodesCache[self::PAYPAL_AVS_CODE])) {
            $this->_labelCodesCache[self::PAYPAL_AVS_CODE] = [
                'A' => __('Matched Address only (no ZIP)'), // Visa, MasterCard, Discover and American Express
                'B' => __('Matched Address only (no ZIP) International'), // international "A"
                'N' => __('No Details matched'),
                'C' => __('No Details matched. International'), // international "N"
                'X' => __('Exact Match.'),
                'D' => __('Exact Match. Address and Postal Code. International'), // international "X"
                'F' => __('Exact Match. Address and Postal Code. UK-specific'), // UK-specific "X"
                'E' => __('N/A. Not allowed for MOTO (Internet/Phone) transactions'),
                'G' => __('N/A. Global Unavailable'),
                'I' => __('N/A. International Unavailable'),
                'Z' => __('Matched five-digit ZIP only (no Address)'),
                'P' => __('Matched Postal Code only (no Address)'), // international "Z"
                'R' => __('N/A. Retry'),
                'S' => __('N/A. Service not Supported'),
                'U' => __('N/A. Unavailable'),
                'W' => __('Matched whole nine-digit ZIP (no Address)'),
                'Y' => __('Yes. Matched Address and five-digit ZIP'),
                '0' => __('All the address information matched'), // Maestro and Solo
                '1' => __('None of the address information matched'),
                '2' => __('Part of the address information matched'),
                '3' => __('N/A. The merchant did not provide AVS information'),
                '4' => __('N/A. Address not checked, or acquirer had no response. Service not available'),
            ];
        }
        return isset($this->_labelCodesCache[self::PAYPAL_AVS_CODE][$value])
            ? $this->_labelCodesCache[self::PAYPAL_AVS_CODE][$value]
            : $value;
    }

    /**
     * Attempt to convert CVV2 check result code into label
     *
     * @param string $value
     * @return string
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_AVSResponseCodes
     * @since 2.0.0
     */
    protected function _getCvv2Label($value)
    {
        if (!isset($this->_labelCodesCache[self::PAYPAL_CVV_2_MATCH])) {
            $this->_labelCodesCache[self::PAYPAL_CVV_2_MATCH] = [
                // Visa, MasterCard, Discover and American Express
                'M' => __('Matched (CVV2CSC)'),
                'N' => __('No match'),
                'P' => __('N/A. Not processed'),
                'S' => __('N/A. Service not supported'),
                'U' => __('N/A. Service not available'),
                'X' => __('N/A. No response'),
                'Y' => __('Matched (CVV2CSC)'),
                // Maestro and Solo
                '0' => __('Matched (CVV2)'),
                '1' => __('No match'),
                '2' => __('N/A. The merchant has not implemented CVV2 code handling'),
                '3' => __('N/A. Merchant has indicated that CVV2 is not present on card'),
                '4' => __('N/A. Service not available'),
            ];
        }
        return isset($this->_labelCodesCache[self::PAYPAL_CVV_2_MATCH][$value])
            ? $this->_labelCodesCache[self::PAYPAL_CVV_2_MATCH][$value]
            : $value;
    }

    /**
     * Retrieve buyer id type value based on code received from PayPal (Brazil only)
     *
     * @param string $code
     * @return string
     * @since 2.0.0
     */
    protected function _getBuyerIdTypeValue($code)
    {
        if (!isset($this->_labelCodesCache[self::BUYER_TAX_ID_TYPE])) {
            $this->_labelCodesCache[self::BUYER_TAX_ID_TYPE] = [
                self::BUYER_TAX_ID_TYPE_CNPJ => __('CNPJ'),
                self::BUYER_TAX_ID_TYPE_CPF => __('CPF'),
            ];
        }
        return isset($this->_labelCodesCache[self::BUYER_TAX_ID_TYPE][$code])
            ? $this->_labelCodesCache[self::BUYER_TAX_ID_TYPE][$code]
            : '';
    }
}
