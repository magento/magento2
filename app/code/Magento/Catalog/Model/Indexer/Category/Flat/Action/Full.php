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
namespace Magento\Catalog\Model\Indexer\Category\Flat\Action;

class Full extends \Magento\Catalog\Model\Indexer\Category\Flat\AbstractAction
{
    /**
     * Suffix for table to show it is old
     */
    const OLD_TABLE_SUFFIX = '_old';

    /**
     * Whether table changes are allowed
     *
     * @var bool
     */
    protected $allowTableChanges = true;

    /**
     * Add suffix to table name to show it is old
     *
     * @param string $tableName
     * @return string
     */
    protected function addOldTableSuffix($tableName)
    {
        return $tableName . self::OLD_TABLE_SUFFIX;
    }

    /**
     * Populate category flat tables with data
     *
     * @param \Magento\Store\Model\Store[] $stores
     * @return Full
     */
    protected function populateFlatTables(array $stores)
    {
        $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        $categories = array();
        $categoriesIds = array();
        /* @var $store \Magento\Store\Model\Store */
        foreach ($stores as $store) {
            if (!isset($categories[$store->getRootCategoryId()])) {
                $select = $this->getWriteAdapter()->select()->from(
                    $this->getWriteAdapter()->getTableName($this->getTableName('catalog_category_entity'))
                )->where(
                    'path = ?',
                    (string)$rootId
                )->orWhere(
                    'path = ?',
                    "{$rootId}/{$store->getRootCategoryId()}"
                )->orWhere(
                    'path LIKE ?',
                    "{$rootId}/{$store->getRootCategoryId()}/%"
                );
                $categories[$store->getRootCategoryId()] = $this->getWriteAdapter()->fetchAll($select);
                $categoriesIds[$store->getRootCategoryId()] = array();
                foreach ($categories[$store->getRootCategoryId()] as $category) {
                    $categoriesIds[$store->getRootCategoryId()][] = $category['entity_id'];
                }
            }
            /** @TODO Do something with chunks */
            $categoriesIdsChunks = array_chunk($categoriesIds[$store->getRootCategoryId()], 500);
            foreach ($categoriesIdsChunks as $categoriesIdsChunk) {
                $attributesData = $this->getAttributeValues($categoriesIdsChunk, $store->getId());
                $data = array();
                foreach ($categories[$store->getRootCategoryId()] as $category) {
                    if (!isset($attributesData[$category['entity_id']])) {
                        continue;
                    }
                    $category['store_id'] = $store->getId();
                    $data[] = $this->prepareValuesToInsert(
                        array_merge($category, $attributesData[$category['entity_id']])
                    );
                }
                $this->getWriteAdapter()->insertMultiple(
                    $this->addTemporaryTableSuffix($this->getMainStoreTable($store->getId())),
                    $data
                );
            }
        }

        return $this;
    }

    /**
     * Create table and add attributes as fields for specified store.
     * This routine assumes that DDL operations are allowed
     *
     * @param int $store
     * @return Full
     */
    protected function createTable($store)
    {
        $temporaryTable = $this->addTemporaryTableSuffix($this->getMainStoreTable($store));
        $table = $this->getFlatTableStructure($temporaryTable);
        $this->getWriteAdapter()->dropTable($temporaryTable);
        $this->getWriteAdapter()->createTable($table);

        return $this;
    }

    /**
     * Create category flat tables and add attributes as fields.
     * Tables are created only if DDL operations are allowed
     *
     * @param \Magento\Store\Model\Store[] $stores if empty, create tables for all stores of the application
     * @return Full
     */
    protected function createTables(array $stores = array())
    {
        if ($this->getWriteAdapter()->getTransactionLevel() > 0) {
            return $this;
        }
        if (empty($stores)) {
            $stores = $this->storeManager->getStores();
        }
        /* @var $store \Magento\Store\Model\Store */
        foreach ($stores as $store) {
            $this->createTable($store->getId());
        }

        return $this;
    }

    /**
     * Switch table (temporary becomes active, old active will be dropped)
     *
     * @param \Magento\Store\Model\Store[] $stores
     * @return Full
     */
    protected function switchTables(array $stores = array())
    {
        /** @var $store \Magento\Store\Model\Store */
        foreach ($stores as $store) {
            $activeTableName = $this->getMainStoreTable($store->getId());
            $temporaryTableName = $this->addTemporaryTableSuffix($this->getMainStoreTable($store->getId()));
            $oldTableName = $this->addOldTableSuffix($this->getMainStoreTable($store->getId()));

            //switch tables
            $tablesToRename = array();
            if ($this->getWriteAdapter()->isTableExists($activeTableName)) {
                $tablesToRename[] = array('oldName' => $activeTableName, 'newName' => $oldTableName);
            }

            $tablesToRename[] = array('oldName' => $temporaryTableName, 'newName' => $activeTableName);

            foreach ($tablesToRename as $tableToRename) {
                $this->getWriteAdapter()->renameTable($tableToRename['oldName'], $tableToRename['newName']);
            }

            //delete inactive table
            $tableToDelete = $oldTableName;

            if ($this->getWriteAdapter()->isTableExists($tableToDelete)) {
                $this->getWriteAdapter()->dropTable($tableToDelete);
            }
        }

        return $this;
    }

    /**
     * Transactional rebuild flat data from eav
     *
     * @return Full
     */
    public function reindexAll()
    {
        $this->createTables();

        if ($this->allowTableChanges) {
            $this->allowTableChanges = false;
        }
        $stores = $this->storeManager->getStores();
        $this->populateFlatTables($stores);
        $this->switchTables($stores);

        $this->allowTableChanges = true;

        return $this;
    }
}
