<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Report\Settlement;

/*
 * Model for report rows
 */
/**
 * @method \Magento\Paypal\Model\ResourceModel\Report\Settlement\Row _getResource()
 * @method \Magento\Paypal\Model\ResourceModel\Report\Settlement\Row getResource()
 * @method int getReportId()
 * @method Row setReportId(int $value)
 * @method string getTransactionId()
 * @method Row setTransactionId(string $value)
 * @method string getInvoiceId()
 * @method Row setInvoiceId(string $value)
 * @method string getPaypalReferenceId()
 * @method Row setPaypalReferenceId(string $value)
 * @method string getPaypalReferenceIdType()
 * @method Row setPaypalReferenceIdType(string $value)
 * @method string getTransactionEventCode()
 * @method Row setTransactionEventCode(string $value)
 * @method string getTransactionInitiationDate()
 * @method Row setTransactionInitiationDate(string $value)
 * @method string getTransactionCompletionDate()
 * @method Row setTransactionCompletionDate(string $value)
 * @method string getTransactionDebitOrCredit()
 * @method Row setTransactionDebitOrCredit(string $value)
 * @method float getGrossTransactionAmount()
 * @method Row setGrossTransactionAmount(float $value)
 * @method string getGrossTransactionCurrency()
 * @method Row setGrossTransactionCurrency(string $value)
 * @method string getFeeDebitOrCredit()
 * @method Row setFeeDebitOrCredit(string $value)
 * @method float getFeeAmount()
 * @method Row setFeeAmount(float $value)
 * @method string getFeeCurrency()
 * @method Row setFeeCurrency(string $value)
 * @method string getCustomField()
 * @method Row setCustomField(string $value)
 * @method string getConsumerId()
 * @method Row setConsumerId(string $value)
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
    private $eventLabelsList = [];

    /**
     * Cast amount relation
     *
     * @var array
     */
    private $castAmountRelation = [
        'fee_amount' => 'fee_debit_or_credit',
        'gross_transaction_amount' => 'transaction_debit_or_credit',
    ];

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Paypal\Model\ResourceModel\Report\Settlement\Row');
    }

    /**
     * Return description of Reference ID Type
     * If no code specified, return full list of codes with their description
     *
     * @param string $code
     * @return string
     */
    public function getReferenceType($code)
    {
        $types = [
            'ODR' => __('Order ID'),
            'PAP' => __('Preapproved Payment ID'),
            'TXN' => __('Transaction ID'),
            'SUB' => __('Subscription ID'),
        ];
        return !empty($types[$code]) ? $types[$code] : $code;
    }

    /**
     * Get native description for transaction code
     *
     * @param string $code
     * @return string
     */
    public function getTransactionEvent($code)
    {
        $events = $this->getTransactionEvents();

        return !empty($events[$code]) ? $events[$code] : $code;
    }

    /**
     * Return description of "Debit or Credit" value
     *
     * @param string $code
     * @return string
     */
    public function getDebitCreditText($code)
    {
        $options = ['CR' => __('Credit'), 'DR' => __('Debit')];

        return !empty($options[$code]) ? $options[$code] : $code;
    }

    /**
     * Cast amounts of the specified keys
     *
     * If the "credit" value is detected, it will be casted to negative amount
     *
     * @param string $key
     * @return float|null
     */
    public function getCastedAmount($key)
    {
        if (empty($this->castAmountRelation[$key])) {
            return null;
        }
        if (empty($this->_data[$key]) || empty($this->_data[$this->castAmountRelation[$key]])) {
            return null;
        }

        $amount = $this->_data[$key];
        if ('CR' == $this->_data[$this->castAmountRelation[$key]]) {
            $amount = -1 * $amount;
        }
        return $amount;
    }

    /**
     * Get full list of codes with their description
     *
     * @return array
     */
    public function getTransactionEvents()
    {
        if (empty($this->eventLabelsList)) {
            $this->eventLabelsList = [
                'T1502' => __('ACH Deposit (Hold for Dispute or Other Investigation)'),
                'T1104' => __('ACH Deposit (Reversal)'),
                'T0302' => __('ACH Funding for Funds Recovery from Account Balance'),
                'T1101' => __('ACH Withdrawal'),
                'T1106' => __('Account-to-Account Payment, initiated by PayPal'),
                'T0010' => __('Auction Payment other than through eBay'),
                'T0401' => __('AutoSweep'),
                'T1503' => __('Available Balance'),
                'T0803' => __('Balance Manager Account Bonus'),
                'T0806' => __('Bonus for first ACH Use'),
                'T1800' => __('Buyer Credit Payment'),
                'T1203' => __('Charge-off'),
                'T1201' => __('Chargeback'),
                'T0903' => __('Coupon Redemption'),
                'T0202' => __('Currency Conversion required to cover negative balance'),
                'T0801' => __('Debit Card Cash Back'),
                'T1102' => __('Debit Card Transaction'),
                'T0005' => __('Direct Payment API'),
                'T0303' => __('EFT Funding (German banking)'),
                'T0006' => __('Express Checkout APIs'),
                'T1108' => __('Fee Reversal'),
                'T0102' => __('Fee: Foreign ACH Withdrawal'),
                'T0104' => __('Fee: Mass Pay Request'),
                'T0101' => __('Fee: Web Site Payments Pro Account Monthly'),
                'T0103' => __('Fee: WorldLink Check Withdrawal'),
                'T1600' => __('Funding'),
                'T1300' => __('General (Authorization)'),
                'T1400' => __('General (Dividend)'),
                'T2000' => __('General (Funds Transfer from PayPal Account to Another)'),
                'T0700' => __('General (Purchase with a credit card)'),
                'T1900' => __('General Adjustment without businessrelated event'),
                'T0200' => __('General Currency Conversion'),
                'T0300' => __('General Funding of PayPal Account '),
                'T0900' => __('General Redemption'),
                'T0400' => __('General Withdrawal from PayPal Account'),
                'T1000' => __('General. Product no longer supported'),
                'T0500' => __('General: Use of PayPal account for purchasing as well as receiving payments'),
                'T0600' => __('General: Withdrawal from PayPal Account'),
                'T1700' => __('General: Withdrawal to Non-Bank Entity'),
                'T1200' => __('General: adjustment of a type not belonging to the other T12xx categories'),
                'T0800' => __('General: bonus of a type not belonging to the other T08xx categories'),
                'T9900' => __('General: event not yet categorized'),
                'T0100' => __('General: non-payment fee of a type not belonging to the other T01xx categories'),
                'T0000' => __('General: received payment of a type not belonging to the other T00xx categories'),
                'T1100' => __('General: reversal of a type not belonging to the other T11xx categories'),
                'T1500' => __('General: temporary hold of a type not belonging to the other T15xx categories'),
                'T0009' => __('Gift Certificate Payment: purchase of Gift Certificate'),
                'T0901' => __('Gift Certificate Redemption'),
                'T0503' => __('Hidden Virtual PayPal Debit Card Transaction'),
                'T1110' => __('Hold for Dispute Investigation'),
                'T1204' => __('Incentive'),
                'T0001' => __('Mass Pay Payment'),
                'T0802' => __('Merchant Referral Bonus'),
                'T0011' => __('Mobile Payment (made via a mobile phone)'),
                'T0701' => __('Negative Balance'),
                'T1501' => __('Open Authorization'),
                'T0301' => __('PayPal Balance Manager function of PayPal account'),
                'T0804' => __('PayPal Buyer Warranty Bonus'),
                'T0504' => __('PayPal Debit Card Cash Advance'),
                'T0502' => __('PayPal Debit Card Withdrawal from ATM'),
                'T0805' => __('PayPal Protection Bonus'),
                'T1107' => __('Payment Refund initiated by merchant'),
                'T0902' => __('Points Incentive Redemption'),
                'T0008' => __('Postage Payment to either USPS or UPS'),
                'T0003' => __('Preapproved Payment (BillUser API, either sent or received'),
                'T1301' => __('Reauthorization'),
                'T1205' => __('Reimbursement of Chargeback'),
                'T1202' => __('Reversal'),
                'T1105' => __('Reversal of General Account Hold'),
                'T1103' => __('Reversal of Points Usage'),
                'T1111' => __('Reversal of hold for Dispute Investigation'),
                'T0904' => __('Reward Voucher Redemption'),
                'T2001' => __('Settlement Consolidation'),
                'T0002' => __('Subscription Payment, either payment sent or payment received'),
                'T0201' => __('User-initiated Currency Conversion'),
                'T0501' => __('Virtual PayPal Debit Card Transaction'),
                'T0012' => __('Virtual Terminal Payment'),
                'T1302' => __('Void'),
                'T1701' => __('WorldLink Withdrawal'),
                'T0004' => __('eBay Auction Payment'),
            ];
        }

        return $this->eventLabelsList;
    }
}
