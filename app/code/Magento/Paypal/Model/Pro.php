<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Model;

use Magento\Paypal\Model\Api\AbstractApi;

/**
 * PayPal Website Payments Pro implementation for payment method instances
 * This model was created because right now PayPal Direct and PayPal Express payment methods cannot have same abstract
 */
class Pro
{
    /**
     * Possible payment review actions (for FMF only)
     */
    const PAYMENT_REVIEW_ACCEPT = 'accept';

    const PAYMENT_REVIEW_DENY = 'deny';

    /**
     * Config instance
     *
     * @var \Magento\Paypal\Model\Config
     */
    protected $_config;

    /**
     * API instance
     *
     * @var \Magento\Paypal\Model\Api\Nvp
     */
    protected $_api;

    /**
     * PayPal info object
     *
     * @var \Magento\Paypal\Model\Info
     */
    protected $_infoInstance;

    /**
     * API model type
     *
     * @var string
     */
    protected $_apiType = 'Magento\Paypal\Model\Api\Nvp';

    /**
     * Config model type
     *
     * @var string
     */
    protected $_configType = 'Magento\Paypal\Model\Config';

    /**
     * @var \Magento\Paypal\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Paypal\Model\Api\Type\Factory
     */
    protected $_apiFactory;

    /**
     * @var \Magento\Paypal\Model\InfoFactory
     */
    protected $_infoFactory;

    /**
     * @param \Magento\Paypal\Model\Config\Factory $configFactory
     * @param \Magento\Paypal\Model\Api\Type\Factory $apiFactory
     * @param \Magento\Paypal\Model\InfoFactory $infoFactory
     */
    public function __construct(
        \Magento\Paypal\Model\Config\Factory $configFactory,
        \Magento\Paypal\Model\Api\Type\Factory $apiFactory,
        \Magento\Paypal\Model\InfoFactory $infoFactory
    ) {
        $this->_configFactory = $configFactory;
        $this->_apiFactory = $apiFactory;
        $this->_infoFactory = $infoFactory;
    }

    /**
     * Payment method code setter. Also instantiates/updates config
     *
     * @param string $code
     * @param int|null $storeId
     * @return $this
     */
    public function setMethod($code, $storeId = null)
    {
        if (null === $this->_config) {
            $params = [$code];
            if (null !== $storeId) {
                $params[] = $storeId;
            }
            $this->_config = $this->_configFactory->create($this->_configType, ['params' => $params]);
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
     * @param \Magento\Paypal\Model\Config $instace
     * @param int|null $storeId
     * @return $this
     */
    public function setConfig(\Magento\Paypal\Model\Config $instace, $storeId = null)
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
     * @return \Magento\Paypal\Model\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * API instance getter
     * Sets current store id to current config instance and passes it to API
     *
     * @return \Magento\Paypal\Model\Api\Nvp
     */
    public function getApi()
    {
        if (null === $this->_api) {
            $this->_api = $this->_apiFactory->create($this->_apiType);
        }
        $this->_api->setConfigObject($this->_config);
        return $this->_api;
    }

    /**
     * Destroy existing NVP Api object
     *
     * @return $this
     */
    public function resetApi()
    {
        $this->_api = null;

        return $this;
    }

    /**
     * Instantiate and return info model
     *
     * @return \Magento\Paypal\Model\Info
     */
    public function getInfo()
    {
        if (null === $this->_infoInstance) {
            $this->_infoInstance = $this->_infoFactory->create();
        }
        return $this->_infoInstance;
    }

    /**
     * Transfer transaction/payment information from API instance to order payment
     *
     * @param \Magento\Framework\Object|AbstractApi $from
     * @param \Magento\Payment\Model\InfoInterface $to
     * @return $this
     */
    public function importPaymentInfo(\Magento\Framework\Object $from, \Magento\Payment\Model\InfoInterface $to)
    {
        // update PayPal-specific payment information in the payment object
        $this->getInfo()->importToPayment($from, $to);

        /**
         * Detect payment review and/or frauds
         * PayPal pro API returns fraud results only in the payment call response
         */
        if ($from->getDataUsingMethod(\Magento\Paypal\Model\Info::IS_FRAUD)) {
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
     * @param \Magento\Framework\Object $payment
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function void(\Magento\Framework\Object $payment)
    {
        $authTransactionId = $this->_getParentTransactionId($payment);
        if ($authTransactionId) {
            $api = $this->getApi();
            $api->setPayment($payment)->setAuthorizationId($authTransactionId)->callDoVoid();
            $this->importPaymentInfo($api, $payment);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You need an authorization transaction to void.')
            );
        }
    }

    /**
     * Attempt to capture payment
     * Will return false if the payment is not supposed to be captured
     *
     * @param \Magento\Framework\Object $payment
     * @param float $amount
     * @return false|null
     */
    public function capture(\Magento\Framework\Object $payment, $amount)
    {
        $authTransactionId = $this->_getParentTransactionId($payment);
        if (!$authTransactionId) {
            return false;
        }
        $api = $this->getApi()->setAuthorizationId(
            $authTransactionId
        )->setIsCaptureComplete(
            $payment->getShouldCloseParentTransaction()
        )->setAmount(
            $amount
        )->setCurrencyCode(
            $payment->getOrder()->getBaseCurrencyCode()
        )->setInvNum(
            $payment->getOrder()->getIncrementId()
        );
        // TODO: pass 'NOTE' to API

        $api->callDoCapture();
        $this->_importCaptureResultToPayment($api, $payment);
    }

    /**
     * Refund a capture transaction
     *
     * @param \Magento\Framework\Object $payment
     * @param float $amount
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Framework\Object $payment, $amount)
    {
        $captureTxnId = $this->_getParentTransactionId($payment);
        if ($captureTxnId) {
            $api = $this->getApi();
            $order = $payment->getOrder();
            $api->setPayment(
                $payment
            )->setTransactionId(
                $captureTxnId
            )->setAmount(
                $amount
            )->setCurrencyCode(
                $order->getBaseCurrencyCode()
            );
            $canRefundMore = $payment->getCreditmemo()->getInvoice()->canRefund();
            $isFullRefund = !$canRefundMore &&
                0 == (double)$order->getBaseTotalOnlineRefunded() + (double)$order->getBaseTotalOfflineRefunded();
            $api->setRefundType(
                $isFullRefund ? \Magento\Paypal\Model\Config::REFUND_TYPE_FULL : \Magento\Paypal\Model\Config::REFUND_TYPE_PARTIAL
            );
            $api->callRefundTransaction();
            $this->_importRefundResultToPayment($api, $payment, $canRefundMore);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t issue a refund transaction because there is no capture transaction.')
            );
        }
    }

    /**
     * Cancel payment
     *
     * @param \Magento\Framework\Object $payment
     * @return void
     */
    public function cancel(\Magento\Framework\Object $payment)
    {
        if (!$payment->getOrder()->getInvoiceCollection()->count()) {
            $this->void($payment);
        }
    }

    /**
     * Check whether can do payment review
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     */
    public function canReviewPayment(\Magento\Payment\Model\InfoInterface $payment)
    {
        $pendingReason = $payment->getAdditionalInformation(\Magento\Paypal\Model\Info::PENDING_REASON_GLOBAL);
        return $this->_isPaymentReviewRequired(
            $payment
        ) && $pendingReason != \Magento\Paypal\Model\Info::PAYMENTSTATUS_REVIEW;
    }

    /**
     * Check whether payment review is required
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     */
    protected function _isPaymentReviewRequired(\Magento\Payment\Model\InfoInterface $payment)
    {
        return \Magento\Paypal\Model\Info::isPaymentReviewRequired($payment);
    }

    /**
     * Perform the payment review
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $action
     * @return bool
     */
    public function reviewPayment(\Magento\Payment\Model\InfoInterface $payment, $action)
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
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\InfoInterface $payment, $transactionId)
    {
        $api = $this->getApi()->setTransactionId($transactionId)->setRawResponseNeeded(true);
        $api->callGetTransactionDetails();
        $this->importPaymentInfo($api, $payment);
        $data = $api->getRawSuccessResponseData();
        return $data ? $data : [];
    }

    /**
     * Import capture results to payment
     *
     * @param \Magento\Paypal\Model\Api\Nvp $api
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return void
     */
    protected function _importCaptureResultToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(false);
        $this->importPaymentInfo($api, $payment);
    }

    /**
     * Import refund results to payment
     *
     * @param \Magento\Paypal\Model\Api\Nvp $api
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param bool $canRefundMore
     * @return void
     */
    protected function _importRefundResultToPayment($api, $payment, $canRefundMore)
    {
        $payment->setTransactionId(
            $api->getRefundTransactionId()
        )->setIsTransactionClosed(
            1 // refund initiated by merchant
        )->setShouldCloseParentTransaction(
            !$canRefundMore
        );
        $this->importPaymentInfo($api, $payment);
    }

    /**
     * Parent transaction id getter
     *
     * @param \Magento\Framework\Object $payment
     * @return string
     */
    protected function _getParentTransactionId(\Magento\Framework\Object $payment)
    {
        return $payment->getParentTransactionId();
    }
}
