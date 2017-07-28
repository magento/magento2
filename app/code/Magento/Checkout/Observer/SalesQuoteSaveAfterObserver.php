<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Checkout\Observer\SalesQuoteSaveAfterObserver
 *
 * @since 2.0.0
 */
class SalesQuoteSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote \Magento\Quote\Model\Quote */
        if ($quote->getIsCheckoutCart()) {
            $this->checkoutSession->getQuoteId($quote->getId());
        }
    }
}
