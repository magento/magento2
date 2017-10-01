<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

/**
 * Represents manipulation with index data
 *
 * @api
 */
interface IndexHandlerInterface
{
    /**
     * Add data to index
     *
     * @param IndexName $indexName
     * @param \Traversable $documents
     * @param string $connectionName
     * @return void
     */
    public function saveIndex(IndexName $indexName, \Traversable $documents, string $connectionName);

    /**
     * Create the index i not exits or remove sku list from the index to rebuild
     *
     * @param IndexName $indexName
     * @param \Traversable $documents
     * @param string $connectionName
     * @return void
     */
    public function cleanIndex(IndexName $indexName, \Traversable $documents, string $connectionName);
}
