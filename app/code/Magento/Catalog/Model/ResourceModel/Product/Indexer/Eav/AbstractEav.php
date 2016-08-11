<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Catalog Product Eav Attributes abstract indexer resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractEav extends \Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = null
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct($context, $tableStrategy, $eavConfig, $connectionName);
    }

    /**
     * Rebuild all index data
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->beginTransaction();
        try {
            $this->clearTemporaryIndexTable();
            $this->_prepareIndex();
            $this->_prepareRelationIndex();
            $this->_removeNotVisibleEntityFromIndex();
            $this->syncData();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Rebuild index data by entities
     *
     *
     * @param int|array $processIds
     * @return $this
     * @throws \Exception
     */
    public function reindexEntities($processIds)
    {
        $connection = $this->getConnection();

        $this->clearTemporaryIndexTable();

        if (!is_array($processIds)) {
            $processIds = [$processIds];
        }

        $parentIds = $this->getRelationsByChild($processIds);
        if ($parentIds) {
            $processIds = array_unique(array_merge($processIds, $parentIds));
        }
        $childIds = $this->getRelationsByParent($processIds);
        if ($childIds) {
            $processIds = array_unique(array_merge($processIds, $childIds));
        }

        $this->_prepareIndex($processIds);
        $this->_prepareRelationIndex($processIds);
        $this->_removeNotVisibleEntityFromIndex();

        $connection->beginTransaction();
        try {
            // remove old index
            $where = $connection->quoteInto('entity_id IN(?)', $processIds);
            $connection->delete($this->getMainTable(), $where);

            // insert new index
            $this->insertFromTable($this->getIdxTable(), $this->getMainTable());
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Rebuild index data by attribute id
     * If attribute is not indexable remove data by attribute
     *
     *
     * @param int $attributeId
     * @param bool $isIndexable
     * @return $this
     */
    public function reindexAttribute($attributeId, $isIndexable = true)
    {
        if (!$isIndexable) {
            $this->_removeAttributeIndexData($attributeId);
        } else {
            $this->clearTemporaryIndexTable();

            $this->_prepareIndex(null, $attributeId);
            $this->_prepareRelationIndex();
            $this->_removeNotVisibleEntityFromIndex();

            $this->_synchronizeAttributeIndexData($attributeId);
        }

        return $this;
    }

    /**
     * Prepare data index for indexable attributes
     *
     * @param array $entityIds      the entity ids limitation
     * @param int $attributeId      the attribute id limitation
     * @return $this
     */
    abstract protected function _prepareIndex($entityIds = null, $attributeId = null);

    /**
     * Remove Not Visible products from temporary data index
     *
     * @return $this
     */
    protected function _removeNotVisibleEntityFromIndex()
    {
        $connection = $this->getConnection();
        $idxTable = $this->getIdxTable();

        $select = $connection->select()->from($idxTable, null);

        $select->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.entity_id = {$idxTable}.entity_id",
            []
        );
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $condition = $connection->quoteInto('=?', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $this->_addAttributeToSelect(
            $select,
            'visibility',
            "cpe.{$linkField}",
            $idxTable . '.store_id',
            $condition
        );

        $query = $select->deleteFromSelect($idxTable);
        $connection->query($query);

        return $this;
    }

    /**
     * Prepare data index select for product relations
     *
     * @param array $parentIds the parent entity ids limitation
     * @return \Magento\Framework\DB\Select
     */
    protected function _prepareRelationIndexSelect($parentIds = null)
    {
        $connection = $this->getConnection();
        $idxTable = $this->getIdxTable();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()->from(
            ['l' => $this->getTable('catalog_product_relation')],
            []
        )->joinLeft(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.' . $linkField .' = l.parent_id',
            ['e.entity_id as parent_id']
        )->join(
            ['cs' => $this->getTable('store')],
            '',
            []
        )->join(
            ['i' => $idxTable],
            'l.child_id = i.entity_id AND cs.store_id = i.store_id',
            ['attribute_id', 'store_id', 'value']
        )->group(
            ['parent_id', 'i.attribute_id', 'i.store_id', 'i.value']
        );
        if ($parentIds !== null) {
            $select->where('e.entity_id IN(?)', $parentIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('l.parent_id'),
                'website_field' => new \Zend_Db_Expr('cs.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            ]
        );

        return $select;
    }

    /**
     * Prepare data index for product relations
     *
     * @param array $parentIds the parent entity ids limitation
     * @return $this
     */
    protected function _prepareRelationIndex($parentIds = null)
    {
        $connection = $this->getConnection();
        $idxTable = $this->getIdxTable();

        $query = $connection->insertFromSelect(
            $this->_prepareRelationIndexSelect($parentIds),
            $idxTable,
            [],
            \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
        );
        $connection->query($query);

        return $this;
    }

    /**
     * Retrieve condition for retrieve indexable attribute select
     * the catalog/eav_attribute table must have alias is ca
     *
     * @return string
     */
    protected function _getIndexableAttributesCondition()
    {
        $conditions = [
            'ca.is_filterable_in_search > 0',
            'ca.is_visible_in_advanced_search > 0',
            'ca.is_filterable > 0',
        ];

        return implode(' OR ', $conditions);
    }

    /**
     * Remove index data from index by attribute id
     *
     * @param int $attributeId
     * @return $this
     * @throws \Exception
     */
    protected function _removeAttributeIndexData($attributeId)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $where = $connection->quoteInto('attribute_id = ?', $attributeId);
            $connection->delete($this->getMainTable(), $where);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Synchronize temporary index table with index table by attribute id
     *
     * @param int $attributeId
     * @return $this
     * @throws \Exception
     */
    protected function _synchronizeAttributeIndexData($attributeId)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            // remove index by attribute
            $where = $connection->quoteInto('attribute_id = ?', $attributeId);
            $connection->delete($this->getMainTable(), $where);

            // insert new index
            $this->insertFromTable($this->getIdxTable(), $this->getMainTable());
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }
}
