<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.0.0
 */
interface IndexerInterface
{
    /**
     * Add entities data to index
     *
     * @param Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface
     * @since 2.0.0
     */
    public function saveIndex($dimensions, \Traversable $documents);

    /**
     * Remove entities data from index
     *
     * @param Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface
     * @since 2.0.0
     */
    public function deleteIndex($dimensions, \Traversable $documents);

    /**
     * Remove all data from index
     *
     * @param Dimension[] $dimensions
     * @return IndexerInterface
     * @since 2.0.0
     */
    public function cleanIndex($dimensions);

    /**
     * Define if engine is available
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAvailable();
}
