<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Cron;

use Exception;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection;
use Magento\Sales\Model\ResourceModel\Quote\Delete;
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
     * @var Delete
     */
    private $quoteDelete;

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
     * @param Delete $quoteDelete
     * @param LoggerInterface $logger
     * @param int $batchSize
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ExpiredQuotesCollection $expiredQuotesCollection,
        Delete $quoteDelete,
        LoggerInterface $logger,
        int $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->storeManager = $storeManager;
        $this->expiredQuotesCollection = $expiredQuotesCollection;
        $this->quoteDelete = $quoteDelete;
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
     * Deletes all quotes in a collection via a single bulk DELETE and advances last processed id.
     *
     * @param QuoteCollection $quoteCollection
     * @param int $lastProcessedId
     * @return int
     */
    private function deleteQuotes(QuoteCollection $quoteCollection, int &$lastProcessedId): int
    {
        $ids = $quoteCollection->getColumnValues('entity_id');
        if (empty($ids)) {
            return 0;
        }

        $lastProcessedId = (int)max($ids);

        try {
            $this->quoteDelete->deleteByIds($ids);
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Unable to delete expired quotes (IDs: %s): %s', implode(', ', $ids), $e->getMessage())
            );
        }

        return count($ids);
    }
}
