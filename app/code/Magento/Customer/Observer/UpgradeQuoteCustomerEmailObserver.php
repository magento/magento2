<?php

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
    private $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Upgrade quote customer email when customer has changed email
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Customer\Model\Data\Customer $customer
         */
        $customer = $observer->getEvent()->getData('customer_data_object');
        $email = $customer->getEmail();

        /**
         * @var \Magento\Customer\Model\Data\Customer $customer
         */
        $customerOrig = $observer->getEvent()->getData('orig_customer_data_object');
        $emailOrig = $customerOrig->getEmail();

        if($email != $emailOrig){
            $quote = $this->cartRepository->getForCustomer($customer->getId());
            $quote->setCustomerEmail($email);
            $this->cartRepository->save($quote);
        }
    }
}