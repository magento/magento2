<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;

/**
 * Sets payment additional information.
 */
class BeforeOrderPaymentSaveObserver implements ObserverInterface
{
    /**
     * Sets current instructions for bank transfer account
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        $instructionMethods = [
            Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE,
            Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
        ];
        if (in_array($payment->getMethod(), $instructionMethods)
            && empty($payment->getAdditionalInformation('instructions'))) {
            $payment->setAdditionalInformation(
                'instructions',
                $payment->getMethodInstance()->getConfigData(
                    'instructions',
                    $payment->getOrder()->getStoreId()
                )
            );
        } elseif ($payment->getMethod() === Checkmo::PAYMENT_METHOD_CHECKMO_CODE) {
            $methodInstance = $payment->getMethodInstance();
            $storeId = $payment->getOrder()->getStoreId();

            $payableTo = $methodInstance->getConfigData('payable_to', $storeId);
            if (!empty($payableTo)) {
                $payment->setAdditionalInformation('payable_to', $payableTo);
            }
            $mailingAddress = $methodInstance->getConfigData('mailing_address', $storeId);
            if (!empty($mailingAddress)) {
                $payment->setAdditionalInformation('mailing_address', $mailingAddress);
            }
        }
    }
}
