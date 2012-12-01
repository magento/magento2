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
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/*
 * Model for report rows
 */
/**
 * Enter description here ...
 *
 * @method Mage_Paypal_Model_Resource_Report_Settlement_Row _getResource()
 * @method Mage_Paypal_Model_Resource_Report_Settlement_Row getResource()
 * @method int getReportId()
 * @method Mage_Paypal_Model_Report_Settlement_Row setReportId(int $value)
 * @method string getTransactionId()
 * @method Mage_Paypal_Model_Report_Settlement_Row setTransactionId(string $value)
 * @method string getInvoiceId()
 * @method Mage_Paypal_Model_Report_Settlement_Row setInvoiceId(string $value)
 * @method string getPaypalReferenceId()
 * @method Mage_Paypal_Model_Report_Settlement_Row setPaypalReferenceId(string $value)
 * @method string getPaypalReferenceIdType()
 * @method Mage_Paypal_Model_Report_Settlement_Row setPaypalReferenceIdType(string $value)
 * @method string getTransactionEventCode()
 * @method Mage_Paypal_Model_Report_Settlement_Row setTransactionEventCode(string $value)
 * @method string getTransactionInitiationDate()
 * @method Mage_Paypal_Model_Report_Settlement_Row setTransactionInitiationDate(string $value)
 * @method string getTransactionCompletionDate()
 * @method Mage_Paypal_Model_Report_Settlement_Row setTransactionCompletionDate(string $value)
 * @method string getTransactionDebitOrCredit()
 * @method Mage_Paypal_Model_Report_Settlement_Row setTransactionDebitOrCredit(string $value)
 * @method float getGrossTransactionAmount()
 * @method Mage_Paypal_Model_Report_Settlement_Row setGrossTransactionAmount(float $value)
 * @method string getGrossTransactionCurrency()
 * @method Mage_Paypal_Model_Report_Settlement_Row setGrossTransactionCurrency(string $value)
 * @method string getFeeDebitOrCredit()
 * @method Mage_Paypal_Model_Report_Settlement_Row setFeeDebitOrCredit(string $value)
 * @method float getFeeAmount()
 * @method Mage_Paypal_Model_Report_Settlement_Row setFeeAmount(float $value)
 * @method string getFeeCurrency()
 * @method Mage_Paypal_Model_Report_Settlement_Row setFeeCurrency(string $value)
 * @method string getCustomField()
 * @method Mage_Paypal_Model_Report_Settlement_Row setCustomField(string $value)
 * @method string getConsumerId()
 * @method Mage_Paypal_Model_Report_Settlement_Row setConsumerId(string $value)
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Model_Report_Settlement_Row extends Mage_Core_Model_Abstract
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
     */
    protected function _construct()
    {
        $this->_init('Mage_Paypal_Model_Resource_Report_Settlement_Row');
    }

    /**
     * Return description of Reference ID Type
     * If no code specified, return full list of codes with their description
     *
     * @param string code
     * @return string|array
     */
    public function getReferenceType($code = null)
    {
        $types = array(
            'TXN' => Mage::helper('Mage_Paypal_Helper_Data')->__('Transaction ID'),
            'ODR' => Mage::helper('Mage_Paypal_Helper_Data')->__('Order ID'),
            'SUB' => Mage::helper('Mage_Paypal_Helper_Data')->__('Subscription ID'),
            'PAP' => Mage::helper('Mage_Paypal_Helper_Data')->__('Preapproved Payment ID')
        );
        if($code === null) {
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
     * @param string code
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
     * @param string code
     * @return string|array
     */
    public function getDebitCreditText($code = null)
    {
        $options = array(
            'CR' => Mage::helper('Mage_Paypal_Helper_Data')->__('Credit'),
            'DR' => Mage::helper('Mage_Paypal_Helper_Data')->__('Debit'),
        );
        if($code === null) {
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
     * @param mixed $key
     * @param mixed $index
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
     */
    protected function _generateEventLabels()
    {
        if (!self::$_eventList) {
            self::$_eventList = array(
            'T0000' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: received payment of a type not belonging to the other T00xx categories'),
            'T0001' => Mage::helper('Mage_Paypal_Helper_Data')->__('Mass Pay Payment'),
            'T0002' => Mage::helper('Mage_Paypal_Helper_Data')->__('Subscription Payment, either payment sent or payment received'),
            'T0003' => Mage::helper('Mage_Paypal_Helper_Data')->__('Preapproved Payment (BillUser API), either sent or received'),
            'T0004' => Mage::helper('Mage_Paypal_Helper_Data')->__('eBay Auction Payment'),
            'T0005' => Mage::helper('Mage_Paypal_Helper_Data')->__('Direct Payment API'),
            'T0006' => Mage::helper('Mage_Paypal_Helper_Data')->__('Express Checkout APIs'),
            'T0007' => Mage::helper('Mage_Paypal_Helper_Data')->__('Website Payments Standard Payment'),
            'T0008' => Mage::helper('Mage_Paypal_Helper_Data')->__('Postage Payment to either USPS or UPS'),
            'T0009' => Mage::helper('Mage_Paypal_Helper_Data')->__('Gift Certificate Payment: purchase of Gift Certificate'),
            'T0010' => Mage::helper('Mage_Paypal_Helper_Data')->__('Auction Payment other than through eBay'),
            'T0011' => Mage::helper('Mage_Paypal_Helper_Data')->__('Mobile Payment (made via a mobile phone)'),
            'T0012' => Mage::helper('Mage_Paypal_Helper_Data')->__('Virtual Terminal Payment'),
            'T0100' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: non-payment fee of a type not belonging to the other T01xx categories'),
            'T0101' => Mage::helper('Mage_Paypal_Helper_Data')->__('Fee: Web Site Payments Pro Account Monthly'),
            'T0102' => Mage::helper('Mage_Paypal_Helper_Data')->__('Fee: Foreign ACH Withdrawal'),
            'T0103' => Mage::helper('Mage_Paypal_Helper_Data')->__('Fee: WorldLink Check Withdrawal'),
            'T0104' => Mage::helper('Mage_Paypal_Helper_Data')->__('Fee: Mass Pay Request'),
            'T0200' => Mage::helper('Mage_Paypal_Helper_Data')->__('General Currency Conversion'),
            'T0201' => Mage::helper('Mage_Paypal_Helper_Data')->__('User-initiated Currency Conversion'),
            'T0202' => Mage::helper('Mage_Paypal_Helper_Data')->__('Currency Conversion required to cover negative balance'),
            'T0300' => Mage::helper('Mage_Paypal_Helper_Data')->__('General Funding of PayPal Account '),
            'T0301' => Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal Balance Manager function of PayPal account'),
            'T0302' => Mage::helper('Mage_Paypal_Helper_Data')->__('ACH Funding for Funds Recovery from Account Balance'),
            'T0303' => Mage::helper('Mage_Paypal_Helper_Data')->__('EFT Funding (German banking)'),
            'T0400' => Mage::helper('Mage_Paypal_Helper_Data')->__('General Withdrawal from PayPal Account'),
            'T0401' => Mage::helper('Mage_Paypal_Helper_Data')->__('AutoSweep'),
            'T0500' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: Use of PayPal account for purchasing as well as receiving payments'),
            'T0501' => Mage::helper('Mage_Paypal_Helper_Data')->__('Virtual PayPal Debit Card Transaction'),
            'T0502' => Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal Debit Card Withdrawal from ATM'),
            'T0503' => Mage::helper('Mage_Paypal_Helper_Data')->__('Hidden Virtual PayPal Debit Card Transaction'),
            'T0504' => Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal Debit Card Cash Advance'),
            'T0600' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: Withdrawal from PayPal Account'),
            'T0700' => Mage::helper('Mage_Paypal_Helper_Data')->__('General (Purchase with a credit card)'),
            'T0701' => Mage::helper('Mage_Paypal_Helper_Data')->__('Negative Balance'),
            'T0800' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: bonus of a type not belonging to the other T08xx categories'),
            'T0801' => Mage::helper('Mage_Paypal_Helper_Data')->__('Debit Card Cash Back'),
            'T0802' => Mage::helper('Mage_Paypal_Helper_Data')->__('Merchant Referral Bonus'),
            'T0803' => Mage::helper('Mage_Paypal_Helper_Data')->__('Balance Manager Account Bonus'),
            'T0804' => Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal Buyer Warranty Bonus'),
            'T0805' => Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal Protection Bonus'),
            'T0806' => Mage::helper('Mage_Paypal_Helper_Data')->__('Bonus for first ACH Use'),
            'T0900' => Mage::helper('Mage_Paypal_Helper_Data')->__('General Redemption'),
            'T0901' => Mage::helper('Mage_Paypal_Helper_Data')->__('Gift Certificate Redemption'),
            'T0902' => Mage::helper('Mage_Paypal_Helper_Data')->__('Points Incentive Redemption'),
            'T0903' => Mage::helper('Mage_Paypal_Helper_Data')->__('Coupon Redemption'),
            'T0904' => Mage::helper('Mage_Paypal_Helper_Data')->__('Reward Voucher Redemption'),
            'T1000' => Mage::helper('Mage_Paypal_Helper_Data')->__('General. Product no longer supported'),
            'T1100' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: reversal of a type not belonging to the other T11xx categories'),
            'T1101' => Mage::helper('Mage_Paypal_Helper_Data')->__('ACH Withdrawal'),
            'T1102' => Mage::helper('Mage_Paypal_Helper_Data')->__('Debit Card Transaction'),
            'T1103' => Mage::helper('Mage_Paypal_Helper_Data')->__('Reversal of Points Usage'),
            'T1104' => Mage::helper('Mage_Paypal_Helper_Data')->__('ACH Deposit (Reversal)'),
            'T1105' => Mage::helper('Mage_Paypal_Helper_Data')->__('Reversal of General Account Hold'),
            'T1106' => Mage::helper('Mage_Paypal_Helper_Data')->__('Account-to-Account Payment, initiated by PayPal'),
            'T1107' => Mage::helper('Mage_Paypal_Helper_Data')->__('Payment Refund initiated by merchant'),
            'T1108' => Mage::helper('Mage_Paypal_Helper_Data')->__('Fee Reversal'),
            'T1110' => Mage::helper('Mage_Paypal_Helper_Data')->__('Hold for Dispute Investigation'),
            'T1111' => Mage::helper('Mage_Paypal_Helper_Data')->__('Reversal of hold for Dispute Investigation'),
            'T1200' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: adjustment of a type not belonging to the other T12xx categories'),
            'T1201' => Mage::helper('Mage_Paypal_Helper_Data')->__('Chargeback'),
            'T1202' => Mage::helper('Mage_Paypal_Helper_Data')->__('Reversal'),
            'T1203' => Mage::helper('Mage_Paypal_Helper_Data')->__('Charge-off'),
            'T1204' => Mage::helper('Mage_Paypal_Helper_Data')->__('Incentive'),
            'T1205' => Mage::helper('Mage_Paypal_Helper_Data')->__('Reimbursement of Chargeback'),
            'T1300' => Mage::helper('Mage_Paypal_Helper_Data')->__('General (Authorization)'),
            'T1301' => Mage::helper('Mage_Paypal_Helper_Data')->__('Reauthorization'),
            'T1302' => Mage::helper('Mage_Paypal_Helper_Data')->__('Void'),
            'T1400' => Mage::helper('Mage_Paypal_Helper_Data')->__('General (Dividend)'),
            'T1500' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: temporary hold of a type not belonging to the other T15xx categories'),
            'T1501' => Mage::helper('Mage_Paypal_Helper_Data')->__('Open Authorization'),
            'T1502' => Mage::helper('Mage_Paypal_Helper_Data')->__('ACH Deposit (Hold for Dispute or Other Investigation)'),
            'T1503' => Mage::helper('Mage_Paypal_Helper_Data')->__('Available Balance'),
            'T1600' => Mage::helper('Mage_Paypal_Helper_Data')->__('Funding'),
            'T1700' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: Withdrawal to Non-Bank Entity'),
            'T1701' => Mage::helper('Mage_Paypal_Helper_Data')->__('WorldLink Withdrawal'),
            'T1800' => Mage::helper('Mage_Paypal_Helper_Data')->__('Buyer Credit Payment'),
            'T1900' => Mage::helper('Mage_Paypal_Helper_Data')->__('General Adjustment without businessrelated event'),
            'T2000' => Mage::helper('Mage_Paypal_Helper_Data')->__('General (Funds Transfer from PayPal Account to Another)'),
            'T2001' => Mage::helper('Mage_Paypal_Helper_Data')->__('Settlement Consolidation'),
            'T9900' => Mage::helper('Mage_Paypal_Helper_Data')->__('General: event not yet categorized'),
            );
            asort(self::$_eventList);
        }
    }
}
