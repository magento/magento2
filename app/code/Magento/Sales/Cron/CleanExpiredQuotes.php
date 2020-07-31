<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Cron;

use Exception;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Select;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Model\ResourceModel\Quote as ResourceQuote;
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
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartInterfaceFactory
     */
    private $quoteFactory;

    /**
     * @var ResourceQuote
     */
    private $quoteResource;

    /**
     * @var QueryGenerator
     */
    private $queryGenerator;

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
     * @param CartRepositoryInterface $quoteRepository
     * @param CartInterfaceFactory $quoteFactory
     * @param ResourceQuote $quoteResource
     * @param QueryGenerator $queryGenerator
     * @param LoggerInterface $logger
     * @param int $batchSize
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ExpiredQuotesCollection $expiredQuotesCollection,
        CartRepositoryInterface $quoteRepository,
        CartInterfaceFactory $quoteFactory,
        ResourceQuote $quoteResource,
        QueryGenerator $queryGenerator,
        LoggerInterface $logger,
        int $batchSize = 1000
    ) {
        $this->storeManager = $storeManager;
        $this->expiredQuotesCollection = $expiredQuotesCollection;
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
        $this->queryGenerator = $queryGenerator;
        $this->logger = $logger;
        $this->batchSize = $batchSize;
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
            /** @var QuoteCollection $quoteCollection */
            $quoteCollection = $this->expiredQuotesCollection->getExpiredQuotes($store);

            $select = $quoteCollection->getSelect()
                ->reset(Select::COLUMNS)
                ->columns(['main_table.' . $this->quoteResource->getIdFieldName()]);
            $queries = $this->queryGenerator->generate(
                $this->quoteResource->getIdFieldName(),
                $select,
                $this->batchSize
            );
            foreach ($queries as $query) {
                $this->deleteQuotes($query);
            }
        }
    }

    /**
     * Deletes all quotes from select
     *
     * @param Select $query
     */
    private function deleteQuotes(Select $query): void
    {
        $quoteIds = $this->quoteResource->getConnection()->fetchCol($query);
        foreach ($quoteIds as $quoteId) {
            $quote = $this->quoteFactory->create();
            $quote->setId((int) $quoteId);

            try {
                $this->quoteRepository->delete($quote);
            } catch (Exception $e) {
                $message = sprintf('Unable to delete expired quote (ID: %s)', $quoteId);
                $this->logger->error($message, ['exception' => $e]);
            }
        }
    }
}
