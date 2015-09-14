<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Observer;

class SalesQuoteSaveAfter
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function invoke($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote \Magento\Quote\Model\Quote */
        if ($quote->getIsCheckoutCart()) {
            $this->checkoutSession->getQuoteId($quote->getId());
        }
    }
}
