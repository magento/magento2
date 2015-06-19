<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Index Engine Interface
 */
namespace Magento\Framework;

use Magento\Framework\Search\Request\Dimension;

interface IndexerInterface
{
    /**
     * Add entities data to index
     *
     * @param Dimension $dimension
     * @param \Traversable $entityIndexes
     * @return IndexerInterface
     */
    public function saveIndex(Dimension $dimension, \Traversable $entityIndexes);

    /**
     * Remove entities data from index
     *
     * @param Dimension $dimension
     * @param \Traversable $entityId
     * @return IndexerInterface
     */
    public function deleteIndex(Dimension $dimension, \Traversable $entityId);

    /**
     * Remove all data from index
     *
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    public function cleanIndex();

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function isAvailable();
}
