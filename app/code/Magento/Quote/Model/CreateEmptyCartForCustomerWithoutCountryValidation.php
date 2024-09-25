<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for creating empty cart for customer without country validation
 */
class CreateEmptyCartForCustomerWithoutCountryValidation
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param CartRepositoryInterface $quoteRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteFactory $quoteFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly QuoteFactory $quoteFactory,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Create empty cart for customer without country validation
     *
     * @param int $customerId
     * @return bool|int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createEmptyCartForCustomerWithoutCountryValidation(int $customerId): bool|int
    {
        $storeId = (int) $this->storeManager->getStore()->getStoreId();
        $quote = $this->createCustomerCart($customerId, $storeId);

        try {
            $this->quoteRepository->save($quote);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__("The quote can't be created."));
        }
        return (int)$quote->getId();
    }

    /**
     * Creates a cart for the currently logged-in customer.
     *
     * @param int $customerId
     * @param int $storeId
     * @return Quote Cart object.
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function createCustomerCart(int $customerId, int $storeId): Quote
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
