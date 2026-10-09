<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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

    /**
     * Clear cache when update mview fails, as the after plugin is skipped then
     *
     * @param \Magento\Indexer\Model\Processor $subject
     * @param callable $proceed
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundUpdateMview(\Magento\Indexer\Model\Processor $subject, callable $proceed)
    {
        return $this->flushOnException($proceed);
    }

    /**
     * Clear cache when reindex of invalid indexers fails, as the after plugin is skipped then
     *
     * @param \Magento\Indexer\Model\Processor $subject
     * @param callable $proceed
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundReindexAllInvalid(\Magento\Indexer\Model\Processor $subject, callable $proceed)
    {
        return $this->flushOnException($proceed);
    }

    /**
     * Balance the start() of the before plugin and clear cache for data changed before the failure
     *
     * @param callable $proceed
     * @return mixed
     * @throws \Throwable
     */
    private function flushOnException(callable $proceed)
    {
        try {
            return $proceed();
        } catch (\Throwable $exception) {
            $this->cacheCleaner->flush();
            throw $exception;
        }
    }
}
