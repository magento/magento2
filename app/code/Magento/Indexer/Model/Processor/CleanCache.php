<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Processor;

class CleanCache
{
    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @param \Magento\Framework\Indexer\CacheContext $context
     * @param \Magento\Framework\Event\Manager $eventManager
     */
    public function __construct(
        \Magento\Framework\Indexer\CacheContext $context,
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->context = $context;
        $this->eventManager = $eventManager;
    }

    /**
     * Update indexer views
     *
     * @param \Magento\Indexer\Model\Processor $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateMview(\Magento\Indexer\Model\Processor $subject)
    {
        $this->eventManager->dispatch('clean_cache_after_reindex', ['object' => $this->context]);
    }

    /**
     * Clear cache after reindex all
     *
     * @param \Magento\Indexer\Model\Processor $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterReindexAllInvalid(\Magento\Indexer\Model\Processor $subject)
    {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->context]);
    }
}
