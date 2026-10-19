<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

/**
 * A changelog walker that reports the version covered by each returned batch.
 */
interface CursorAwareChangelogBatchWalkerInterface extends ChangelogBatchWalkerInterface
{
    /**
     * Walk through batches and report the highest changelog version included in each batch.
     *
     * Each item contains the action payload in `ids` and the highest included changelog version in `version_id`.
     *
     * @param ChangelogInterface $changelog
     * @param int $fromVersionId
     * @param int $lastVersionId
     * @param int $batchSize
     * @return iterable<array{ids: array, version_id: int}>
     */
    public function walkWithCursor(
        ChangelogInterface $changelog,
        int $fromVersionId,
        int $lastVersionId,
        int $batchSize
    ): iterable;
}
