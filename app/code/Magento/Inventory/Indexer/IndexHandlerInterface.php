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
    public function saveIndex(IndexName $indexName, \Traversable $documents): void;

    /**
     * Remove data from index
     *
     * @param IndexName $indexName
     * @param \Traversable $documents
     * @return void
     */
    public function deleteIndex(IndexName $indexName, \Traversable $documents): void;
}
