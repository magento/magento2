<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Operations\AuthorizeOperation;
use Magento\Sales\Model\Order\Payment\Operations\CaptureOperation;
use Magento\Sales\Model\Order\Payment\Operations\OrderOperation as OrderOperation;
use Magento\Sales\Model\Order\Payment\Operations\RegisterCaptureNotificationOperation;

/**
 * Class Processor using for process payment
 */
class Processor
{
    /**
     * @var AuthorizeOperation
     */
    protected $authorizeOperation;

    /**
     * @var CaptureOperation
     */
    protected $captureOperation;

    /**
     * @var OrderOperation
     */
    protected $orderOperation;

    /**
     * @var RegisterCaptureNotificationOperation
     */
    protected $registerCaptureNotification;

    /**
     * Set operations
     *
     * @param AuthorizeOperation $authorizeOperation
     * @param CaptureOperation $captureOperation
     * @param OrderOperation $orderOperation
     * @param RegisterCaptureNotificationOperation $registerCaptureNotification
     */
    public function __construct(
        AuthorizeOperation $authorizeOperation,
        CaptureOperation $captureOperation,
        OrderOperation $orderOperation,
        RegisterCaptureNotificationOperation $registerCaptureNotification
    ) {
        $this->authorizeOperation = $authorizeOperation;
        $this->captureOperation = $captureOperation;
        $this->orderOperation = $orderOperation;
        $this->registerCaptureNotification = $registerCaptureNotification;
    }

    /**
     * Process authorize operation
     *
     * @param OrderPaymentInterface $payment
     * @param bool $isOnline
     * @param float $amount
     * @return OrderPaymentInterface|Payment
     */
    public function authorize(OrderPaymentInterface $payment, $isOnline, $amount)
    {
        return $this->authorizeOperation->authorize($payment, $isOnline, $amount);
    }

    /**
     * Process capture operation
     *
     * @param OrderPaymentInterface $payment
     * @param InvoiceInterface $invoice
     * @return OrderPaymentInterface|Payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(OrderPaymentInterface $payment, $invoice)
    {
        return $this->captureOperation->capture($payment, $invoice);
    }

    /**
     * Process order operation
     *
     * @param OrderPaymentInterface $payment
     * @param float $amount
     * @return OrderPaymentInterface|Payment
     */
    public function order(OrderPaymentInterface $payment, $amount)
    {
        return $this->orderOperation->order($payment, $amount);
    }

    /**
     * Registers capture notification.
     *
     * @param OrderPaymentInterface $payment
     * @param string|float $amount
     * @param bool|int $skipFraudDetection
     * @return OrderPaymentInterface
     */
    public function registerCaptureNotification(
        OrderPaymentInterface $payment,
        $amount,
        $skipFraudDetection = false
    ) {
        return $this->registerCaptureNotification->registerCaptureNotification($payment, $amount, $skipFraudDetection);
    }
}
