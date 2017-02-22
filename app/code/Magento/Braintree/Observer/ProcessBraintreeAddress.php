<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProcessBraintreeAddress implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if ($quote->getPayment()->getMethod() === \Magento\Braintree\Model\PaymentMethod\PayPal:: METHOD_CODE) {
            $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        }
    }
}
