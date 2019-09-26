<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Sales\Model\Order\Payment;

/**
 * Sets payment additional information.
 */
class BeforeOrderPaymentSaveObserver implements ObserverInterface
{
    /**
     * Sets current instructions for bank transfer account.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        $instructionMethods = [
            Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE,
            Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
        ];
        if (in_array($payment->getMethod(), $instructionMethods)) {
            $payment->setAdditionalInformation('instructions', $this->getInstructions($payment));
        } elseif ($payment->getMethod() === Checkmo::PAYMENT_METHOD_CHECKMO_CODE) {
            $methodInstance = $payment->getMethodInstance();
            if (!empty($methodInstance->getPayableTo())) {
                $payment->setAdditionalInformation('payable_to', $methodInstance->getPayableTo());
            }
            if (!empty($methodInstance->getMailingAddress())) {
                $payment->setAdditionalInformation('mailing_address', $methodInstance->getMailingAddress());
            }
        }
    }

    /**
     * Retrieve store-specific payment method instructions, or already saved if exists.
     *
     * @param Payment $payment
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getInstructions(Payment $payment): ?string
    {
        return $payment->getAdditionalInformation('instructions')
            ?: $payment->getMethodInstance()->getConfigData('instructions', $payment->getOrder()->getStoreId());
    }
}
