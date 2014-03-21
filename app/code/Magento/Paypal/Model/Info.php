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
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model;

/**
 * PayPal payment information model
 *
 * Aware of all PayPal payment methods
 * Collects and provides access to PayPal-specific payment data
 * Provides business logic information about payment flow
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

    const CVV2_MATCH = 'cvv2_check_result';

    const CENTINEL_VPAS = 'centinel_vpas_result';

    const CENTINEL_ECI = 'centinel_eci_result';

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
     */
    protected $_paymentMap = array(
        self::PAYER_ID => 'paypal_payer_id',
        self::PAYER_EMAIL => 'paypal_payer_email',
        self::PAYER_STATUS => 'paypal_payer_status',
        self::ADDRESS_ID => 'paypal_address_id',
        self::ADDRESS_STATUS => 'paypal_address_status',
        self::PROTECTION_EL => 'paypal_protection_eligibility',
        self::FRAUD_FILTERS => 'paypal_fraud_filters',
        self::CORRELATION_ID => 'paypal_correlation_id',
        self::AVS_CODE => 'paypal_avs_code',
        self::CVV2_MATCH => 'paypal_cvv2_match',
        self::CENTINEL_VPAS => self::CENTINEL_VPAS,
        self::CENTINEL_ECI => self::CENTINEL_ECI,
        self::BUYER_TAX_ID => self::BUYER_TAX_ID,
        self::BUYER_TAX_ID_TYPE => self::BUYER_TAX_ID_TYPE
    );

    /**
     * System information map
     *
     * @var array
     */
    protected $_systemMap = array(
        self::PAYMENT_STATUS => self::PAYMENT_STATUS_GLOBAL,
        self::PENDING_REASON => self::PENDING_REASON_GLOBAL,
        self::IS_FRAUD => self::IS_FRAUD_GLOBAL
    );

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
     */
    protected $_paymentPublicMap = array('paypal_payer_email', self::BUYER_TAX_ID, self::BUYER_TAX_ID_TYPE);

    /**
     * Rendered payment map cache
     *
     * @var array
     */
    protected $_paymentMapFull = array();

    /**
     * All available payment info getter
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    public function getPaymentInfo(\Magento\Payment\Model\Info $payment, $labelValuesOnly = false)
    {
        // collect paypal-specific info
        $result = $this->_getFullInfo(array_values($this->_paymentMap), $payment, $labelValuesOnly);

        // add last_trans_id
        $label = __('Last Transaction ID');
        $value = $payment->getLastTransId();
        if ($labelValuesOnly) {
            $result[$label] = $value;
        } else {
            $result['last_trans_id'] = array('label' => $label, 'value' => $value);
        }

        return $result;
    }

    /**
     * Public payment info getter
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    public function getPublicPaymentInfo(\Magento\Payment\Model\Info $payment, $labelValuesOnly = false)
    {
        return $this->_getFullInfo($this->_paymentPublicMap, $payment, $labelValuesOnly);
    }

    /**
     * Grab data from source and map it into payment
     *
     * @param array|\Magento\Object|callback $from
     * @param \Magento\Payment\Model\Info $payment
     * @return void
     */
    public function importToPayment($from, \Magento\Payment\Model\Info $payment)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        if (is_object($from)) {
            $from = array($from, 'getDataUsingMethod');
        }
        \Magento\Object\Mapper::accumulateByMap($from, array($payment, 'setAdditionalInformation'), $fullMap);
    }

    /**
     * Grab data from payment and map it into target
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param array|\Magento\Object|callback $to
     * @param array|null $map
     * @return array|\Magento\Object
     */
    public function &exportFromPayment(\Magento\Payment\Model\Info $payment, $to, array $map = null)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        \Magento\Object\Mapper::accumulateByMap(
            array($payment, 'getAdditionalInformation'),
            $to,
            $map ? $map : array_flip($fullMap)
        );
        return $to;
    }

    /**
     * Check whether the payment is in review state
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public static function isPaymentReviewRequired(\Magento\Payment\Model\Info $payment)
    {
        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        if (self::PAYMENTSTATUS_PENDING === $paymentStatus) {
            $pendingReason = $payment->getAdditionalInformation(self::PENDING_REASON_GLOBAL);
            return !in_array($pendingReason, array('authorization', 'order'));
        }
        return false;
    }

    /**
     * Check whether fraud order review detected and can be reviewed
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public static function isFraudReviewAllowed(\Magento\Payment\Model\Info $payment)
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
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public static function isPaymentCompleted(\Magento\Payment\Model\Info $payment)
    {
        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        return self::PAYMENTSTATUS_COMPLETED === $paymentStatus;
    }

    /**
     * Check whether the payment was processed successfully
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public static function isPaymentSuccessful(\Magento\Payment\Model\Info $payment)
    {
        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        if (in_array(
            $paymentStatus,
            array(
                self::PAYMENTSTATUS_COMPLETED,
                self::PAYMENTSTATUS_INPROGRESS,
                self::PAYMENTSTATUS_REFUNDED,
                self::PAYMENTSTATUS_REFUNDEDPART,
                self::PAYMENTSTATUS_UNREVERSED,
                self::PAYMENTSTATUS_PROCESSED
            )
        )
        ) {
            return true;
        }
        $pendingReason = $payment->getAdditionalInformation(self::PENDING_REASON_GLOBAL);
        return self::PAYMENTSTATUS_PENDING === $paymentStatus && in_array(
            $pendingReason,
            array('authorization', 'order')
        );
    }

    /**
     * Check whether the payment was processed unsuccessfully or failed
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public static function isPaymentFailed(\Magento\Payment\Model\Info $payment)
    {
        $paymentStatus = $payment->getAdditionalInformation(self::PAYMENT_STATUS_GLOBAL);
        return in_array(
            $paymentStatus,
            array(
                self::PAYMENTSTATUS_DENIED,
                self::PAYMENTSTATUS_EXPIRED,
                self::PAYMENTSTATUS_FAILED,
                self::PAYMENTSTATUS_REVERSED,
                self::PAYMENTSTATUS_VOIDED
            )
        );
    }

    /**
     * Explain pending payment reason code
     *
     * @param string $code
     * @return string
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetTransactionDetails
     */
    public static function explainPendingReason($code)
    {
        switch ($code) {
            case 'address':
                return __('This customer did not include a confirmed address.');
            case 'authorization':
            case 'order':
                return __('The payment is authorized but not settled.');
            case 'echeck':
                return __('The payment eCheck is not yet cleared.');
            case 'intl':
                return __('The merchant holds a non-U.S. account and does not have a withdrawal mechanism.');
            case 'multi-currency':
                // break is intentionally omitted
            case 'multi_currency':
                // break is intentionally omitted
            case 'multicurrency':
                return __('The payment currency does not match any of the merchant\'s balances currency.');
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
     */
    public static function explainReasonCode($code)
    {
        $comments = array(
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
            'merchandise' => __('Buyer claims that the received merchandise is unsatisfactory, defective, or damaged.')
        );
        $value = array_key_exists(
            $code,
            $comments
        ) && !empty($comments[$code]) ? $comments[$code] : __(
            'Unknown reason. Please contact PayPal customer service.'
        );
        return $value;
    }

    /**
     * Whether a reversal/refund can be disputed with PayPal
     *
     * @param string $code
     * @return bool;
     */
    public static function isReversalDisputable($code)
    {
        switch ($code) {
            case 'none':
            case 'other':
            case 'chargeback':
            case 'buyer-complaint':
            case 'buyer_complaint':
            case 'adjustment_reversal':
                return true;
            case 'guarantee':
            case 'refund':
            case 'chargeback_reimbursement':
            case 'chargeback_settlement':
            default:
                return false;
        }
    }

    /**
     * Render info item
     *
     * @param array $keys
     * @param \Magento\Payment\Model\Info $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    protected function _getFullInfo(array $keys, \Magento\Payment\Model\Info $payment, $labelValuesOnly)
    {
        $result = array();
        foreach ($keys as $key) {
            if (!isset($this->_paymentMapFull[$key])) {
                $this->_paymentMapFull[$key] = array();
            }
            if (!isset($this->_paymentMapFull[$key]['label'])) {
                if (!$payment->hasAdditionalInformation($key)) {
                    $this->_paymentMapFull[$key]['label'] = false;
                    $this->_paymentMapFull[$key]['value'] = false;
                } else {
                    $value = $payment->getAdditionalInformation($key);
                    $this->_paymentMapFull[$key]['label'] = $this->_getLabel($key);
                    $this->_paymentMapFull[$key]['value'] = $this->_getValue($value, $key);
                }
            }
            if (!empty($this->_paymentMapFull[$key]['value'])) {
                if ($labelValuesOnly) {
                    $result[$this->_paymentMapFull[$key]['label']] = $this->_paymentMapFull[$key]['value'];
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
     */
    protected function _getLabel($key)
    {
        switch ($key) {
            case 'paypal_payer_id':
                return __('Payer ID');
            case 'paypal_payer_email':
                return __('Payer Email');
            case 'paypal_payer_status':
                return __('Payer Status');
            case 'paypal_address_id':
                return __('Payer Address ID');
            case 'paypal_address_status':
                return __('Payer Address Status');
            case 'paypal_protection_eligibility':
                return __('Merchant Protection Eligibility');
            case 'paypal_fraud_filters':
                return __('Triggered Fraud Filters');
            case 'paypal_correlation_id':
                return __('Last Correlation ID');
            case 'paypal_avs_code':
                return __('Address Verification System Response');
            case 'paypal_cvv2_match':
                return __('CVV2 Check Result by PayPal');
            case self::BUYER_TAX_ID:
                return __('Buyer\'s Tax ID');
            case self::BUYER_TAX_ID_TYPE:
                return __('Buyer\'s Tax ID Type');
            case self::CENTINEL_VPAS:
                return __('PayPal/Centinel Visa Payer Authentication Service Result');
            case self::CENTINEL_ECI:
                return __('PayPal/Centinel Electronic Commerce Indicator');
        }
        return '';
    }

    /**
     * Get case type label
     *
     * @param string $key
     * @return string
     */
    public static function getCaseTypeLabel($key)
    {
        $labels = array('chargeback' => __('Chargeback'), 'complaint' => __('Complaint'), 'dispute' => __('Dispute'));
        $value = array_key_exists($key, $labels) && !empty($labels[$key]) ? $labels[$key] : '';
        return $value;
    }

    /**
     * Apply a filter upon value getting
     *
     * @param string $value
     * @param string $key
     * @return string
     */
    protected function _getValue($value, $key)
    {
        $label = '';
        switch ($key) {
            case 'paypal_avs_code':
                $label = $this->_getAvsLabel($value);
                break;
            case 'paypal_cvv2_match':
                $label = $this->_getCvv2Label($value);
                break;
            case self::CENTINEL_VPAS:
                $label = $this->_getCentinelVpasLabel($value);
                break;
            case self::CENTINEL_ECI:
                $label = $this->_getCentinelEciLabel($value);
                break;
            case self::BUYER_TAX_ID_TYPE:
                $value = $this->_getBuyerIdTypeValue($value);
                // fall-through intentional
            default:
                return $value;
        }
        return sprintf('#%s%s', $value, $value == $label ? '' : ': ' . $label);
    }

    /**
     * Attempt to convert AVS check result code into label
     *
     * @param string $value
     * @return string
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_AVSResponseCodes
     */
    protected function _getAvsLabel($value)
    {
        switch ($value) {
            // Visa, MasterCard, Discover and American Express
            case 'A':
            case 'YN':
                return __('Matched Address only (no ZIP)');
            case 'B':
                // international "A"
                return __('Matched Address only (no ZIP) International');
            case 'N':
                return __('No Details matched');
            case 'C':
                // international "N"
                return __('No Details matched. International');
            case 'X':
                return __('Exact Match. Address and nine-digit ZIP code');
            case 'D':
                // international "X"
                return __('Exact Match. Address and Postal Code. International');
            case 'F':
                // UK-specific "X"
                return __('Exact Match. Address and Postal Code. UK-specific');
            case 'E':
                return __('N/A. Not allowed for MOTO (Internet/Phone) transactions');
            case 'G':
                return __('N/A. Global Unavailable');
            case 'I':
                return __('N/A. International Unavailable');
            case 'Z':
            case 'NY':
                return __('Matched five-digit ZIP only (no Address)');
            case 'P':
                // international "Z"
            case 'NY':
                return __('Matched Postal Code only (no Address)');
            case 'R':
                return __('N/A. Retry');
            case 'S':
                return __('N/A. Service not Supported');
            case 'U':
                return __('N/A. Unavailable');
            case 'W':
                return __('Matched whole nine-didgit ZIP (no Address)');
            case 'Y':
                return __('Yes. Matched Address and five-didgit ZIP');
                // Maestro and Solo
            case '0':
                return __('All the address information matched');
            case '1':
                return __('None of the address information matched');
            case '2':
                return __('Part of the address information matched');
            case '3':
                return __('N/A. The merchant did not provide AVS information');
            case '4':
                return __('N/A. Address not checked, or acquirer had no response. Service not available');
            default:
                return $value;
        }
    }

    /**
     * Attempt to convert CVV2 check result code into label
     *
     * @param string $value
     * @return string
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_AVSResponseCodes
     */
    protected function _getCvv2Label($value)
    {
        switch ($value) {
            // Visa, MasterCard, Discover and American Express
            case 'M':
                return __('Matched (CVV2CSC)');
            case 'N':
                return __('No match');
            case 'P':
                return __('N/A. Not processed');
            case 'S':
                return __('N/A. Service not supported');
            case 'U':
                return __('N/A. Service not available');
            case 'X':
                return __('N/A. No response');
                // Maestro and Solo
            case '0':
                return __('Matched (CVV2)');
            case '1':
                return __('No match');
            case '2':
                return __('N/A. The merchant has not implemented CVV2 code handling');
            case '3':
                return __('N/A. Merchant has indicated that CVV2 is not present on card');
            case '4':
                return __('N/A. Service not available');
            default:
                return $value;
        }
    }

    /**
     * Attempt to convert centinel VPAS result into label
     *
     * @param string $value
     * @return string
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoDirectPayment
     */
    private function _getCentinelVpasLabel($value)
    {
        switch ($value) {
            case '2':
            case 'D':
                return __('Authenticated, Good Result');
            case '1':
                return __('Authenticated, Bad Result');
            case '3':
            case '6':
            case '8':
            case 'A':
            case 'C':
                return __('Attempted Authentication, Good Result');
            case '4':
            case '7':
            case '9':
                return __('Attempted Authentication, Bad Result');
            case '':
            case '0':
            case 'B':
                return __('No Liability Shift');
            default:
                return $value;
        }
    }

    /**
     * Attempt to convert centinel ECI result into label
     *
     * @param string $value
     * @return string
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoDirectPayment
     */
    private function _getCentinelEciLabel($value)
    {
        switch ($value) {
            case '01':
            case '07':
                return __('Merchant Liability');
            case '02':
            case '05':
            case '06':
                return __('Issuer Liability');
            default:
                return $value;
        }
    }

    /**
     * Retrieve buyer id type value based on code received from PayPal (Brazil only)
     *
     * @param string $code
     * @return string
     */
    protected function _getBuyerIdTypeValue($code)
    {
        $value = '';
        switch ($code) {
            case self::BUYER_TAX_ID_TYPE_CNPJ:
                $value = __('CNPJ');
                break;
            case self::BUYER_TAX_ID_TYPE_CPF:
                $value = __('CPF');
                break;
        }
        return $value;
    }
}
