<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Model;

/**
 * Fetch data from database in batches
 * @api
 */
interface FetchBatchesInterface
{
    /**
     * Fetch the columns from the database table in batches
     * $modificationDateColumn contains the entities which were changed since last execution
     * to avoid fetching items that have been previously synchronized
     *
     * @param string $tableName
     * @param array $columns
     * @param string|null $modificationDateColumn
     * @return \Traversable
     */
    public function execute(string $tableName, array $columns, ?string $modificationDateColumn): \Traversable;
}
