<?php declare(strict_types=1);
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Mview\View;

/**
 * Interface \Magento\Framework\Mview\View\ChangelogBatchWalkerInterface
 *
 */
interface ChangelogBatchWalkerInterface
{
    /**
     * Walk through batches
     *
     * @param ChangelogInterface $changelog
     * @param int $fromVersionId
     * @param int $lastVersionId
     * @param int $batchSize
     * @return iterable
     */
    public function walk(
        ChangelogInterface $changelog,
        int                $fromVersionId,
        int                $lastVersionId,
        int                $batchSize
    ): iterable;
}
