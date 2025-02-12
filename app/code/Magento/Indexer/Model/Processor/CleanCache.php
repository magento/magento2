<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Processor;

use Magento\Indexer\Model\Indexer\DeferredCacheCleaner;

/**
 * Clear cache after reindex
 */
class CleanCache
{
    /**
     * @var DeferredCacheCleaner
     */
    private $cacheCleaner;

    /**
     * @param DeferredCacheCleaner $cacheCleaner
     */
    public function __construct(
        DeferredCacheCleaner $cacheCleaner
    ) {
        $this->cacheCleaner = $cacheCleaner;
    }

    /**
     * Defer cache cleaning until after update mview
     *
     * @param \Magento\Indexer\Model\Processor $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeUpdateMview(\Magento\Indexer\Model\Processor $subject)
    {
        $this->cacheCleaner->start();
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
        $this->cacheCleaner->flush();
    }

    /**
     * Defer cache cleaning until after reindex invalid indexers
     *
     * @param \Magento\Indexer\Model\Processor $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeReindexAllInvalid(\Magento\Indexer\Model\Processor $subject)
    {
        $this->cacheCleaner->start();
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
        $this->cacheCleaner->flush();
    }
}
