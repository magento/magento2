<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class to create an empty cart and quote for a specified customer.
 */
readonly class CreateEmptyCartForCustomer
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param CartRepositoryInterface $quoteRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        private StoreManagerInterface $storeManager,
        private CartRepositoryInterface $quoteRepository,
        private CustomerRepositoryInterface $customerRepository,
        private QuoteFactory $quoteFactory,
    ) {
    }

    /**
     * Creates an empty cart and quote for a specified customer if customer does not have a cart yet.
     *
     * @param int $customerId
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(int $customerId): int
    {
        $storeId = (int) $this->storeManager->getStore()->getStoreId();
        $quote = $this->getCustomerActiveQuote($customerId, $storeId);

        try {
            $this->quoteRepository->save($quote);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__("The quote can't be created."));
        }
        return (int)$quote->getId();
    }

    /**
     * Get an active quote for the currently logged-in customer.
     *
     * @param int $customerId
     * @param int $storeId
     * @return Quote Cart object.
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getCustomerActiveQuote(int $customerId, int $storeId): Quote
    {
        try {
            $activeQuote = $this->quoteRepository->getActiveForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $activeCustomer = $this->customerRepository->getById($customerId);
            $activeQuote = $this->quoteFactory->create();
            $activeQuote->setStoreId($storeId);
            $activeQuote->setCustomer($activeCustomer);
            $activeQuote->setCustomerIsGuest(0);
        }
        return $activeQuote;
    }
}
