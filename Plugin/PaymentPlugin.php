<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Plugin;

use Magento\Framework\Registry;
use Magento\Payment\Model\MethodInterface;
use Magento\Signifyd\Api\GuaranteeCancelingServiceInterface;

/**
 * Plugin for Magento\Payment\Model\MethodInterface.
 *
 * @see MethodInterface
 */
class PaymentPlugin
{
    /**
     * @var GuaranteeCancelingServiceInterface
     */
    private $guaranteeCancelingService;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param GuaranteeCancelingServiceInterface $guaranteeCancelingService
     * @param Registry $registry
     */
    public function __construct(
        GuaranteeCancelingServiceInterface $guaranteeCancelingService,
        Registry $registry
    ) {
        $this->guaranteeCancelingService = $guaranteeCancelingService;
        $this->registry = $registry;
    }

    /**
     * Performs Signifyd guarantee cancel operation after payment denying.
     *
     * @see MethodInterface::denyPayment
     * @param MethodInterface $subject
     * @param MethodInterface|bool $result
     * @return MethodInterface|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDenyPayment(MethodInterface $subject, $result)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->registry->registry('current_order');

        if ($this->isPaymentDenied($order->getPayment(), $result)) {
            $this->guaranteeCancelingService->cancelForOrder(
                $order->getEntityId()
            );
        }

        return $result;
    }

    /**
     * Checks if deny payment operation was successful.
     *
     * Result not false check for payment methods using AbstractMethod.
     * Transaction is closed check for payment methods using Gateway.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @param MethodInterface $result
     * @return bool
     */
    private function isPaymentDenied($payment, $result)
    {
        return $result !== false || $payment->getIsTransactionClosed();
    }
}
