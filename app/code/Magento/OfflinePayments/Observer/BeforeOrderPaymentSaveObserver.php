<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * OfflinePayments Observer
 */
namespace Magento\OfflinePayments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;

/**
 * Class \Magento\OfflinePayments\Observer\BeforeOrderPaymentSaveObserver
 *
 */
class BeforeOrderPaymentSaveObserver implements ObserverInterface
{
    /**
     * Sets current instructions for bank transfer account
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        $instructionMethods = [
            Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE,
            Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
        ];
        if (in_array($payment->getMethod(), $instructionMethods)) {
            $payment->setAdditionalInformation(
                'instructions',
                $payment->getMethodInstance()->getInstructions()
            );
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
}
