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
     * @param int $storeId
     * @param array $entityIndexes
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function saveIndex($storeId, $entityIndexes);

    /**
     * Remove entities data from index
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function deleteIndex($storeId, $entityIndexes);

    /**
     * Remove all data from index
     *
     * @param int $storeId
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function cleanIndex($storeId = null);

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function isAvailable();
}
