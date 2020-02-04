<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Plugin;

use Magento\Payment\Model\InfoInterface;
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
     * @param GuaranteeCancelingServiceInterface $guaranteeCancelingService
     */
    public function __construct(
        GuaranteeCancelingServiceInterface $guaranteeCancelingService
    ) {
        $this->guaranteeCancelingService = $guaranteeCancelingService;
    }

    /**
     * Performs Signifyd guarantee cancel operation after payment denying.
     *
     * @see MethodInterface::denyPayment
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param MethodInterface $subject
     * @param MethodInterface|bool $result
     * @param InfoInterface $payment
     * @return bool|MethodInterface
     */
    public function afterDenyPayment(MethodInterface $subject, $result, InfoInterface $payment)
    {
        if ($this->isPaymentDenied($payment, $result)) {
            $this->guaranteeCancelingService->cancelForOrder($payment->getParentId());
        }

        return $result;
    }

    /**
     * Checks if deny payment operation was successful.
     *
     * Result not false check for payment methods using AbstractMethod.
     * Transaction is closed check for payment methods using Gateway.
     *
     * @param InfoInterface $payment
     * @param MethodInterface $result
     * @return bool
     */
    private function isPaymentDenied($payment, $result)
    {
        return $result !== false || $payment->getIsTransactionClosed();
    }
}
