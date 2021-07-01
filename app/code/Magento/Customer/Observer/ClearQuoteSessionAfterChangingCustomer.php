<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Clear quote id session if customer was updated
 */
class ClearQuoteSessionAfterChangingCustomer implements ObserverInterface
{
    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * @param QuoteSession $quoteSession
     */
    public function __construct(QuoteSession $quoteSession)
    {
        $this->quoteSession = $quoteSession;
    }

    /**
     * Clear quote id session if current customer was updated
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var CustomerInterface $customerOrig */
        $customerOrig = $observer->getEvent()->getOrigCustomerDataObject();

        if ($customerOrig && (int)$customerOrig->getId() === $this->quoteSession->getCustomerId()) {
            $this->quoteSession->setQuoteId(null);
        }
    }
}
