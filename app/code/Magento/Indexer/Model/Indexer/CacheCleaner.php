<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface;

/**
 * Clean cache for reindexed entities after executed action.
 */
class CacheCleaner
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
     * Defer cache cleaning until after execute full
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecuteFull(ActionInterface $subject)
    {
        $this->cacheCleaner->start();
    }

    /**
     * Clean cache after full reindex full
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteFull(ActionInterface $subject)
    {
        $this->cacheCleaner->flush();
    }

    /**
     * Defer cache cleaning until after execute list
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecuteList(ActionInterface $subject)
    {
        $this->cacheCleaner->start();
    }

    /**
     * Clean cache after reindexed list.
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(ActionInterface $subject)
    {
        $this->cacheCleaner->flush();
    }

    /**
     * Defer cache cleaning until after execute row
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecuteRow(ActionInterface $subject)
    {
        $this->cacheCleaner->start();
    }

    /**
     * Clean cache after reindexed row.
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteRow(ActionInterface $subject)
    {
        $this->cacheCleaner->flush();
    }

    /**
     * Clean cache when execute full fails, as the after plugin is skipped then
     *
     * @param ActionInterface $subject
     * @param callable $proceed
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteFull(ActionInterface $subject, callable $proceed)
    {
        return $this->flushOnException($proceed);
    }

    /**
     * Clean cache when execute list fails, as the after plugin is skipped then
     *
     * @param ActionInterface $subject
     * @param callable $proceed
     * @param array $ids
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteList(ActionInterface $subject, callable $proceed, array $ids)
    {
        return $this->flushOnException($proceed, [$ids]);
    }

    /**
     * Clean cache when execute row fails, as the after plugin is skipped then
     *
     * @param ActionInterface $subject
     * @param callable $proceed
     * @param int $id
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteRow(ActionInterface $subject, callable $proceed, $id)
    {
        return $this->flushOnException($proceed, [$id]);
    }

    /**
     * Balance the start() of the before plugin and clean cache for data changed before the failure
     *
     * @param callable $proceed
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
    private function flushOnException(callable $proceed, array $arguments = [])
    {
        try {
            return $proceed(...$arguments);
        } catch (\Throwable $exception) {
            $this->cacheCleaner->flush();
            throw $exception;
        }
    }
}
