<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class observer UpgradeQuoteCustomerEmailObserver
 */
class UpgradeQuoteCustomerEmailObserver implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Upgrade quote customer email when customer has changed email
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $observer->getEvent()->getCustomerDataObject();
        $email = $customer->getEmail();

        /** @var \Magento\Customer\Model\Data\Customer $customerOrig */
        $customerOrig = $observer->getEvent()->getOrigCustomerDataObject();
        $emailOrig = $customerOrig->getEmail();

        if ($email != $emailOrig) {
                $quote = $this->quoteRepository->getForCustomer($customer->getId());
                $quote->setCustomerEmail($email);
                $this->quoteRepository->save($quote);

        }
    }
}
