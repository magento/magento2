<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Mview\Config;
use Magento\Framework\Phrase;

/**
 * Interface \Magento\Framework\Mview\View\ChangeLogBatchWalkerInterface
 *
 */
interface ChangeLogBatchWalkerInterface
{
    /**
     * Walk through batches
     *
     * @param ChangelogInterface $changelog
     * @param int $fromVersionId
     * @param int $lastVersionId
     * @param int $batchSize
     * @return mixed
     */
    public function walk(ChangelogInterface $changelog, int $fromVersionId, int $lastVersionId, int $batchSize);
}
