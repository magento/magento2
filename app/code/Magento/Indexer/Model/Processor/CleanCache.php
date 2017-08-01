<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Processor;

use \Magento\Framework\App\CacheInterface;

/**
 * Class \Magento\Indexer\Model\Processor\CleanCache
 *
 * @since 2.1.0
 */
class CleanCache
{
    /**
     * @var \Magento\Framework\Indexer\CacheContext
     * @since 2.1.0
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\Manager
     * @since 2.1.0
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     * @since 2.2.0
     */
    private $cache;

    /**
     * @param \Magento\Framework\Indexer\CacheContext $context
     * @param \Magento\Framework\Event\Manager $eventManager
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function afterUpdateMview(\Magento\Indexer\Model\Processor $subject)
    {
        $this->eventManager->dispatch('clean_cache_after_reindex', ['object' => $this->context]);
        if (!empty($this->context->getIdentities())) {
            $this->getCache()->clean($this->context->getIdentities());
        }
    }

    /**
     * Clear cache after reindex all
     *
     * @param \Magento\Indexer\Model\Processor $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function afterReindexAllInvalid(\Magento\Indexer\Model\Processor $subject)
    {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->context]);
        if (!empty($this->context->getIdentities())) {
            $this->getCache()->clean($this->context->getIdentities());
        }
    }

    /**
     * Get cache interface
     *
     * @return \Magento\Framework\App\CacheInterface
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getCache()
    {
        if ($this->cache === null) {
            $this->cache = \Magento\Framework\App\ObjectManager::getInstance()->get(CacheInterface::class);
        }
        return $this->cache;
    }
}
