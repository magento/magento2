<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMultiDimensionalIndexerApi\Model;

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
    public function saveIndex(IndexName $indexName, \Traversable $documents, string $connectionName): void;

    /**
     * Remove given documents from Index
     *
     * @param IndexName $indexName
     * @param \Traversable $documents
     * @param string $connectionName
     * @return void
     */
    public function cleanIndex(IndexName $indexName, \Traversable $documents, string $connectionName): void;
}
