<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Persistent\Model\QuoteManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ConvertCustomerCartToGuest
{
    /**
     * @param CustomerSession $customerSession
     * @param PersistentSession $persistentSession
     * @param QuoteManager $quoteManager
     */
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly PersistentSession $persistentSession,
        private readonly QuoteManager $quoteManager
    ) {
    }

    /**
     * Convert customer cart to guest cart before order is placed if customer is not logged in
     *
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @param array $orderData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(QuoteManagement $subject, Quote $quote, array $orderData = []): void
    {
        if ($quote->getIsPersistent() && $quote->getCustomerId() && $quote->getCustomerIsGuest()) {
            $this->customerSession->setCustomerId(null);
            $this->customerSession->setCustomerGroupId(null);
            $this->persistentSession->getSession()->removePersistentCookie();
            $this->persistentSession->setSession(null);
            $this->quoteManager->convertCustomerCartToGuest($quote);
        }
    }
}
