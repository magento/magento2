<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogSearch Index Engine Interface
 */
namespace Magento\CatalogSearch\Model\Resource;

interface EngineInterface
{
    /**
     * Add entity data to fulltext search table
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     * @param string $entity 'product'|'cms'
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function saveEntityIndex($entityId, $storeId, $index, $entity = 'product');

    /**
     * Multi add entities data to fulltext search table
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @param string $entity 'product'|'cms'
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function saveEntityIndexes($storeId, $entityIndexes, $entity = 'product');

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return array
     */
    public function getAllowedVisibility();

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex();

    /**
     * Proepare attribute value to store in index
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param mixed $value
     * @return mixed
     */
    public function processAttributeValue($attribute, $value);

    /**
     * Remove entity data from fulltext search table
     *
     * @param int $storeId
     * @param int $entityId
     * @param string $entity 'product'|'cms'
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function cleanIndex($storeId = null, $entityId = null, $entity = 'product');

    /**
     * Prepare index array as a string glued by separator
     *
     * @param array $index
     * @param string $separator
     * @return string
     */
    public function prepareEntityIndex($index, $separator = ' ');

    /**
     * Return resource model for the full text search
     *
     * @return \Magento\Framework\Model\Resource\AbstractResource
     */
    public function getResource();

    /**
     * Return resource collection model for the full text search
     *
     * @return \Magento\Framework\Data\Collection\Db
     */
    public function getResourceCollection();

    /**
     * Retrieve fulltext search result data collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getResultCollection();

    /**
     * Retrieve advanced search result data collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getAdvancedResultCollection();

    /**
     * Define if Layered Navigation is allowed
     *
     * @return bool
     */
    public function isLayeredNavigationAllowed();

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function test();
}
