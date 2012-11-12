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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Website Payments Pro implementation for payment method instaces
 * This model was created because right now PayPal Direct and PayPal Express payment methods cannot have same abstract
 */
class Mage_Paypal_Model_Pro
{
    /**
     * Possible payment review actions (for FMF only)
     *
     * @var string
     */
    const PAYMENT_REVIEW_ACCEPT = 'accept';
    const PAYMENT_REVIEW_DENY = 'deny';

    /**
     * Config instance
     *
     * @var Mage_Paypal_Model_Config
     */
    protected $_config = null;

    /**
     * API instance
     *
     * @var Mage_Paypal_Model_Api_Nvp
     */
    protected $_api = null;

    /**
     * PayPal info object
     *
     * @var Mage_Paypal_Model_Info
     */
    protected $_infoInstance = null;

    /**
     * API model type
     *
     * @var string
     */
    protected $_apiType = 'Mage_Paypal_Model_Api_Nvp';

    /**
     * Config model type
     *
     * @var string
     */
    protected $_configType = 'Mage_Paypal_Model_Config';

    /**
     * Payment method code setter. Also instantiates/updates config
     *
     * @param string $code
     * @param int|null $storeId
     */
    public function setMethod($code, $storeId = null)
    {
        if (null === $this->_config) {
            $params = array($code);
            if (null !== $storeId) {
                $params[] = $storeId;
            }
            $this->_config = Mage::getModel($this->_configType, array('params' => $params));
        } else {
            $this->_config->setMethod($code);
            if (null !== $storeId) {
                $this->_config->setStoreId($storeId);
            }
        }
        return $this;
    }

    /**
     * Config instance setter
     *
     * @param Mage_Paypal_Model_Config $instace
     * @param int $storeId
     */
    public function setConfig(Mage_Paypal_Model_Config $instace, $storeId = null)
    {
        $this->_config = $instace;
        if (null !== $storeId) {
            $this->_config->setStoreId($storeId);
        }
        return $this;
    }

    /**
     * Config instance getter
     *
     * @return Mage_Paypal_Model_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * API instance getter
     * Sets current store id to current config instance and passes it to API
     *
     * @return Mage_Paypal_Model_Api_Nvp
     */
    public function getApi()
    {
        if (null === $this->_api) {
            $this->_api = Mage::getModel($this->_apiType);
        }
        $this->_api->setConfigObject($this->_config);
        return $this->_api;
    }

    /**
     * Destroy existing NVP Api object
     *
     * @return Mage_Paypal_Model_Pro
     */
    public function resetApi()
    {
        $this->_api = null;

        return $this;
    }

    /**
     * Instantiate and return info model
     *
     * @return Mage_Paypal_Model_Info
     */
    public function getInfo()
    {
        if (null === $this->_infoInstance) {
            $this->_infoInstance = Mage::getModel('Mage_Paypal_Model_Info');
        }
        return $this->_infoInstance;
    }

    /**
     * Transfer transaction/payment information from API instance to order payment
     *
     * @param Mage_Paypal_Model_Api_Abstract $from
     * @param Mage_Payment_Model_Info $to
     * @return Mage_Paypal_Model_Pro
     */
    public function importPaymentInfo(Varien_Object $from, Mage_Payment_Model_Info $to)
    {
        // update PayPal-specific payment information in the payment object
        $this->getInfo()->importToPayment($from, $to);

        /**
         * Detect payment review and/or frauds
         * PayPal pro API returns fraud results only in the payment call response
         */
        if ($from->getDataUsingMethod(Mage_Paypal_Model_Info::IS_FRAUD)) {
            $to->setIsTransactionPending(true);
            $to->setIsFraudDetected(true);
        } elseif ($this->getInfo()->isPaymentReviewRequired($to)) {
            $to->setIsTransactionPending(true);
        }

        // give generic info about transaction state
        if ($this->getInfo()->isPaymentSuccessful($to)) {
            $to->setIsTransactionApproved(true);
        } elseif ($this->getInfo()->isPaymentFailed($to)) {
            $to->setIsTransactionDenied(true);
        }

        return $this;
    }

    /**
     * Void transaction
     *
     * @param Varien_Object $payment
     */
    public function void(Varien_Object $payment)
    {
        if ($authTransactionId = $this->_getParentTransactionId($payment)) {
            $api = $this->getApi();
            $api->setPayment($payment)->setAuthorizationId($authTransactionId)->callDoVoid();
            $this->importPaymentInfo($api, $payment);
        } else {
            Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('Authorization transaction is required to void.'));
        }
    }

    /**
     * Attempt to capture payment
     * Will return false if the payment is not supposed to be captured
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return false|null
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $authTransactionId = $this->_getParentTransactionId($payment);
        if (!$authTransactionId) {
            return false;
        }
        $api = $this->getApi()
            ->setAuthorizationId($authTransactionId)
            ->setIsCaptureComplete($payment->getShouldCloseParentTransaction())
            ->setAmount($amount)
            ->setCurrencyCode($payment->getOrder()->getBaseCurrencyCode())
            ->setInvNum($payment->getOrder()->getIncrementId())
            // TODO: pass 'NOTE' to API
        ;

        $api->callDoCapture();
        $this->_importCaptureResultToPayment($api, $payment);
    }

    /**
     * Refund a capture transaction
     *
     * @param Varien_Object $payment
     * @param float $amount
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $captureTxnId = $this->_getParentTransactionId($payment);
        if ($captureTxnId) {
            $api = $this->getApi();
            $order = $payment->getOrder();
            $api->setPayment($payment)
                ->setTransactionId($captureTxnId)
                ->setAmount($amount)
                ->setCurrencyCode($order->getBaseCurrencyCode())
            ;
            $canRefundMore = $payment->getCreditmemo()->getInvoice()->canRefund();
            $isFullRefund = !$canRefundMore
                && (0 == ((float)$order->getBaseTotalOnlineRefunded() + (float)$order->getBaseTotalOfflineRefunded()));
            $api->setRefundType($isFullRefund ? Mage_Paypal_Model_Config::REFUND_TYPE_FULL
                : Mage_Paypal_Model_Config::REFUND_TYPE_PARTIAL
            );
            $api->callRefundTransaction();
            $this->_importRefundResultToPayment($api, $payment, $canRefundMore);
        } else {
            Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('Impossible to issue a refund transaction because the capture transaction does not exist.'));
        }
    }

    /**
     * Cancel payment
     *
     * @param Varien_Object $payment
     */
    public function cancel(Varien_Object $payment)
    {
        if (!$payment->getOrder()->getInvoiceCollection()->count()) {
            $this->void($payment);
        }
    }

    /**
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return bool
     */
    public function canReviewPayment(Mage_Payment_Model_Info $payment)
    {
        return Mage_Paypal_Model_Info::isPaymentReviewRequired($payment);
    }

    /**
     * Perform the payment review
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $action
     * @return bool
     */
    public function reviewPayment(Mage_Payment_Model_Info $payment, $action)
    {
        $api = $this->getApi()->setTransactionId($payment->getLastTransId());

        // check whether the review is still needed
        $api->callGetTransactionDetails();
        $this->importPaymentInfo($api, $payment);
        if (!$this->getInfo()->isPaymentReviewRequired($payment)) {
            return false;
        }

        // perform the review action
        $api->setAction($action)->callManagePendingTransactionStatus();
        $api->callGetTransactionDetails();
        $this->importPaymentInfo($api, $payment);
        return true;
    }

    /**
     * Fetch transaction details info
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        $api = $this->getApi()
            ->setTransactionId($transactionId)
            ->setRawResponseNeeded(true);
        $api->callGetTransactionDetails();
        $this->importPaymentInfo($api, $payment);
        $data = $api->getRawSuccessResponseData();
        return ($data) ? $data : array();
    }

    /**
     * Validate RP data
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @throws Mage_Core_Exception
     */
    public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
    {
        $errors = array();
        if (strlen($profile->getSubscriberName()) > 32) { // up to 32 single-byte chars
            $errors[] = Mage::helper('Mage_Paypal_Helper_Data')->__('Subscriber name is too long.');
        }
        $refId = $profile->getInternalReferenceId(); // up to 127 single-byte alphanumeric
        if (strlen($refId) > 127) { //  || !preg_match('/^[a-z\d\s]+$/i', $refId)
            $errors[] = Mage::helper('Mage_Paypal_Helper_Data')->__('Merchant reference ID format is not supported.');
        }
        $scheduleDescr = $profile->getScheduleDescription(); // up to 127 single-byte alphanumeric
        if (strlen($refId) > 127) { //  || !preg_match('/^[a-z\d\s]+$/i', $scheduleDescr)
            $errors[] = Mage::helper('Mage_Paypal_Helper_Data')->__('Schedule description is too long.');
        }
        if ($errors) {
            Mage::throwException(implode(' ', $errors));
        }
    }

    /**
     * Submit RP to the gateway
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Payment_Model_Info $paymentInfo
     * @throws Mage_Core_Exception
     */
    public function submitRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile,
        Mage_Payment_Model_Info $paymentInfo
    ) {
        $api = $this->getApi();
        Varien_Object_Mapper::accumulateByMap($profile, $api, array(
            'token', // EC fields
            // TODO: DP fields
            // profile fields
            'subscriber_name', 'start_datetime', 'internal_reference_id', 'schedule_description',
            'suspension_threshold', 'bill_failed_later', 'period_unit', 'period_frequency', 'period_max_cycles',
            'billing_amount' => 'amount', 'trial_period_unit', 'trial_period_frequency', 'trial_period_max_cycles',
            'trial_billing_amount', 'currency_code', 'shipping_amount', 'tax_amount', 'init_amount', 'init_may_fail',
        ));
        $api->callCreateRecurringPaymentsProfile();
        $profile->setReferenceId($api->getRecurringProfileId());
        if ($api->getIsProfileActive()) {
            $profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);
        } elseif ($api->getIsProfilePending()) {
            $profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_PENDING);
        }
    }

    /**
     * Fetch RP details
     *
     * @param string $referenceId
     * @param Varien_Object $result
     */
    public function getRecurringProfileDetails($referenceId, Varien_Object $result)
    {
        $api = $this->getApi();
        $api->setRecurringProfileId($referenceId)
            ->callGetRecurringPaymentsProfileDetails($result)
        ;
    }

    /**
     * Update RP data
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
    {

    }

    /**
     * Manage status
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile)
    {
        $api = $this->getApi();
        $action = null;
        switch ($profile->getNewState()) {
            case Mage_Sales_Model_Recurring_Profile::STATE_CANCELED: $action = 'cancel'; break;
            case Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED: $action = 'suspend'; break;
            case Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE: $action = 'activate'; break;
        }
        $state = $profile->getState();
        $api->setRecurringProfileId($profile->getReferenceId())
            ->setIsAlreadyCanceled($state == Mage_Sales_Model_Recurring_Profile::STATE_CANCELED)
            ->setIsAlreadySuspended($state == Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED)
            ->setIsAlreadyActive($state == Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE)
            ->setAction($action)
            ->callManageRecurringPaymentsProfileStatus()
        ;
    }

    /**
     * Import capture results to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp
     * @param Mage_Sales_Model_Order_Payment
     */
    protected function _importCaptureResultToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(false);
        $this->importPaymentInfo($api, $payment);
    }

    /**
     * Import refund results to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp
     * @param Mage_Sales_Model_Order_Payment
     * @param bool $canRefundMore
     */
    protected function _importRefundResultToPayment($api, $payment, $canRefundMore)
    {
        $payment->setTransactionId($api->getRefundTransactionId())
                ->setIsTransactionClosed(1) // refund initiated by merchant
                ->setShouldCloseParentTransaction(!$canRefundMore)
            ;
        $this->importPaymentInfo($api, $payment);
    }

    /**
     * Parent transaction id getter
     *
     * @param Varien_Object $payment
     * @return string
     */
    protected function _getParentTransactionId(Varien_Object $payment)
    {
        return $payment->getParentTransactionId();
    }
}
