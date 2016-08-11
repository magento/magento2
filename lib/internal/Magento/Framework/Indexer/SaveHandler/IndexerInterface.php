<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Index Engine Interface
 */
namespace Magento\Framework\Indexer\SaveHandler;

use Magento\Framework\Search\Request\Dimension;

/**
 * Indexer persistence handler
 *
 * @api
 */
interface IndexerInterface
{
    /**
     * Add entities data to index
     *
     * @param Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface
     */
    public function saveIndex($dimensions, \Traversable $documents);

    /**
     * Remove entities data from index
     *
     * @param Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface
     */
    public function deleteIndex($dimensions, \Traversable $documents);

    /**
     * Remove all data from index
     *
     * @param Dimension[] $dimensions
     * @return IndexerInterface
     */
    public function cleanIndex($dimensions);

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function isAvailable();
}
