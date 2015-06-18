<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Index Engine Interface
 */
namespace Magento\Framework\Search;

interface IndexerInterface
{
    /**
     * Add entities data to index
     *
     * @param array $entityIndexes
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function saveIndex(array $entityIndexes);

    /**
     * Remove entities data from index
     *
     * @param array $entityIndexes
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function deleteIndex(array $entityIndexes);

    /**
     * Remove all data from index
     *
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function cleanIndex();

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function isAvailable();
}
