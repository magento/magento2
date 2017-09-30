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
     * @return void
     */
    public function saveIndex(IndexName $indexName, \Traversable $documents);

    /**
     * Create the index i not exits or remove sku list from the index to rebuild
     * @param IndexName $indexName
     * @param array $skuList
     * @param string $connectionName
     * @return void
     */
    public function cleanUp(
        IndexName $indexName,
        array $skuList,
        string $connectionName = ResourceConnection::DEFAULT_CONNECTION
    );

    /**
     * Remove data from index
     *
     * @param IndexName $indexName
     * @param \Traversable $documents
     * @return void
     */
    public function deleteIndex(IndexName $indexName, \Traversable $documents);
}
