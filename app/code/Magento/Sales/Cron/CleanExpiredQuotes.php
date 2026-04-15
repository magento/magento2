<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Cron;

use Exception;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Cron job for cleaning expired Quotes
 */
class CleanExpiredQuotes
{
    /**
     * Default number of quotes processed per iteration.
     */
    private const DEFAULT_BATCH_SIZE = 5000;

    /**
     * @var ExpiredQuotesCollection
     */
    private $expiredQuotesCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ExpiredQuotesCollection $expiredQuotesCollection
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     * @param int $batchSize
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ExpiredQuotesCollection $expiredQuotesCollection,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        int $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->storeManager = $storeManager;
        $this->expiredQuotesCollection = $expiredQuotesCollection;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->batchSize = $batchSize > 0 ? $batchSize : self::DEFAULT_BATCH_SIZE;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->deleteExpiredQuotesInBatches($store);
        }
    }

    /**
     * Deletes expired quotes in keyset batches for a single store.
     *
     * @param StoreInterface $store
     */
    private function deleteExpiredQuotesInBatches(StoreInterface $store): void
    {
        $lastProcessedId = 0;
        do {
            /** @var $quoteCollection QuoteCollection */
            $quoteCollection = $this->expiredQuotesCollection->getExpiredQuotes($store);
            $quoteCollection->addFieldToSelect('entity_id');
            $quoteCollection->addFieldToFilter('main_table.entity_id', ['gt' => $lastProcessedId]);
            $quoteCollection->setOrder('main_table.entity_id', 'ASC');
            $quoteCollection->setPageSize($this->batchSize);
            $quoteCollection->setCurPage(1);
            $quoteCollection->getSelect()->distinct(true);
            $processedCount = $this->deleteQuotes($quoteCollection, $lastProcessedId);
        } while ($processedCount === $this->batchSize);
    }

    /**
     * Deletes all quotes in a collection and advances last processed id.
     *
     * @param QuoteCollection $quoteCollection
     * @param int $lastProcessedId
     * @return int
     */
    private function deleteQuotes(QuoteCollection $quoteCollection, int &$lastProcessedId): int
    {
        $processedCount = 0;
        foreach ($quoteCollection as $quote) {
            $processedCount++;
            $lastProcessedId = (int)$quote->getId();
            try {
                $this->quoteRepository->delete($quote);
            } catch (Exception $e) {
                $message = sprintf(
                    'Unable to delete expired quote (ID: %s): %s',
                    $quote->getId(),
                    (string)$e
                );
                $this->logger->error($message);
            }
        }

        $quoteCollection->clear();
        return $processedCount;
    }
}
