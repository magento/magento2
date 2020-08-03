<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Cron;

use Exception;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Cron job for cleaning expired Quotes
 */
class CleanExpiredQuotes
{
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
     * @param StoreManagerInterface $storeManager
     * @param ExpiredQuotesCollection $expiredQuotesCollection
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ExpiredQuotesCollection $expiredQuotesCollection,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->expiredQuotesCollection = $expiredQuotesCollection;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
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
            /** @var $quoteCollection QuoteCollection */
            $quoteCollection = $this->expiredQuotesCollection->getExpiredQuotes($store);
            $quoteCollection->setPageSize(50);

            // Last page returns 1 even when we don't have any results
            $lastPage = $quoteCollection->getSize() ? $quoteCollection->getLastPageNumber() : 0;

            for ($currentPage = $lastPage; $currentPage >= 1; $currentPage--) {
                $quoteCollection->setCurPage($currentPage);

                $this->deleteQuotes($quoteCollection);
            }
        }
    }

    /**
     * Deletes all quotes in collection
     *
     * @param QuoteCollection $quoteCollection
     */
    private function deleteQuotes(QuoteCollection $quoteCollection): void
    {
        foreach ($quoteCollection as $quote) {
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
    }
}
