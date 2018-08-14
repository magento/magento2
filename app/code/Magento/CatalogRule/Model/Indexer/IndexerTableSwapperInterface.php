<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

/**
 * Manage additional tables used while building new index to preserve
 * index tables until the process finishes.
 */
interface IndexerTableSwapperInterface
{
    /**
     * Get working table name used to build index.
     *
     * @param string $originalTable
     *
     * @return string
     */
    public function getWorkingTableName(string $originalTable): string;

    /**
     * Swap working tables with actual tables to save new indexes.
     *
     * @param string[] $originalTablesNames
     *
     * @return void
     */
    public function swapIndexTables(array $originalTablesNames);
}
