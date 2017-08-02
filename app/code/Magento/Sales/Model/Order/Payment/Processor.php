<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.0.0
 */
class Processor
{
    /**
     * @var AuthorizeOperation
     * @since 2.0.0
     */
    protected $authorizeOperation;

    /**
     * @var CaptureOperation
     * @since 2.0.0
     */
    protected $captureOperation;

    /**
     * @var OrderOperation
     * @since 2.0.0
     */
    protected $orderOperation;

    /**
     * @var RegisterCaptureNotificationOperation
     * @since 2.0.0
     */
    protected $registerCaptureNotification;

    /**
     * Set operations
     *
     * @param AuthorizeOperation $authorizeOperation
     * @param CaptureOperation $captureOperation
     * @param OrderOperation $orderOperation
     * @param RegisterCaptureNotificationOperation $registerCaptureNotification
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function registerCaptureNotification(
        OrderPaymentInterface $payment,
        $amount,
        $skipFraudDetection = false
    ) {
        return $this->registerCaptureNotification->registerCaptureNotification($payment, $amount, $skipFraudDetection);
    }
}
