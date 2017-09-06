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
    public function create(IndexName $indexName, string $connectionName = ResourceConnection::DEFAULT_CONNECTION): void;

    /**
     * @param IndexName $indexName
     * @param string $connectionName
     * @return void
     */
    public function delete(IndexName $indexName, string $connectionName = ResourceConnection::DEFAULT_CONNECTION): void;
}
