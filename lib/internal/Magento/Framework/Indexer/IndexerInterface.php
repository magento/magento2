<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Index Engine Interface
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\Search\Request\Dimension;

interface IndexerInterface
{
    /**
     * Add entities data to index
     *
     * @param Dimension $dimension
     * @param \Iterator $entityIndexes
     * @return IndexerInterface
     */
    public function saveIndex(Dimension $dimension, \Iterator $entityIndexes);

    /**
     * Remove entities data from index
     *
     * @param Dimension $dimension
     * @param \Iterator $entityId
     * @return IndexerInterface
     */
    public function deleteIndex(Dimension $dimension, \Iterator $entityId);

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
