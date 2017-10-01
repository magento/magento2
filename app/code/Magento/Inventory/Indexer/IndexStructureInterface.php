<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

/**
 * Represent manipulation with index structure
 *
 * @api
 */
interface IndexStructureInterface
{
    /**
     * Create the Index Structure if is not existing
     *
     * @param IndexName $indexName
     * @param string $connectionName
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function create(IndexName $indexName, string $connectionName);

    /**
     * Delete the given Index from the database
     *
     * @param IndexName $indexName
     * @param string $connectionName
     * @return void
     */
    public function delete(IndexName $indexName, string $connectionName);

    /**
     * Checks is the index exits.
     *
     * @param IndexName $indexName
     * @param string $connectionName
     * @return bool
     */
    public function isExist(IndexName $indexName, string $connectionName): bool;
}
