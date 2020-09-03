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
 * Interface \Magento\Framework\Mview\View\ChangeLogBatchIteratorInterface
 *
 */
interface ChangeLogBatchIteratorInterface
{
    /**
     * Walk through batches
     *
     * @param array $changeLogData
     * @param $fromVersionId
     * @param int $batchSize
     * @return mixed
     * @throws ChangelogTableNotExistsException
     */
    public function walk(array $changeLogData, $fromVersionId, int $batchSize);
}
