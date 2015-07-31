<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment;


use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Operations\Authorize;
use Magento\Sales\Model\Order\Payment\Operations\Capture;
use Magento\Sales\Model\Order\Payment\Operations\Order as OrderOperation;

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


    function __construct(
        Authorize $authorizeOperation,
        Capture $captureOperation,
        OrderOperation $orderOperation
    ) {
        $this->authorizeOperation = $authorizeOperation;
        $this->captureOperation = $captureOperation;
        $this->orderOperation = $orderOperation;
    }


    public function authorize(OrderPaymentInterface $payment, $isOnline, $amount)
    {
        return $this->authorizeOperation->authorize($payment, $isOnline, $amount);
    }

    public function capture(OrderPaymentInterface $payment, $invoice)
    {
        return $this->captureOperation->capture($payment, $invoice);
    }

    public function order(OrderPaymentInterface $payment, $amount)
    {
        return $this->orderOperation->order($payment, $amount);
    }
}
