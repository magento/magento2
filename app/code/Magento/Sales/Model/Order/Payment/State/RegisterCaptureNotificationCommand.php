<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment\State;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusResolver;

/**
 * Class \Magento\Sales\Model\Order\Payment\State\RegisterCaptureNotificationCommand
 *
 * @since 2.0.0
 */
class RegisterCaptureNotificationCommand implements CommandInterface
{
    /**
     * @var StatusResolver
     * @since 2.2.0
     */
    private $statusResolver;

    /**
     * @param StatusResolver|null $statusResolver
     * @since 2.2.0
     */
    public function __construct(StatusResolver $statusResolver = null)
    {
        $this->statusResolver = $statusResolver
            ? : ObjectManager::getInstance()->get(StatusResolver::class);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param string|float|int $amount
     * @param OrderInterface $order
     * @return string
     * @since 2.0.0
     */
    public function execute(OrderPaymentInterface $payment, $amount, OrderInterface $order)
    {
        $state = Order::STATE_PROCESSING;
        $status = null;
        $message = 'Registered notification about captured amount of %1.';

        if ($payment->getIsTransactionPending()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $message = 'An amount of %1 will be captured after being approved at the payment gateway.';
        }

        if ($payment->getIsFraudDetected()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $status = Order::STATUS_FRAUD;
            $message = 'Order is suspended as its capture amount %1 is suspected to be fraudulent.';
        }

        if (!isset($status)) {
            $status = $this->statusResolver->getOrderStatusByState($order, $state);
        }

        $order->setState($state);
        $order->setStatus($status);

        return __($message, $order->getBaseCurrency()->formatTxt($amount));
    }

    /**
     * @deprecated 2.2.0 Replaced by a StatusResolver class call.
     *
     * @param Order $order
     * @param string $status
     * @param string $state
     * @return void
     * @since 2.0.0
     */
    protected function setOrderStateAndStatus(Order $order, $status, $state)
    {
        if (!$status) {
            $status = $order->getConfig()->getStateDefaultStatus($state);
        }

        $order->setState($state)->setStatus($status);
    }
}
