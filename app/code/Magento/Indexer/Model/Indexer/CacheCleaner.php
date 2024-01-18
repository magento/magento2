<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
}
