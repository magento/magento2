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
namespace Magento\Paypal\Model\Report\Settlement;

/*
 * Model for report rows
 */
/**
 * @method \Magento\Paypal\Model\Resource\Report\Settlement\Row _getResource()
 * @method \Magento\Paypal\Model\Resource\Report\Settlement\Row getResource()
 * @method int getReportId()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setReportId(int $value)
 * @method string getTransactionId()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setTransactionId(string $value)
 * @method string getInvoiceId()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setInvoiceId(string $value)
 * @method string getPaypalReferenceId()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setPaypalReferenceId(string $value)
 * @method string getPaypalReferenceIdType()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setPaypalReferenceIdType(string $value)
 * @method string getTransactionEventCode()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setTransactionEventCode(string $value)
 * @method string getTransactionInitiationDate()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setTransactionInitiationDate(string $value)
 * @method string getTransactionCompletionDate()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setTransactionCompletionDate(string $value)
 * @method string getTransactionDebitOrCredit()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setTransactionDebitOrCredit(string $value)
 * @method float getGrossTransactionAmount()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setGrossTransactionAmount(float $value)
 * @method string getGrossTransactionCurrency()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setGrossTransactionCurrency(string $value)
 * @method string getFeeDebitOrCredit()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setFeeDebitOrCredit(string $value)
 * @method float getFeeAmount()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setFeeAmount(float $value)
 * @method string getFeeCurrency()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setFeeCurrency(string $value)
 * @method string getCustomField()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setCustomField(string $value)
 * @method string getConsumerId()
 * @method \Magento\Paypal\Model\Report\Settlement\Row setConsumerId(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Row extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Assoc array event code => label
     *
     * @var array
     */
    protected static $_eventList = array();

    /**
     * Casted amount keys registry
     *
     * @var array
     */
    protected $_castedAmounts = array();

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Paypal\Model\Resource\Report\Settlement\Row');
    }

    /**
     * Return description of Reference ID Type
     * If no code specified, return full list of codes with their description
     *
     * @param string|null $code
     * @return string|array
     */
    public function getReferenceType($code = null)
    {
        $types = array(
            'TXN' => __('Transaction ID'),
            'ODR' => __('Order ID'),
            'SUB' => __('Subscription ID'),
            'PAP' => __('Preapproved Payment ID')
        );
        if ($code === null) {
            asort($types);
            return $types;
        }
        if (isset($types[$code])) {
            return $types[$code];
        }
        return $code;
    }

    /**
     * Get native description for transaction code
     *
     * @param string $code
     * @return string
     */
    public function getTransactionEvent($code)
    {
        $this->_generateEventLabels();
        if (isset(self::$_eventList[$code])) {
            return self::$_eventList[$code];
        }
        return $code;
    }

    /**
     * Get full list of codes with their description
     *
     * @return &array
     */
    public function &getTransactionEvents()
    {
        $this->_generateEventLabels();
        return self::$_eventList;
    }

    /**
     * Return description of "Debit or Credit" value
     * If no code specified, return full list of codes with their description
     *
     * @param string|null $code
     * @return string|array
     */
    public function getDebitCreditText($code = null)
    {
        $options = array('CR' => __('Credit'), 'DR' => __('Debit'));
        if ($code === null) {
            return $options;
        }
        if (isset($options[$code])) {
            return $options[$code];
        }
        return $code;
    }

    /**
     * Invoke casting some amounts
     *
     * @param string $key
     * @param string|int|null $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        $this->_castAmount('fee_amount', 'fee_debit_or_credit');
        $this->_castAmount('gross_transaction_amount', 'transaction_debit_or_credit');
        return parent::getData($key, $index);
    }

    /**
     * Cast amounts of the specified keys
     *
     * PayPal settlement reports contain amounts in cents, hence the values need to be divided by 100
     * Also if the "credit" value is detected, it will be casted to negative amount
     *
     * @param string $key
     * @param string $creditKey
     * @return void
     */
    public function _castAmount($key, $creditKey)
    {
        if (isset($this->_castedAmounts[$key]) || !isset($this->_data[$key]) || !isset($this->_data[$creditKey])) {
            return;
        }
        if (empty($this->_data[$key])) {
            return;
        }
        $amount = $this->_data[$key] / 100;
        if ('CR' === $this->_data[$creditKey]) {
            $amount = -1 * $amount;
        }
        $this->_data[$key] = $amount;
        $this->_castedAmounts[$key] = true;
    }

    /**
     * Fill/translate and sort all event codes/labels
     *
     * @return void
     */
    protected function _generateEventLabels()
    {
        if (!self::$_eventList) {
            self::$_eventList = array(
                'T0000' => __('General: received payment of a type not belonging to the other T00xx categories'),
                'T0001' => __('Mass Pay Payment'),
                'T0002' => __('Subscription Payment, either payment sent or payment received'),
                'T0003' => __('Preapproved Payment (BillUser API), either sent or received'),
                'T0004' => __('eBay Auction Payment'),
                'T0005' => __('Direct Payment API'),
                'T0006' => __('Express Checkout APIs'),
                'T0007' => __('Website Payments Standard Payment'),
                'T0008' => __('Postage Payment to either USPS or UPS'),
                'T0009' => __('Gift Certificate Payment: purchase of Gift Certificate'),
                'T0010' => __('Auction Payment other than through eBay'),
                'T0011' => __('Mobile Payment (made via a mobile phone)'),
                'T0012' => __('Virtual Terminal Payment'),
                'T0100' => __('General: non-payment fee of a type not belonging to the other T01xx categories'),
                'T0101' => __('Fee: Web Site Payments Pro Account Monthly'),
                'T0102' => __('Fee: Foreign ACH Withdrawal'),
                'T0103' => __('Fee: WorldLink Check Withdrawal'),
                'T0104' => __('Fee: Mass Pay Request'),
                'T0200' => __('General Currency Conversion'),
                'T0201' => __('User-initiated Currency Conversion'),
                'T0202' => __('Currency Conversion required to cover negative balance'),
                'T0300' => __('General Funding of PayPal Account '),
                'T0301' => __('PayPal Balance Manager function of PayPal account'),
                'T0302' => __('ACH Funding for Funds Recovery from Account Balance'),
                'T0303' => __('EFT Funding (German banking)'),
                'T0400' => __('General Withdrawal from PayPal Account'),
                'T0401' => __('AutoSweep'),
                'T0500' => __('General: Use of PayPal account for purchasing as well as receiving payments'),
                'T0501' => __('Virtual PayPal Debit Card Transaction'),
                'T0502' => __('PayPal Debit Card Withdrawal from ATM'),
                'T0503' => __('Hidden Virtual PayPal Debit Card Transaction'),
                'T0504' => __('PayPal Debit Card Cash Advance'),
                'T0600' => __('General: Withdrawal from PayPal Account'),
                'T0700' => __('General (Purchase with a credit card)'),
                'T0701' => __('Negative Balance'),
                'T0800' => __('General: bonus of a type not belonging to the other T08xx categories'),
                'T0801' => __('Debit Card Cash Back'),
                'T0802' => __('Merchant Referral Bonus'),
                'T0803' => __('Balance Manager Account Bonus'),
                'T0804' => __('PayPal Buyer Warranty Bonus'),
                'T0805' => __('PayPal Protection Bonus'),
                'T0806' => __('Bonus for first ACH Use'),
                'T0900' => __('General Redemption'),
                'T0901' => __('Gift Certificate Redemption'),
                'T0902' => __('Points Incentive Redemption'),
                'T0903' => __('Coupon Redemption'),
                'T0904' => __('Reward Voucher Redemption'),
                'T1000' => __('General. Product no longer supported'),
                'T1100' => __('General: reversal of a type not belonging to the other T11xx categories'),
                'T1101' => __('ACH Withdrawal'),
                'T1102' => __('Debit Card Transaction'),
                'T1103' => __('Reversal of Points Usage'),
                'T1104' => __('ACH Deposit (Reversal)'),
                'T1105' => __('Reversal of General Account Hold'),
                'T1106' => __('Account-to-Account Payment, initiated by PayPal'),
                'T1107' => __('Payment Refund initiated by merchant'),
                'T1108' => __('Fee Reversal'),
                'T1110' => __('Hold for Dispute Investigation'),
                'T1111' => __('Reversal of hold for Dispute Investigation'),
                'T1200' => __('General: adjustment of a type not belonging to the other T12xx categories'),
                'T1201' => __('Chargeback'),
                'T1202' => __('Reversal'),
                'T1203' => __('Charge-off'),
                'T1204' => __('Incentive'),
                'T1205' => __('Reimbursement of Chargeback'),
                'T1300' => __('General (Authorization)'),
                'T1301' => __('Reauthorization'),
                'T1302' => __('Void'),
                'T1400' => __('General (Dividend)'),
                'T1500' => __('General: temporary hold of a type not belonging to the other T15xx categories'),
                'T1501' => __('Open Authorization'),
                'T1502' => __('ACH Deposit (Hold for Dispute or Other Investigation)'),
                'T1503' => __('Available Balance'),
                'T1600' => __('Funding'),
                'T1700' => __('General: Withdrawal to Non-Bank Entity'),
                'T1701' => __('WorldLink Withdrawal'),
                'T1800' => __('Buyer Credit Payment'),
                'T1900' => __('General Adjustment without businessrelated event'),
                'T2000' => __('General (Funds Transfer from PayPal Account to Another)'),
                'T2001' => __('Settlement Consolidation'),
                'T9900' => __('General: event not yet categorized')
            );
            asort(self::$_eventList);
        }
    }
}
