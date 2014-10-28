<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
