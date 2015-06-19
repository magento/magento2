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
     * @param \Traversable $documents
     * @return IndexerInterface
     */
    public function saveIndex(Dimension $dimension, \Traversable $documents);

    /**
     * Remove entities data from index
     *
     * @param Dimension $dimension
     * @param \Traversable $documents
     * @return IndexerInterface
     */
    public function deleteIndex(Dimension $dimension, \Traversable $documents);

    /**
     * Remove all data from index
     *
     * @param Dimension $dimension
     * @return IndexerInterface
     */
    public function cleanIndex(Dimension $dimension);

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function isAvailable();
}
