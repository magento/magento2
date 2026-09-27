<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\ResourceModel;

use Iterator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Persistent\Helper\Data;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Iterates, one quote at a time, over persistent quotes expired for a given store.
 *
 * Internally fetches quotes in batches (to bound memory and query size), tracking an
 * entity_id cursor between batches, but exposes a flat, item-level Iterator so callers
 * can foreach over quotes directly instead of managing batches themselves.
 */
class ExpiredPersistentQuotesCollection implements Iterator
{
    /**
     * @var Collection|null
     */
    private ?Collection $currentBatch = null;

    /**
     * @var Iterator|null
     */
    private ?Iterator $batchIterator = null;

    /**
     * @var Quote|null
     */
    private ?Quote $current = null;

    /**
     * @var int
     */
    private int $lastProcessedId = 0;

    /**
     * @var bool
     */
    private bool $initialized = false;

    /**
     * @var int|null
     */
    private ?int $lifetime = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreInterface $store
     * @param CollectionFactory $quoteCollectionFactory
     * @param int $batchSize
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreInterface $store,
        private readonly CollectionFactory $quoteCollectionFactory,
        private readonly int $batchSize
    ) {
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->ensureInitialized();
        return $this->current;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        $this->ensureInitialized();
        return $this->current?->getId();
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        $this->ensureInitialized();
        return $this->current !== null;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->initialized = false;
        $this->lastProcessedId = 0;
        $this->currentBatch = null;
        $this->batchIterator = null;
        $this->current = null;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->ensureInitialized();
        $this->advanceToNextItem();
    }

    /**
     * Lazily rewind on first use, so current()/key()/valid()/next() behave correctly
     * even if called before an explicit rewind() (matching a plain PHP array's
     * internal pointer, which is already positioned at the first element).
     *
     * @return void
     */
    private function ensureInitialized(): void
    {
        if (!$this->initialized) {
            $this->initialized = true;
            $this->advanceToNextItem();
        }
    }

    /**
     * Advance to the next available quote, crossing batch boundaries as needed.
     *
     * @return void
     */
    private function advanceToNextItem(): void
    {
        while ($this->batchIterator === null || !$this->batchIterator->valid()) {
            if (!$this->loadNextBatch()) {
                $this->current = null;
                return;
            }
        }

        /** @var Quote $quote */
        $quote = $this->batchIterator->current();
        $this->batchIterator->next();
        $this->current = $quote;
        $this->lastProcessedId = (int)$quote->getId();
    }

    /**
     * Fetch the next batch of expired quotes, releasing the previous batch's memory.
     *
     * @return bool Whether a non-empty batch was loaded.
     */
    private function loadNextBatch(): bool
    {
        if ($this->currentBatch !== null) {
            $this->currentBatch->clear();
        }
        $this->currentBatch = $this->buildBatchQuery();
        $this->batchIterator = $this->currentBatch->getIterator();

        return $this->batchIterator->valid();
    }

    /**
     * Build the collection selecting the next batch of expired persistent quotes.
     *
     * @return Collection
     */
    private function buildBatchQuery(): Collection
    {
        $this->lifetime ??= (int) $this->scopeConfig->getValue(
            Data::XML_PATH_LIFE_TIME,
            ScopeInterface::SCOPE_WEBSITE,
            $this->store->getWebsiteId()
        );

        $lastLoginCondition = gmdate("Y-m-d H:i:s", time() - $this->lifetime);

        /** @var $quotes Collection */
        $quotes = $this->quoteCollectionFactory->create();
        $quotes->addFieldToFilter('main_table.store_id', (int)$this->store->getId());
        $quotes->addFieldToFilter('main_table.updated_at', ['lt' => $lastLoginCondition]);
        $quotes->addFieldToFilter('main_table.is_persistent', 1);
        $quotes->addFieldToFilter('main_table.entity_id', ['gt' => $this->lastProcessedId]);
        $quotes->setOrder('entity_id', Collection::SORT_ORDER_ASC);
        $quotes->setPageSize($this->batchSize);

        // A persistent quote is expired if the owning customer either:
        //case 1 - logged in and explicitly logged out (regardless of how long ago), or
        //case 2 - logged in and never logged out, but that session has since expired, or
        //case 3 - logged in, logged out, logged in again, and that second session has since expired
        $quotes->getSelect()
            ->joinLeft(
                ['cl' => $quotes->getTable('customer_log')],
                'cl.customer_id = main_table.customer_id',
                []
            )->where(
                '(cl.last_logout_at IS NOT NULL AND cl.last_login_at < cl.last_logout_at)
                OR (cl.last_login_at < "' . $lastLoginCondition . '"
                    AND (cl.last_logout_at IS NULL OR cl.last_login_at > cl.last_logout_at))'
            );

        return $quotes;
    }
}
