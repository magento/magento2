<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * OfflinePayments Observer
 */
namespace Magento\OfflinePayments\Model;

class Observer
{
    /**
     * Sets current instructions for bank transfer account
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function beforeOrderPaymentSave(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        if ($payment->getMethod() === Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE
            || $payment->getMethod() === Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
        ) {
            $payment->setAdditionalInformation(
                'instructions',
                $payment->getMethodInstance()->getInstructions()
            );
        } elseif ($payment->getMethod() === Checkmo::PAYMENT_METHOD_CHECKMO_CODE) {
            $payment->setAdditionalInformation(
                'payable_to',
                $payment->getMethodInstance()->getPayableTo()
            );
            $payment->setAdditionalInformation(
                'mailing_address',
                $payment->getMethodInstance()->getMailingAddress()
            );
        }
    }
}
