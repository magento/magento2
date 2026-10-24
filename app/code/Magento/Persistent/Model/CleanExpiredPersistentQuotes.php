<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Persistent\Model\ResourceModel\ExpiredPersistentQuotesCollectionFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Cleaning expired persistent quotes from the cron
 */
class CleanExpiredPersistentQuotes
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param ExpiredPersistentQuotesCollectionFactory $expiredPersistentQuotesCollectionFactory
     * @param QuoteRepository $quoteRepository
     * @param Snapshot $snapshot
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ExpiredPersistentQuotesCollectionFactory $expiredPersistentQuotesCollectionFactory,
        private readonly QuoteRepository $quoteRepository,
        private readonly Snapshot $snapshot,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Execute the cron job
     *
     * @param int $websiteId
     * @return void
     */
    public function execute(int $websiteId): void
    {
        $stores = $this->storeManager->getWebsite($websiteId)->getStores();
        foreach ($stores as $store) {
            $this->processStoreQuotes($store);
        }
    }

    /**
     * Process store quotes in batches
     *
     * @param StoreInterface $store
     * @return void
     */
    private function processStoreQuotes(StoreInterface $store): void
    {
        $expiredQuotes = $this->expiredPersistentQuotesCollectionFactory->create(['store' => $store]);
        foreach ($expiredQuotes as $quote) {
            try {
                $this->quoteRepository->delete($quote);
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Unable to delete expired quote (ID: %s): %s',
                    $quote->getId(),
                    $e->getMessage()
                ));
            }
            $this->snapshot->clear($quote);
            $quote->clearInstance();
        }
    }
}
