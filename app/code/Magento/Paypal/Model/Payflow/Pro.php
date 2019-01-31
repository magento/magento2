<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow;

/**
 * PayPal Website Payments Pro (Payflow Edition) implementation for payment method instances
 * This model was created because right now PayPal Direct and PayPal Express payment
 * (Payflow Edition) methods cannot have same abstract
 */
class Pro extends \Magento\Paypal\Model\Pro
{
    /**
     * Api model type
     *
     * @var string
     */
    protected $_apiType = \Magento\Paypal\Model\Api\PayflowNvp::class;

    /**
     * Config model type
     *
     * @var string
     */
    protected $_configType = \Magento\Paypal\Model\Config::class;

    /**
     * Payflow trx_id key in transaction info
     */
    const TRANSPORT_PAYFLOW_TXN_ID = 'payflow_trxid';

    /**
     * Refund a capture transaction
     *
     * @param \Magento\Framework\DataObject $payment
     * @param float $amount
     * @return void
     */
    public function refund(\Magento\Framework\DataObject $payment, $amount)
    {
        $captureTxnId = $this->_getParentTransactionId($payment);
        if ($captureTxnId) {
            $api = $this->getApi();
            $api->setAuthorizationId($captureTxnId);
        }
        parent::refund($payment, $amount);
    }

    /**
     * Is capture request needed on this transaction
     *
     * @return true
     */
    protected function _isCaptureNeeded()
    {
        return true;
    }

    /**
     * Get payflow transaction id from parent transaction
     *
     * @param \Magento\Framework\DataObject $payment
     * @return string
     */
    protected function _getParentTransactionId(\Magento\Framework\DataObject $payment)
    {
        if ($payment->getParentTransactionId()) {
            return $this->transactionRepository->getByTransactionId(
                $payment->getParentTransactionId(),
                $payment->getId(),
                $payment->getOrder()->getId()
            )->getAdditionalInformation(
                self::TRANSPORT_PAYFLOW_TXN_ID
            );
        }
        return $payment->getParentTransactionId();
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
        $payment->setTransactionId(
            $api->getPaypalTransactionId()
        )->setIsTransactionClosed(
            false
        )->setTransactionAdditionalInfo(
            self::TRANSPORT_PAYFLOW_TXN_ID,
            $api->getTransactionId()
        );
        $payment->setPreparedMessage(__('Payflow PNREF: #%1.', $api->getTransactionId()));
        $this->_infoFactory->create()->importToPayment($api, $payment);
    }

    /**
     * Fetch transaction details info method does not exists in Payflow
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $transactionId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\InfoInterface $payment, $transactionId)
    {
        throw new \Magento\Framework\Exception\LocalizedException(
            __('Unable to fetch transaction details.')
        );
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
            $api->getPaypalTransactionId()
        )->setIsTransactionClosed(
            1 // refund initiated by merchant
        )->setShouldCloseParentTransaction(
            !$canRefundMore
        )->setTransactionAdditionalInfo(
            self::TRANSPORT_PAYFLOW_TXN_ID,
            $api->getTransactionId()
        );
        $payment->setPreparedMessage(__('Payflow PNREF: #%1.', $api->getTransactionId()));
        $this->_infoFactory->create()->importToPayment($api, $payment);
    }
}
