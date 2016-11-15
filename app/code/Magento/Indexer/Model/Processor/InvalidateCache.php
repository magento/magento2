<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model\Processor;

/**
 * Class InvalidateCache
 * @deprecated
 */
class InvalidateCache
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
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param \Magento\Framework\Indexer\CacheContext $context
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\Indexer\CacheContext $context,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->context = $context;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
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
        if ($this->moduleManager->isEnabled('Magento_PageCache')) {
            $this->eventManager->dispatch('clean_cache_after_reindex', ['object' => $this->context]);
        }
    }
}
