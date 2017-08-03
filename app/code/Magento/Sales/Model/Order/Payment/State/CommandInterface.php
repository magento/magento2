<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment\State;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Interface CommandInterface using for payment related changes of order state
 * @since 2.0.0
 */
interface CommandInterface
{
    /**
     * Run command
     *
     * @param OrderPaymentInterface $payment
     * @param string|float|int $amount
     * @param OrderInterface $order
     * @return string
     * @since 2.0.0
     */
    public function execute(OrderPaymentInterface $payment, $amount, OrderInterface $order);
}
