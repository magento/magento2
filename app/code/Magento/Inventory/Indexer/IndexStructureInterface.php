<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

use Magento\Framework\App\ResourceConnection;

/**
 * Represent manipulation with index structure
 *
 * @api
 */
interface IndexStructureInterface
{
    /**
     * If index is exist then recreate it
     *
     * @param IndexName $indexName
     * @param string $connectionName
     * @return void
     */
    public function create(IndexName $indexName, string $connectionName = ResourceConnection::DEFAULT_CONNECTION);

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
     * Delete the given Index from the database
     * @param IndexName $indexName
     * @param string $connectionName
     * @return void
     */
    public function delete(IndexName $indexName, string $connectionName = ResourceConnection::DEFAULT_CONNECTION);
}
