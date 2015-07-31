<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment;


use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Operations\Authorize;
use Magento\Sales\Model\Order\Payment\Operations\Capture;
use Magento\Sales\Model\Order\Payment\Operations\Order as OrderOperation;
use Magento\Sales\Model\Order\Payment\Operations\RegisterCaptureNotification;

/**
 * Class Processor using for process payment
 */
class Processor
{
    /**
     * @var Authorize
     */
    protected $authorizeOperation;

    /**
     * @var Capture
     */
    protected $captureOperation;

    /**
     * @var OrderOperation
     */
    protected $orderOperation;

    /**
     * @var RegisterCaptureNotification
     */
    protected $registerCaptureNotification;

    /**
     * Set operations
     *
     * @param Authorize $authorizeOperation
     * @param Capture $captureOperation
     * @param OrderOperation $orderOperation
     */
    public function __construct(
        Authorize $authorizeOperation,
        Capture $captureOperation,
        OrderOperation $orderOperation,
        RegisterCaptureNotification $registerCaptureNotification
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

    public function registerCaptureNotification(
        OrderPaymentInterface $payment,
        $amount,
        $skipFraudDetection = false
    ) {
        return $this->registerCaptureNotification->registerCaptureNotification($payment, $amount, $skipFraudDetection);
    }
}
