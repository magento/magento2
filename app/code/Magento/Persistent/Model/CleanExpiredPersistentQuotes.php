<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Persistent\Model\ResourceModel\ExpiredPersistentQuotesCollection;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Cleaning expired persistent quotes from the cron
 */
class CleanExpiredPersistentQuotes
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param ExpiredPersistentQuotesCollection $expiredPersistentQuotesCollection
     * @param QuoteRepository $quoteRepository
     * @param Snapshot $snapshot
     * @param LoggerInterface $logger
     * @param int $batchSize
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ExpiredPersistentQuotesCollection $expiredPersistentQuotesCollection,
        private readonly QuoteRepository $quoteRepository,
        private readonly Snapshot $snapshot,
        private readonly LoggerInterface $logger,
        private readonly int $batchSize
    ) {
    }

    /**
     * Execute the cron job
     *
     * @param int $websiteId
     * @return void
     * @throws LocalizedException
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
        $lastProcessedId = $count = 0;

        while (true) {
            $quotesToProcess = $this->expiredPersistentQuotesCollection
                ->getExpiredPersistentQuotes($store, $lastProcessedId, $this->batchSize);

            if (!$quotesToProcess->count()) {
                break;
            }

            foreach ($quotesToProcess as $quote) {
                $count++;
                try {
                    $this->quoteRepository->delete($quote);
                    $lastProcessedId = (int)$quote->getId();
                } catch (Exception $e) {
                    $this->logger->error(sprintf(
                        'Unable to delete expired quote (ID: %s): %s',
                        $quote->getId(),
                        (string)$e
                    ));
                }
                if ($count % $this->batchSize === 0) {
                    $this->snapshot->clear($quote);
                }
                $quote->clearInstance();
                unset($quote);
            }

            $quotesToProcess->clear();
            unset($quotesToProcess);
        }
    }
}
