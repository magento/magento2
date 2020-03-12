<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Cron;

use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection;
use Magento\Store\Model\StoreManagerInterface;

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
     * @param StoreManagerInterface $storeManager
     * @param ExpiredQuotesCollection $expiredQuotesCollection
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ExpiredQuotesCollection $expiredQuotesCollection
    ) {
        $this->storeManager = $storeManager;
        $this->expiredQuotesCollection = $expiredQuotesCollection;
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

            for ($currentPage = 1; $currentPage <= $lastPage; $currentPage++) {
                $quoteCollection->setCurPage($currentPage);

                $quoteCollection->walk('delete');

                $quoteCollection->clear();
            }
        }
    }
}
