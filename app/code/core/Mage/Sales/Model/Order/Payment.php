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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order payment information
 *
 * @method Mage_Sales_Model_Resource_Order_Payment _getResource()
 * @method Mage_Sales_Model_Resource_Order_Payment getResource()
 * @method int getParentId()
 * @method Mage_Sales_Model_Order_Payment setParentId(int $value)
 * @method float getBaseShippingCaptured()
 * @method Mage_Sales_Model_Order_Payment setBaseShippingCaptured(float $value)
 * @method float getShippingCaptured()
 * @method Mage_Sales_Model_Order_Payment setShippingCaptured(float $value)
 * @method float getAmountRefunded()
 * @method Mage_Sales_Model_Order_Payment setAmountRefunded(float $value)
 * @method float getBaseAmountPaid()
 * @method Mage_Sales_Model_Order_Payment setBaseAmountPaid(float $value)
 * @method float getAmountCanceled()
 * @method Mage_Sales_Model_Order_Payment setAmountCanceled(float $value)
 * @method float getBaseAmountAuthorized()
 * @method Mage_Sales_Model_Order_Payment setBaseAmountAuthorized(float $value)
 * @method float getBaseAmountPaidOnline()
 * @method Mage_Sales_Model_Order_Payment setBaseAmountPaidOnline(float $value)
 * @method float getBaseAmountRefundedOnline()
 * @method Mage_Sales_Model_Order_Payment setBaseAmountRefundedOnline(float $value)
 * @method float getBaseShippingAmount()
 * @method Mage_Sales_Model_Order_Payment setBaseShippingAmount(float $value)
 * @method float getShippingAmount()
 * @method Mage_Sales_Model_Order_Payment setShippingAmount(float $value)
 * @method float getAmountPaid()
 * @method Mage_Sales_Model_Order_Payment setAmountPaid(float $value)
 * @method float getAmountAuthorized()
 * @method Mage_Sales_Model_Order_Payment setAmountAuthorized(float $value)
 * @method float getBaseAmountOrdered()
 * @method Mage_Sales_Model_Order_Payment setBaseAmountOrdered(float $value)
 * @method float getBaseShippingRefunded()
 * @method Mage_Sales_Model_Order_Payment setBaseShippingRefunded(float $value)
 * @method float getShippingRefunded()
 * @method Mage_Sales_Model_Order_Payment setShippingRefunded(float $value)
 * @method float getBaseAmountRefunded()
 * @method Mage_Sales_Model_Order_Payment setBaseAmountRefunded(float $value)
 * @method float getAmountOrdered()
 * @method Mage_Sales_Model_Order_Payment setAmountOrdered(float $value)
 * @method float getBaseAmountCanceled()
 * @method Mage_Sales_Model_Order_Payment setBaseAmountCanceled(float $value)
 * @method int getQuotePaymentId()
 * @method Mage_Sales_Model_Order_Payment setQuotePaymentId(int $value)
 * @method string getAdditionalData()
 * @method Mage_Sales_Model_Order_Payment setAdditionalData(string $value)
 * @method string getCcExpMonth()
 * @method Mage_Sales_Model_Order_Payment setCcExpMonth(string $value)
 * @method string getCcSsStartYear()
 * @method Mage_Sales_Model_Order_Payment setCcSsStartYear(string $value)
 * @method string getEcheckBankName()
 * @method Mage_Sales_Model_Order_Payment setEcheckBankName(string $value)
 * @method string getMethod()
 * @method Mage_Sales_Model_Order_Payment setMethod(string $value)
 * @method string getCcDebugRequestBody()
 * @method Mage_Sales_Model_Order_Payment setCcDebugRequestBody(string $value)
 * @method string getCcSecureVerify()
 * @method Mage_Sales_Model_Order_Payment setCcSecureVerify(string $value)
 * @method string getProtectionEligibility()
 * @method Mage_Sales_Model_Order_Payment setProtectionEligibility(string $value)
 * @method string getCcApproval()
 * @method Mage_Sales_Model_Order_Payment setCcApproval(string $value)
 * @method string getCcLast4()
 * @method Mage_Sales_Model_Order_Payment setCcLast4(string $value)
 * @method string getCcStatusDescription()
 * @method Mage_Sales_Model_Order_Payment setCcStatusDescription(string $value)
 * @method string getEcheckType()
 * @method Mage_Sales_Model_Order_Payment setEcheckType(string $value)
 * @method string getCcDebugResponseSerialized()
 * @method Mage_Sales_Model_Order_Payment setCcDebugResponseSerialized(string $value)
 * @method string getCcSsStartMonth()
 * @method Mage_Sales_Model_Order_Payment setCcSsStartMonth(string $value)
 * @method string getEcheckAccountType()
 * @method Mage_Sales_Model_Order_Payment setEcheckAccountType(string $value)
 * @method string getLastTransId()
 * @method Mage_Sales_Model_Order_Payment setLastTransId(string $value)
 * @method string getCcCidStatus()
 * @method Mage_Sales_Model_Order_Payment setCcCidStatus(string $value)
 * @method string getCcOwner()
 * @method Mage_Sales_Model_Order_Payment setCcOwner(string $value)
 * @method string getCcType()
 * @method Mage_Sales_Model_Order_Payment setCcType(string $value)
 * @method string getPoNumber()
 * @method Mage_Sales_Model_Order_Payment setPoNumber(string $value)
 * @method string getCcExpYear()
 * @method Mage_Sales_Model_Order_Payment setCcExpYear(string $value)
 * @method string getCcStatus()
 * @method Mage_Sales_Model_Order_Payment setCcStatus(string $value)
 * @method string getEcheckRoutingNumber()
 * @method Mage_Sales_Model_Order_Payment setEcheckRoutingNumber(string $value)
 * @method string getAccountStatus()
 * @method Mage_Sales_Model_Order_Payment setAccountStatus(string $value)
 * @method string getAnetTransMethod()
 * @method Mage_Sales_Model_Order_Payment setAnetTransMethod(string $value)
 * @method string getCcDebugResponseBody()
 * @method Mage_Sales_Model_Order_Payment setCcDebugResponseBody(string $value)
 * @method string getCcSsIssue()
 * @method Mage_Sales_Model_Order_Payment setCcSsIssue(string $value)
 * @method string getEcheckAccountName()
 * @method Mage_Sales_Model_Order_Payment setEcheckAccountName(string $value)
 * @method string getCcAvsStatus()
 * @method Mage_Sales_Model_Order_Payment setCcAvsStatus(string $value)
 * @method string getCcNumberEnc()
 * @method Mage_Sales_Model_Order_Payment setCcNumberEnc(string $value)
 * @method string getCcTransId()
 * @method Mage_Sales_Model_Order_Payment setCcTransId(string $value)
 * @method string getAddressStatus()
 * @method Mage_Sales_Model_Order_Payment setAddressStatus(string $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Payment extends Mage_Payment_Model_Info
{
    /**
     * Actions for payment when it triggered review state
     *
     * @var string
     */
    const REVIEW_ACTION_ACCEPT = 'accept';
    const REVIEW_ACTION_DENY   = 'deny';
    const REVIEW_ACTION_UPDATE = 'update';

    /**
     * Order model object
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Billing agreement instance that may be created during payment processing
     *
     * @var Mage_Sales_Model_Billing_Agreement
     */
    protected $_billingAgreement = null;

    /**
     * Whether can void
     * @var string
     */
    protected $_canVoidLookup = null;

    /**
     * Transactions registry to spare resource calls
     * array(txn_id => sales/order_payment_transaction)
     * @var array
     */
    protected $_transactionsLookup = array();

    protected $_eventPrefix = 'sales_order_payment';
    protected $_eventObject = 'payment';

    /**
     * Transaction addditional information container
     *
     * @var array
     */
    protected $_transactionAdditionalInfo = array();

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Order_Payment');
    }

    /**
     * Declare order model object
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Mage_Sales_Model_Order_Payment
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Retrieve order model object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Check order payment capture action availability
     *
     * @return bool
     */
    public function canCapture()
    {
        if (!$this->getMethodInstance()->canCapture()) {
            return false;
        }
        // Check Authoriztion transaction state
        $authTransaction = $this->getAuthorizationTransaction();
        if ($authTransaction && $authTransaction->getIsClosed()) {
            $orderTransaction = $this->_lookupTransaction(null, Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
            if (!$orderTransaction) {
                return false;
            }
        }
        return true;
    }

    public function canRefund()
    {
        return $this->getMethodInstance()->canRefund();
    }

    public function canRefundPartialPerInvoice()
    {
        return $this->getMethodInstance()->canRefundPartialPerInvoice();
    }

    public function canCapturePartial()
    {
        return $this->getMethodInstance()->canCapturePartial();
    }

    /**
     * Authorize or authorize and capture payment on gateway, if applicable
     * This method is supposed to be called only when order is placed
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function place()
    {
        Mage::dispatchEvent('sales_order_payment_place_start', array('payment' => $this));
        $order = $this->getOrder();

        $this->setAmountOrdered($order->getTotalDue());
        $this->setBaseAmountOrdered($order->getBaseTotalDue());
        $this->setShippingAmount($order->getShippingAmount());
        $this->setBaseShippingAmount($order->getBaseShippingAmount());

        $methodInstance = $this->getMethodInstance();
        $methodInstance->setStore($order->getStoreId());

        $orderState = Mage_Sales_Model_Order::STATE_NEW;
        $orderStatus= false;

        $stateObject = new Varien_Object();

        /**
         * Do order payment validation on payment method level
         */
        $methodInstance->validate();
        $action = $methodInstance->getConfigPaymentAction();
        if ($action) {
            if ($methodInstance->isInitializeNeeded()) {
                /**
                 * For method initialization we have to use original config value for payment action
                 */
                $methodInstance->initialize($methodInstance->getConfigData('payment_action'), $stateObject);
            } else {
                $orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
                switch ($action) {
                    case Mage_Payment_Model_Method_Abstract::ACTION_ORDER:
                        $this->_order($order->getBaseTotalDue());
                        break;
                    case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
                        $this->_authorize(true, $order->getBaseTotalDue()); // base amount will be set inside
                        $this->setAmountAuthorized($order->getTotalDue());
                        break;
                    case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
                        $this->setAmountAuthorized($order->getTotalDue());
                        $this->setBaseAmountAuthorized($order->getBaseTotalDue());
                        $this->capture(null);
                        break;
                    default:
                        break;
                }
            }
        }

        $this->_createBillingAgreement();

        $orderIsNotified = null;
        if ($stateObject->getState() && $stateObject->getStatus()) {
            $orderState      = $stateObject->getState();
            $orderStatus     = $stateObject->getStatus();
            $orderIsNotified = $stateObject->getIsNotified();
        } else {
            $orderStatus = $methodInstance->getConfigData('order_status');
            if (!$orderStatus) {
                $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
            }
        }
        $isCustomerNotified = (null !== $orderIsNotified) ? $orderIsNotified : $order->getCustomerNoteNotify();
        $message = $order->getCustomerNote();

        // add message if order was put into review during authorization or capture
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            if ($message) {
                $order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
            }
        }
        // add message to history if order state already declared
        elseif ($order->getState() && ($orderStatus !== $order->getStatus() || $message)) {
            $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
        }
        // set order state
        elseif (($order->getState() != $orderState) || ($order->getStatus() != $orderStatus) || $message) {
            $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
        }

        Mage::dispatchEvent('sales_order_payment_place_end', array('payment' => $this));

        return $this;
    }

    /**
     * Capture the payment online
     * Requires an invoice. If there is no invoice specified, will automatically prepare an invoice for order
     * Updates transactions hierarchy, if required
     * Updates payment totals, updates order status and adds proper comments
     *
     * TODO: eliminate logic duplication with registerCaptureNotification()
     *
     * @return Mage_Sales_Model_Order_Payment
     * @throws Mage_Core_Exception
     */
    public function capture($invoice)
    {
        if (is_null($invoice)) {
            $invoice = $this->_invoice();
            $this->setCreatedInvoice($invoice);
            return $this; // @see Mage_Sales_Model_Order_Invoice::capture()
        }
        $amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
        $order = $this->getOrder();

        // prepare parent transaction and its amount
        $paidWorkaround = 0;
        if (!$invoice->wasPayCalled()) {
            $paidWorkaround = (float)$amountToCapture;
        }
        $this->_isCaptureFinal($paidWorkaround);

        $this->_generateTransactionId(
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
            $this->getAuthorizationTransaction()
        );

        Mage::dispatchEvent('sales_order_payment_capture', array('payment' => $this, 'invoice' => $invoice));

        /**
         * Fetch an update about existing transaction. It can determine whether the transaction can be paid
         * Capture attempt will happen only when invoice is not yet paid and the transaction can be paid
         */
        if ($invoice->getTransactionId()) {
            $this->getMethodInstance()
                ->setStore($order->getStoreId())
                ->fetchTransactionInfo($this, $invoice->getTransactionId());
        }
        $status = true;
        if (!$invoice->getIsPaid() && !$this->getIsTransactionPending()) {
            // attempt to capture: this can trigger "is_transaction_pending"
            $this->getMethodInstance()->setStore($order->getStoreId())->capture($this, $amountToCapture);

            $transaction = $this->_addTransaction(
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                $invoice,
                true
            );

            if ($this->getIsTransactionPending()) {
                $message = Mage::helper('Mage_Sales_Helper_Data')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amountToCapture));
                $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                if ($this->getIsFraudDetected()) {
                    $status = Mage_Sales_Model_Order::STATUS_FRAUD;
                }
                $invoice->setIsPaid(false);
            } else { // normal online capture: invoice is marked as "paid"
                $message = Mage::helper('Mage_Sales_Helper_Data')->__('Captured amount of %s online.', $this->_formatPrice($amountToCapture));
                $state = Mage_Sales_Model_Order::STATE_PROCESSING;
                $invoice->setIsPaid(true);
                $this->_updateTotals(array('base_amount_paid_online' => $amountToCapture));
            }
            if ($order->isNominal()) {
                $message = $this->_prependMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Nominal order registered.'));
            } else {
                $message = $this->_prependMessage($message);
                $message = $this->_appendTransactionToMessage($transaction, $message);
            }
            $order->setState($state, $status, $message);
            $this->getMethodInstance()->processInvoice($invoice, $this);
            return $this;
        }
        Mage::throwException(
            Mage::helper('Mage_Sales_Helper_Data')->__('The transaction "%s" cannot be captured yet.', $invoice->getTransactionId())
        );
    }

    /**
     * Process a capture notification from a payment gateway for specified amount
     * Creates an invoice automatically if the amount covers the order base grand total completely
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     *
     * TODO: eliminate logic duplication with capture()
     *
     * @param float $amount
     * @return Mage_Sales_Model_Order_Payment
     */
    public function registerCaptureNotification($amount)
    {
        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
            $this->getAuthorizationTransaction()
        );

        $order   = $this->getOrder();
        $amount  = (float)$amount;
        $invoice = $this->_getInvoiceForTransactionId($this->getTransactionId());

        // register new capture
        if (!$invoice) {
            if ($this->_isCaptureFinal($amount)) {
                $invoice = $order->prepareInvoice()->register();
                $order->addRelatedObject($invoice);
                $this->setCreatedInvoice($invoice);
            } else {
                $this->setIsFraudDetected(true);
                $this->_updateTotals(array('base_amount_paid_online' => $amount));
            }
        }

        $status = true;
        if ($this->getIsTransactionPending()) {
            $message = Mage::helper('Mage_Sales_Helper_Data')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $message = Mage::helper('Mage_Sales_Helper_Data')->__('Order is suspended as its capture amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
        } else {
            $message = Mage::helper('Mage_Sales_Helper_Data')->__('Registered notification about captured amount of %s.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            if ($this->getIsFraudDetected()) {
                $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                $message = Mage::helper('Mage_Sales_Helper_Data')->__('Order is suspended as its capture amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
            // register capture for an existing invoice
            if ($invoice && Mage_Sales_Model_Order_Invoice::STATE_OPEN == $invoice->getState()) {
                $invoice->pay();
                $this->_updateTotals(array('base_amount_paid_online' => $amount));
                $order->addRelatedObject($invoice);
            }
        }

        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $invoice, true);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState($state, $status, $message);
        return $this;
    }

    /**
     * Process authorization notification
     *
     * @see self::_authorize()
     * @param float $amount
     * @return Mage_Sales_Model_Order_Payment
     */
    public function registerAuthorizationNotification($amount)
    {
        return ($this->_isTransactionExists()) ? $this : $this->_authorize(false, $amount);
    }

    /**
     * Register payment fact: update self totals from the invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Mage_Sales_Model_Order_Payment
     */
    public function pay($invoice)
    {
        $this->_updateTotals(array(
            'amount_paid' => $invoice->getGrandTotal(),
            'base_amount_paid' => $invoice->getBaseGrandTotal(),
            'shipping_captured' => $invoice->getShippingAmount(),
            'base_shipping_captured' => $invoice->getBaseShippingAmount(),
        ));
        Mage::dispatchEvent('sales_order_payment_pay', array('payment' => $this, 'invoice' => $invoice));
        return $this;
    }

    /**
     * Cancel specified invoice: update self totals from it
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Mage_Sales_Model_Order_Payment
     */
    public function cancelInvoice($invoice)
    {
        $this->_updateTotals(array(
            'amount_paid' => -1 * $invoice->getGrandTotal(),
            'base_amount_paid' => -1 * $invoice->getBaseGrandTotal(),
            'shipping_captured' => -1 * $invoice->getShippingAmount(),
            'base_shipping_captured' => -1 * $invoice->getBaseShippingAmount(),
        ));
        Mage::dispatchEvent('sales_order_payment_cancel_invoice', array('payment' => $this, 'invoice' => $invoice));
        return $this;
    }

    /**
     * Create new invoice with maximum qty for invoice for each item
     * register this invoice and capture
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _invoice()
    {
        $invoice = $this->getOrder()->prepareInvoice();

        $invoice->register();
        if ($this->getMethodInstance()->canCapture()) {
            $invoice->capture();
        }

        $this->getOrder()->addRelatedObject($invoice);
        return $invoice;
    }

    /**
     * Check order payment void availability
     *
     * @return bool
     */
    public function canVoid(Varien_Object $document)
    {
        if (null === $this->_canVoidLookup) {
            $this->_canVoidLookup = (bool)$this->getMethodInstance()->canVoid($document);
            if ($this->_canVoidLookup) {
                $authTransaction = $this->getAuthorizationTransaction();
                $this->_canVoidLookup = (bool)$authTransaction && !(int)$authTransaction->getIsClosed();
            }
        }
        return $this->_canVoidLookup;
    }

    /**
     * Void payment online
     *
     * @see self::_void()
     * @param Varien_Object $document
     * @return Mage_Sales_Model_Order_Payment
     */
    public function void(Varien_Object $document)
    {
        $this->_void(true);
        Mage::dispatchEvent('sales_order_payment_void', array('payment' => $this, 'invoice' => $document));
        return $this;
    }

    /**
     * Process void notification
     *
     * @see self::_void()
     * @param float $amount
     * @return Mage_Sales_Model_Payment
     */
    public function registerVoidNotification($amount = null)
    {
        if (!$this->hasMessage()) {
            $this->setMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Registered a Void notification.'));
        }
        return $this->_void(false, $amount);
    }

    /**
     * Refund payment online or offline, depending on whether there is invoice set in the creditmemo instance
     * Updates transactions hierarchy, if required
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Mage_Sales_Model_Order_Payment
     */
    public function refund($creditmemo)
    {
        $baseAmountToRefund = $this->_formatAmount($creditmemo->getBaseGrandTotal());
        $order = $this->getOrder();

        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);

        // call refund from gateway if required
        $isOnline = false;
        $gateway = $this->getMethodInstance();
        $invoice = null;
        if ($gateway->canRefund() && $creditmemo->getDoTransaction()) {
            $this->setCreditmemo($creditmemo);
            $invoice = $creditmemo->getInvoice();
            if ($invoice) {
                $isOnline = true;
                $captureTxn = $this->_lookupTransaction($invoice->getTransactionId());
                if ($captureTxn) {
                    $this->setParentTransactionId($captureTxn->getTxnId());
                }
                $this->setShouldCloseParentTransaction(true); // TODO: implement multiple refunds per capture
                try {
                    $gateway->setStore($this->getOrder()->getStoreId())
                        ->processBeforeRefund($invoice, $this)
                        ->refund($this, $baseAmountToRefund)
                        ->processCreditmemo($creditmemo, $this)
                    ;
                } catch (Mage_Core_Exception $e) {
                    if (!$captureTxn) {
                        $e->setMessage(' ' . Mage::helper('Mage_Sales_Helper_Data')->__('If the invoice was created offline, try creating an offline creditmemo.'), true);
                    }
                    throw $e;
                }
            }
        }

        // update self totals from creditmemo
        $this->_updateTotals(array(
            'amount_refunded' => $creditmemo->getGrandTotal(),
            'base_amount_refunded' => $baseAmountToRefund,
            'base_amount_refunded_online' => $isOnline ? $baseAmountToRefund : null,
            'shipping_refunded' => $creditmemo->getShippingAmount(),
            'base_shipping_refunded' => $creditmemo->getBaseShippingAmount(),
        ));

        // update transactions and order state
        $transaction = $this->_addTransaction(
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,
            $creditmemo,
            $isOnline
        );
        if ($invoice) {
            $message = Mage::helper('Mage_Sales_Helper_Data')->__('Refunded amount of %s online.', $this->_formatPrice($baseAmountToRefund));
        } else {
            $message = $this->hasMessage() ? $this->getMessage()
                : Mage::helper('Mage_Sales_Helper_Data')->__('Refunded amount of %s offline.', $this->_formatPrice($baseAmountToRefund));
        }
        $message = $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);

        Mage::dispatchEvent('sales_order_payment_refund', array('payment' => $this, 'creditmemo' => $creditmemo));
        return $this;
    }

    /**
     * Process payment refund notification
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     * TODO: potentially a full capture can be refunded. In this case if there was only one invoice for that transaction
     *       then we should create a creditmemo from invoice and also refund it offline
     * TODO: implement logic of chargebacks reimbursements (via negative amount)
     *
     * @param float $amount
     * @return Mage_Sales_Model_Order_Payment
     */
    public function registerRefundNotification($amount)
    {
        $notificationAmount = $amount;
        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,
            $this->_lookupTransaction($this->getParentTransactionId())
        );
        if ($this->_isTransactionExists()) {
            return $this;
        }
        $order = $this->getOrder();
        $invoice = $this->_getInvoiceForTransactionId($this->getParentTransactionId());

        if ($invoice) {
            $baseGrandTotal = $invoice->getBaseGrandTotal();
            $amountRefundLeft = $baseGrandTotal - $invoice->getBaseTotalRefunded();
        } else {
            $baseGrandTotal = $order->getBaseGrandTotal();
            $amountRefundLeft = $baseGrandTotal - $order->getBaseTotalRefunded();
        }

        if ($amountRefundLeft < $amount) {
            $amount = $amountRefundLeft;
        }

        if ($amount <= 0) {
            $order->addStatusHistoryComment(Mage::helper('Mage_Sales_Helper_Data')->__('IPN "Refunded". Refund issued by merchant. Registered notification about refunded amount of %s. Transaction ID: "%s"', $this->_formatPrice($notificationAmount), $this->getTransactionId()), false);
            return $this;
        }

        $serviceModel = Mage::getModel('Mage_Sales_Model_Service_Order', $order);
        if ($invoice) {
            if ($invoice->getBaseTotalRefunded() > 0) {
                $adjustment = array('adjustment_positive' => $amount);
            } else {
                $adjustment = array('adjustment_negative' => $baseGrandTotal - $amount);
            }
            $creditmemo = $serviceModel->prepareInvoiceCreditmemo($invoice, $adjustment);
            if ($creditmemo) {
                $totalRefunded = $invoice->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal();
                $this->setShouldCloseParentTransaction($invoice->getBaseGrandTotal() <= $totalRefunded);
            }
        } else {
            if ($order->getBaseTotalRefunded() > 0) {
                $adjustment = array('adjustment_positive' => $amount);
            } else {
                $adjustment = array('adjustment_negative' => $baseGrandTotal - $amount);
            }
            $creditmemo = $serviceModel->prepareCreditmemo($adjustment);
            if ($creditmemo) {
                $totalRefunded = $order->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal();
                $this->setShouldCloseParentTransaction($order->getBaseGrandTotal() <= $totalRefunded);
            }
        }

        $creditmemo->setPaymentRefundDisallowed(true)
            ->setAutomaticallyCreated(true)
            ->register()
            ->addComment(Mage::helper('Mage_Sales_Helper_Data')->__('Credit memo has been created automatically'))
            ->save();

        $this->_updateTotals(array(
            'amount_refunded' => $creditmemo->getGrandTotal(),
            'base_amount_refunded_online' => $amount
        ));

        $this->setCreatedCreditmemo($creditmemo);
        // update transactions and order state
        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $creditmemo);
        $message = $this->_prependMessage(
            Mage::helper('Mage_Sales_Helper_Data')->__('Registered notification about refunded amount of %s.', $this->_formatPrice($amount))
        );
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);
        return $this;
    }

    /**
     * Cancel a creditmemo: substract its totals from the payment
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Mage_Sales_Model_Order_Payment
     */
    public function cancelCreditmemo($creditmemo)
    {
        $this->_updateTotals(array(
            'amount_refunded' => -1 * $creditmemo->getGrandTotal(),
            'base_amount_refunded' => -1 * $creditmemo->getBaseGrandTotal(),
            'shipping_refunded' => -1 * $creditmemo->getShippingAmount(),
            'base_shipping_refunded' => -1 * $creditmemo->getBaseShippingAmount()
        ));
        Mage::dispatchEvent('sales_order_payment_cancel_creditmemo',
            array('payment' => $this, 'creditmemo' => $creditmemo)
        );
        return $this;
    }

    /**
     * Order cancellation hook for payment method instance
     * Adds void transaction if needed
     * @return Mage_Sales_Model_Order_Payment
     */
    public function cancel()
    {
        $isOnline = true;
        if (!$this->canVoid(new Varien_Object())) {
            $isOnline = false;
        }

        if (!$this->hasMessage()) {
            $this->setMessage($isOnline ? Mage::helper('Mage_Sales_Helper_Data')->__('Canceled order online.')
                : Mage::helper('Mage_Sales_Helper_Data')->__('Canceled order offline.')
            );
        }

        if ($isOnline) {
            $this->_void($isOnline, null, 'cancel');
        }

        Mage::dispatchEvent('sales_order_payment_cancel', array('payment' => $this));

        return $this;
    }

    /**
     * Check order payment review availability
     *
     * @return bool
     */
    public function canReviewPayment()
    {
        return (bool)$this->getMethodInstance()->canReviewPayment($this);
    }

    public function canFetchTransactionInfo()
    {
        return (bool)$this->getMethodInstance()->canFetchTransactionInfo();
    }

    /**
     * Accept online a payment that is in review state
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function accept()
    {
        $this->registerPaymentReviewAction(self::REVIEW_ACTION_ACCEPT, true);
        return $this;
    }

    /**
     * Accept order with payment method instance
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function deny()
    {
        $this->registerPaymentReviewAction(self::REVIEW_ACTION_DENY, true);
        return $this;
    }

    /**
     * Perform the payment review action: either initiated by merchant or by a notification
     *
     * Sets order to processing state and optionally approves invoice or cancels the order
     *
     * @param string $action
     * @param bool $isOnline
     * @return Mage_Sales_Model_Order_Payment
     */
    public function registerPaymentReviewAction($action, $isOnline)
    {
        $order = $this->getOrder();

        $transactionId = $isOnline ? $this->getLastTransId() : $this->getTransactionId();
        $invoice = $this->_getInvoiceForTransactionId($transactionId);

        // invoke the payment method to determine what to do with the transaction
        $result = null; $message = null;
        switch ($action) {
            case self::REVIEW_ACTION_ACCEPT:
                if ($isOnline) {
                    if ($this->getMethodInstance()->setStore($order->getStoreId())->acceptPayment($this)) {
                        $result = true;
                        $message = Mage::helper('Mage_Sales_Helper_Data')->__('Approved the payment online.');
                    } else {
                        $result = -1;
                        $message = Mage::helper('Mage_Sales_Helper_Data')->__('There is no need to approve this payment.');
                    }
                } else {
                    $result = (bool)$this->getNotificationResult() ? true : -1;
                    $message = Mage::helper('Mage_Sales_Helper_Data')->__('Registered notification about approved payment.');
                }
                break;
            case self::REVIEW_ACTION_DENY:
                if ($isOnline) {
                    if ($this->getMethodInstance()->setStore($order->getStoreId())->denyPayment($this)) {
                        $result = false;
                        $message = Mage::helper('Mage_Sales_Helper_Data')->__('Denied the payment online.');
                    } else {
                        $result = -1;
                        $message = Mage::helper('Mage_Sales_Helper_Data')->__('There is no need to deny this payment.');
                    }
                } else {
                    $result = (bool)$this->getNotificationResult() ? false : -1;
                    $message = Mage::helper('Mage_Sales_Helper_Data')->__('Registered notification about denied payment.');
                }
                break;
            case self::REVIEW_ACTION_UPDATE:
                if ($isOnline) {
                    $this->getMethodInstance()
                        ->setStore($order->getStoreId())
                        ->fetchTransactionInfo($this, $transactionId);
                } else {
                    // notification mechanism is responsible to update the payment object first
                }
                if ($this->getIsTransactionApproved()) {
                    $result = true;
                    $message = Mage::helper('Mage_Sales_Helper_Data')->__('Registered update about approved payment.');
                } elseif ($this->getIsTransactionDenied()) {
                    $result = false;
                    $message = Mage::helper('Mage_Sales_Helper_Data')->__('Registered update about denied payment.');
                } else {
                    $result = -1;
                    $message = Mage::helper('Mage_Sales_Helper_Data')->__('There is no update for the payment.');
                }
                break;
            default:
                throw new Exception('Not implemented.');
        }
        $message = $this->_prependMessage($message);
        if ($transactionId) {
            $message = $this->_appendTransactionToMessage($transactionId, $message);
        }

        // process payment in case of positive or negative result, or add a comment
        if (-1 === $result) { // switch won't work with such $result!
            $order->addStatusHistoryComment($message);
        } elseif (true === $result) {
            if ($invoice) {
                $invoice->pay();
                $this->_updateTotals(array('base_amount_paid_online' => $invoice->getBaseGrandTotal()));
                $order->addRelatedObject($invoice);
            }
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);
        } elseif (false === $result) {
            if ($invoice) {
                $invoice->cancel();
                $order->addRelatedObject($invoice);
            }
            $order->registerCancellation($message, false);
        }
        return $this;
    }

    /**
     * Order payment either online
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param float $amount
     * @return Mage_Sales_Model_Order_Payment
     */
    protected function _order($amount)
    {
        // update totals
        $amount = $this->_formatAmount($amount, true);

        // do ordering
        $order  = $this->getOrder();
        $state  = Mage_Sales_Model_Order::STATE_PROCESSING;
        $status = true;
        $this->getMethodInstance()->setStore($order->getStoreId())->order($this, $amount);

        if ($this->getSkipOrderProcessing()) {
            return $this;
        }

        // similar logic of "payment review" order as in capturing
        if ($this->getIsTransactionPending()) {
            $message = Mage::helper('Mage_Sales_Helper_Data')->__('Ordering amount of %s is pending approval on gateway.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
        } else {
            $message = Mage::helper('Mage_Sales_Helper_Data')->__('Ordered amount of %s.', $this->_formatPrice($amount));
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState($state, $status, $message);
        return $this;
    }

    /**
     * Authorize payment either online or offline (process auth notification)
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param bool $isOnline
     * @param float $amount
     * @return Mage_Sales_Model_Order_Payment
     */
    protected function _authorize($isOnline, $amount)
    {
        // update totals
        $amount = $this->_formatAmount($amount, true);
        $this->setBaseAmountAuthorized($amount);

        // do authorization
        $order  = $this->getOrder();
        $state  = Mage_Sales_Model_Order::STATE_PROCESSING;
        $status = true;
        if ($isOnline) {
            // invoke authorization on gateway
            $this->getMethodInstance()->setStore($order->getStoreId())->authorize($this, $amount);
        }

        // similar logic of "payment review" order as in capturing
        if ($this->getIsTransactionPending()) {
            $message = Mage::helper('Mage_Sales_Helper_Data')->__('Authorizing amount of %s is pending approval on gateway.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
        } else {
            $message = Mage::helper('Mage_Sales_Helper_Data')->__('Authorized amount of %s.', $this->_formatPrice($amount));
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
        if ($order->isNominal()) {
            $message = $this->_prependMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Nominal order registered.'));
        } else {
            $message = $this->_prependMessage($message);
            $message = $this->_appendTransactionToMessage($transaction, $message);
        }
        $order->setState($state, $status, $message);

        return $this;
    }

    /**
     * Public access to _authorize method
     * @param bool $isOnline
     * @param float $amount
     */
    public function authorize($isOnline, $amount)
    {
        return $this->_authorize($isOnline, $amount);
    }

    /**
     * Void payment either online or offline (process void notification)
     * NOTE: that in some cases authorization can be voided after a capture. In such case it makes sense to use
     *       the amount void amount, for informational purposes.
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param bool $isOnline
     * @param float $amount
     * @param string $gatewayCallback
     * @return Mage_Sales_Model_Order_Payment
     */
    protected function _void($isOnline, $amount = null, $gatewayCallback = 'void')
    {
        $order = $this->getOrder();
        $authTransaction = $this->getAuthorizationTransaction();
        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID, $authTransaction);
        $this->setShouldCloseParentTransaction(true);

        // attempt to void
        if ($isOnline) {
            $this->getMethodInstance()->setStore($order->getStoreId())->$gatewayCallback($this);
        }
        if ($this->_isTransactionExists()) {
            return $this;
        }

        // if the authorization was untouched, we may assume voided amount = order grand total
        // but only if the payment auth amount equals to order grand total
        if ($authTransaction && ($order->getBaseGrandTotal() == $this->getBaseAmountAuthorized())
            && (0 == $this->getBaseAmountCanceled())) {
            if ($authTransaction->canVoidAuthorizationCompletely()) {
                $amount = (float)$order->getBaseGrandTotal();
            }
        }

        if ($amount) {
            $amount = $this->_formatAmount($amount);
        }

        // update transactions, order state and add comments
        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID, null, true);
        $message = $this->hasMessage() ? $this->getMessage() : Mage::helper('Mage_Sales_Helper_Data')->__('Voided authorization.');
        $message = $this->_prependMessage($message);
        if ($amount) {
            $message .= ' ' . Mage::helper('Mage_Sales_Helper_Data')->__('Amount: %s.', $this->_formatPrice($amount));
        }
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);
        return $this;
    }

//    /**
//     * TODO: implement this
//     * @param Mage_Sales_Model_Order_Invoice $invoice
//     * @return Mage_Sales_Model_Order_Payment
//     */
//    public function cancelCapture($invoice = null)
//    {
//    }

    /**
     * Create transaction,
     * prepare its insertion into hierarchy and add its information to payment and comments
     *
     * To add transactions and related information,
     * the following information should be set to payment before processing:
     * - transaction_id
     * - is_transaction_closed (optional) - whether transaction should be closed or open (closed by default)
     * - parent_transaction_id (optional)
     * - should_close_parent_transaction (optional) - whether to close parent transaction (closed by default)
     *
     * If the sales document is specified, it will be linked to the transaction as related for future usage.
     * Currently transaction ID is set into the sales object
     * This method writes the added transaction ID into last_trans_id field of the payment object
     *
     * To make sure transaction object won't cause trouble before saving, use $failsafe = true
     *
     * @param string $type
     * @param Mage_Sales_Model_Abstract $salesDocument
     * @param bool $failsafe
     * @return null|Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function _addTransaction($type, $salesDocument = null, $failsafe = false)
    {
        if ($this->getSkipTransactionCreation()) {
            $this->unsTransactionId();
            return null;
        }

        // look for set transaction ids
        $transactionId = $this->getTransactionId();
        if (null !== $transactionId) {
            // set transaction parameters
            $transaction = false;
            if ($this->getOrder()->getId()) {
                $transaction = $this->_lookupTransaction($transactionId);
            }
            if (!$transaction) {
                $transaction = Mage::getModel('Mage_Sales_Model_Order_Payment_Transaction')->setTxnId($transactionId);
            }
            $transaction
                ->setOrderPaymentObject($this)
                ->setTxnType($type)
                ->isFailsafe($failsafe);

            if ($this->hasIsTransactionClosed()) {
                $transaction->setIsClosed((int)$this->getIsTransactionClosed());
            }

            //set transaction addition information
            if ($this->_transactionAdditionalInfo) {
                foreach ($this->_transactionAdditionalInfo as $key => $value) {
                    $transaction->setAdditionalInformation($key, $value);
                }
            }

            // link with sales entities
            $this->setLastTransId($transactionId);
            $this->setCreatedTransaction($transaction);
            $this->getOrder()->addRelatedObject($transaction);
            if ($salesDocument && $salesDocument instanceof Mage_Sales_Model_Abstract) {
                $salesDocument->setTransactionId($transactionId);
                // TODO: linking transaction with the sales document
            }

            // link with parent transaction
            $parentTransactionId = $this->getParentTransactionId();

            if ($parentTransactionId) {
                $transaction->setParentTxnId($parentTransactionId);
                if ($this->getShouldCloseParentTransaction()) {
                    $parentTransaction = $this->_lookupTransaction($parentTransactionId);
                    if ($parentTransaction) {
                        if (!$parentTransaction->getIsClosed()) {
                            $parentTransaction->isFailsafe($failsafe)->close(false);
                        }
                        $this->getOrder()->addRelatedObject($parentTransaction);
                    }
                }
            }
            return $transaction;
        }
    }

    /**
     * Public acces to _addTransaction method
     *
     * @param string $type
     * @param Mage_Sales_Model_Abstract $salesDocument
     * @param bool $failsafe
     * @param string $message
     * @return null|Mage_Sales_Model_Order_Payment_Transaction
     */
    public function addTransaction($type, $salesDocument = null, $failsafe = false, $message = false)
    {
        $transaction = $this->_addTransaction($type, $salesDocument, $failsafe);

        if ($message) {
            $order = $this->getOrder();
            $message = $this->_appendTransactionToMessage($transaction, $message);
            $order->addStatusHistoryComment($message);
        }

        return $transaction;
    }

    /**
     * Import details data of specified transaction
     *
     * @param Mage_Sales_Model_Order_Payment_Transaction $transactionTo
     * @return Mage_Sales_Model_Order_Payment
     */
    public function importTransactionInfo(Mage_Sales_Model_Order_Payment_Transaction $transactionTo)
    {
        $data = $this->getMethodInstance()
            ->setStore($this->getOrder()->getStoreId())
            ->fetchTransactionInfo($this, $transactionTo->getTxnId());
        if ($data) {
            $transactionTo->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $data);
        }
        return $this;
    }

    /**
     * Get the billing agreement, if any
     *
     * @return Mage_Sales_Model_Billing_Agreement|null
     */
    public function getBillingAgreement()
    {
        return $this->_billingAgreement;
    }

    /**
     * Totals updater utility method
     * Updates self totals by keys in data array('key' => $delta)
     *
     * @param array $data
     */
    protected function _updateTotals($data)
    {
        foreach ($data as $key => $amount) {
            if (null !== $amount) {
                $was = $this->getDataUsingMethod($key);
                $this->setDataUsingMethod($key, $was + $amount);
            }
        }
    }

    /**
     * Check transaction existence by specified transaction id
     *
     * @param string $txnId
     * @return boolean
     */
    protected function _isTransactionExists($txnId = null)
    {
        if (null === $txnId) {
            $txnId = $this->getTransactionId();
        }
        return $txnId && $this->_lookupTransaction($txnId);
    }

    /**
     * Append transaction ID (if any) message to the specified message
     *
     * @param Mage_Sales_Model_Order_Payment_Transaction|null $transaction
     * @param string $message
     * @return string
     */
    protected function _appendTransactionToMessage($transaction, $message)
    {
        if ($transaction) {
            $txnId = is_object($transaction) ? $transaction->getTxnId() : $transaction;
            $message .= ' ' . Mage::helper('Mage_Sales_Helper_Data')->__('Transaction ID: "%s".', $txnId);
        }
        return $message;
    }

    /**
     * Prepend a "prepared_message" that may be set to the payment instance before, to the specified message
     * Prepends value to the specified string or to the comment of specified order status history item instance
     *
     * @param string|Mage_Sales_Model_Order_Status_History $messagePrependTo
     * @return string|Mage_Sales_Model_Order_Status_History
     */
    protected function _prependMessage($messagePrependTo)
    {
        $preparedMessage = $this->getPreparedMessage();
        if ($preparedMessage) {
            if (is_string($preparedMessage)) {
                return $preparedMessage . ' ' . $messagePrependTo;
            } elseif (is_object($preparedMessage)
                && ($preparedMessage instanceof Mage_Sales_Model_Order_Status_History)
            ) {
                $comment = $preparedMessage->getComment() . ' ' . $messagePrependTo;
                $preparedMessage->setComment($comment);
                return $comment;
            }
        }
        return $messagePrependTo;
    }

    /**
     * Round up and cast specified amount to float or string
     *
     * @param string|float $amount
     * @param bool $asFloat
     * @return string|float
     */
    protected function _formatAmount($amount, $asFloat = false)
    {
         $amount = Mage::app()->getStore()->roundPrice($amount);
         return !$asFloat ? (string)$amount : $amount;
    }

    /**
     * Format price with currency sign
     * @param float $amount
     * @return string
     */
    protected function _formatPrice($amount)
    {
        return $this->getOrder()->getBaseCurrency()->formatTxt($amount);
    }

    /**
     * Find one transaction by ID or type
     * @param string $txnId
     * @param string $txnType
     * @return Mage_Sales_Model_Order_Payment_Transaction|false
     */
    protected function _lookupTransaction($txnId, $txnType = false)
    {
        if (!$txnId) {
            if ($txnType && $this->getId()) {
                $collection = Mage::getModel('Mage_Sales_Model_Order_Payment_Transaction')->getCollection()
                    ->setOrderFilter($this->getOrder())
                    ->addPaymentIdFilter($this->getId())
                    ->addTxnTypeFilter($txnType)
                    ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
                    ->setOrder('transaction_id', Varien_Data_Collection::SORT_ORDER_DESC);
                foreach ($collection as $txn) {
                    $txn->setOrderPaymentObject($this);
                    $this->_transactionsLookup[$txn->getTxnId()] = $txn;
                    return $txn;
                }
            }
            return false;
        }
        if (isset($this->_transactionsLookup[$txnId])) {
            return $this->_transactionsLookup[$txnId];
        }
        $txn = Mage::getModel('Mage_Sales_Model_Order_Payment_Transaction')
            ->setOrderPaymentObject($this)
            ->loadByTxnId($txnId);
        if ($txn->getId()) {
            $this->_transactionsLookup[$txnId] = $txn;
        } else {
            $this->_transactionsLookup[$txnId] = false;
        }
        return $this->_transactionsLookup[$txnId];
    }

    /**
     * Find one transaction by ID or type
     * @param string $txnId
     * @param string $txnType
     * @return Mage_Sales_Model_Order_Payment_Transaction|false
     */
    public function lookupTransaction($txnId, $txnType = false)
    {
        return $this->_lookupTransaction($txnId, $txnType);
    }

    /**
     * Lookup an authorization transaction using parent transaction id, if set
     * @return Mage_Sales_Model_Order_Payment_Transaction|false
     */
    public function getAuthorizationTransaction()
    {
        if ($this->getParentTransactionId()) {
            $txn = $this->_lookupTransaction($this->getParentTransactionId());
        } else {
            $txn = false;
        }

        if (!$txn) {
            $txn = $this->_lookupTransaction(false, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
        }
        return $txn;
    }

    /**
     * Lookup the transaction by id
     * @param string $transactionId
     * @return Mage_Sales_Model_Order_Payment_Transaction|false
     */
    public function getTransaction($transactionId)
    {
        return $this->_lookupTransaction($transactionId);
    }

    /**
     * Update transaction ids for further processing
     * If no transactions were set before invoking, may generate an "offline" transaction id
     *
     * @param string $type
     * @param Mage_Sales_Model_Order_Payment_Transaction $transactionBasedOn
     */
    protected function _generateTransactionId($type, $transactionBasedOn = false)
    {
        if (!$this->getParentTransactionId() && !$this->getTransactionId() && $transactionBasedOn) {
            $this->setParentTransactionId($transactionBasedOn->getTxnId());
        }
        // generate transaction id for an offline action or payment method that didn't set it
        if (($parentTxnId = $this->getParentTransactionId()) && !$this->getTransactionId()) {
            $this->setTransactionId("{$parentTxnId}-{$type}");
        }
    }

    /**
     * Decide whether authorization transaction may close (if the amount to capture will cover entire order)
     * @param float $amountToCapture
     * @return bool
     */
    protected function _isCaptureFinal($amountToCapture)
    {
        $amountToCapture = $this->_formatAmount($amountToCapture, true);
        $orderGrandTotal = $this->_formatAmount($this->getOrder()->getBaseGrandTotal(), true);
        if ($orderGrandTotal == $this->_formatAmount($this->getBaseAmountPaid(), true) + $amountToCapture) {
            if (false !== $this->getShouldCloseParentTransaction()) {
                $this->setShouldCloseParentTransaction(true);
            }
            return true;
        }
        return false;
    }

    /**
     * Before object save manipulations
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getOrder()) {
            $this->setParentId($this->getOrder()->getId());
        }

        return $this;
    }

    /**
     * Generate billing agreement object if there is billing agreement data
     * Adds it to order as related object
     */
    protected function _createBillingAgreement()
    {
        if ($this->getBillingAgreementData()) {
            $order = $this->getOrder();
            $agreement = Mage::getModel('Mage_Sales_Model_Billing_Agreement')->importOrderPayment($this);
            if ($agreement->isValid()) {
                $message = Mage::helper('Mage_Sales_Helper_Data')->__('Created billing agreement #%s.', $agreement->getReferenceId());
                $order->addRelatedObject($agreement);
                $this->_billingAgreement = $agreement;
            } else {
                $message = Mage::helper('Mage_Sales_Helper_Data')->__('Failed to create billing agreement for this order.');
            }
            $comment = $order->addStatusHistoryComment($message);
            $order->addRelatedObject($comment);
        }
    }

    /**
     * Additionnal transaction info setter
     *
     * @param sting $key
     * @param string $value
     */
    public function setTransactionAdditionalInfo($key, $value)
    {
        if (is_array($key)) {
            $this->_transactionAdditionalInfo = $key;
        } else {
            $this->_transactionAdditionalInfo[$key] = $value;
        }
    }

    /**
     * Additionnal transaction info getter
     *
     * @param sting $key
     * @return mixed
     */
    public function getTransactionAdditionalInfo($key = null)
    {
        if (is_null($key)) {
            return $this->_transactionAdditionalInfo;
        }
        return isset($this->_transactionAdditionalInfo[$key]) ? $this->_transactionAdditionalInfo[$key] : null;
    }

    /**
     * Reset transaction additional info property
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function resetTransactionAdditionalInfo()
    {
        $this->_transactionAdditionalInfo = array();
        return $this;
    }

    /**
     * Return invoice model for transaction
     *
     * @param string $transactionId
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _getInvoiceForTransactionId($transactionId)
    {
        foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() == $transactionId) {
                $invoice->load($invoice->getId()); // to make sure all data will properly load (maybe not required)
                return $invoice;
            }
        }
        foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
            if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN
                && $invoice->load($invoice->getId())
            ) {
                $invoice->setTransactionId($transactionId);
                return $invoice;
            }
        }
        return false;
    }
}