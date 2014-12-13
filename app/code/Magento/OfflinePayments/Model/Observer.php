<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $banktransfer = \Magento\OfflinePayments\Model\Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE;
        if ($payment->getMethod() === $banktransfer) {
            $payment->setAdditionalInformation('instructions', $payment->getMethodInstance()->getInstructions());
        }
    }
}
