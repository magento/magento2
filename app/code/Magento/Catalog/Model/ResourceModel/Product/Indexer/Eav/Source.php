<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Catalog Product Eav Select and Multiply Select Attributes Indexer resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Source extends AbstractEav
{
    const TRANSIT_PREFIX = 'transit_';

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param null|string $connectionName
     * @param \Magento\Eav\Api\AttributeRepositoryInterface|null $attributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder|null $criteriaBuilder
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        $connectionName = null,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository = null,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder = null
    ) {
        parent::__construct(
            $context,
            $tableStrategy,
            $eavConfig,
            $eventManager,
            $connectionName
        );
        $this->_resourceHelper = $resourceHelper;
        $this->attributeRepository = $attributeRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);
        $this->criteriaBuilder = $criteriaBuilder
            ?: \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
    }

    /**
     * Initialize connection and define main index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav', 'entity_id');
    }

    /**
     * Retrieve indexable eav attribute ids
     *
     * @param bool $multiSelect
     * @return array
     */
    protected function _getIndexableAttributes($multiSelect)
    {
        $select = $this->getConnection()->select()->from(
            ['ca' => $this->getTable('catalog_eav_attribute')],
            'attribute_id'
        )->join(
            ['ea' => $this->getTable('eav_attribute')],
            'ca.attribute_id = ea.attribute_id',
            []
        )->where(
            $this->_getIndexableAttributesCondition()
        );

        if ($multiSelect == true) {
            $select->where('ea.backend_type = ?', 'varchar')->where('ea.frontend_input = ?', 'multiselect');
        } else {
            $select->where('ea.backend_type = ?', 'int')->where('ea.frontend_input IN( ? )', ['select', 'boolean']);
        }

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Prepare data index for indexable attributes
     *
     * @param array $entityIds the entity ids limitation
     * @param int $attributeId the attribute id limitation
     * @return $this
     */
    protected function _prepareIndex($entityIds = null, $attributeId = null)
    {
        $this->_prepareSelectIndex($entityIds, $attributeId);
        $this->_prepareMultiselectIndex($entityIds, $attributeId);

        return $this;
    }

    /**
     * Prepare data index for indexable select attributes
     *
     * @param array $entityIds the entity ids limitation
     * @param int $attributeId the attribute id limitation
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareSelectIndex($entityIds = null, $attributeId = null)
    {
        $connection = $this->getConnection();
        $idxTable = $this->getIdxTable();
        // prepare select attributes
        $attrIds = $attributeId === null ? $this->_getIndexableAttributes(false) : [$attributeId];
        if (!$attrIds) {
            return $this;
        }
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $attrIdsFlat = implode(',', array_map('intval', $attrIds));
        $ifNullSql = $connection->getIfNullSql('pis.value', 'COALESCE(ds.value, dd.value)');

        /**@var $select \Magento\Framework\DB\Select*/
        $select = $connection->select()->distinct(true)->from(
            ['s' => $this->getTable('store')],
            []
        )->joinLeft(
            ['dd' => $this->getTable('catalog_product_entity_int')],
            'dd.store_id = 0',
            []
        )->joinLeft(
            ['ds' => $this->getTable('catalog_product_entity_int')],
            "ds.store_id = s.store_id AND ds.attribute_id = dd.attribute_id AND " .
            "ds.{$productIdField} = dd.{$productIdField}",
            []
        )->joinLeft(
            ['d2d' => $this->getTable('catalog_product_entity_int')],
            sprintf(
                "d2d.store_id = 0 AND d2d.{$productIdField} = dd.{$productIdField} AND d2d.attribute_id = %s",
                $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status')->getId()
            ),
            []
        )->joinLeft(
            ['d2s' => $this->getTable('catalog_product_entity_int')],
            "d2s.store_id = s.store_id AND d2s.attribute_id = d2d.attribute_id AND " .
            "d2s.{$productIdField} = d2d.{$productIdField}",
            []
        )->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.{$productIdField} = dd.{$productIdField}",
            []
        )->joinLeft(
            ['pis' => $this->getTable('catalog_product_entity_int')],
            "pis.{$productIdField} = cpe.{$productIdField} " .
            "AND pis.attribute_id = dd.attribute_id AND pis.store_id = s.store_id",
            []
        )->where(
            's.store_id != 0'
        )->where(
            '(ds.value IS NOT NULL OR dd.value IS NOT NULL)'
        )->where(
            (new \Zend_Db_Expr('COALESCE(d2s.value, d2d.value)')) . ' = ' . ProductStatus::STATUS_ENABLED
        )->where(
            "dd.attribute_id IN({$attrIdsFlat})"
        )->where(
            'NOT(pis.value IS NULL AND pis.value_id IS NOT NULL)'
        )->where(
            $ifNullSql . ' IS NOT NULL'
        )->columns(
            [
                'cpe.entity_id',
                'dd.attribute_id',
                's.store_id',
                'value' => new \Zend_Db_Expr('COALESCE(ds.value, dd.value)'),
                'cpe.entity_id',
            ]
        );

        if ($entityIds !== null) {
            $ids = implode(',', array_map('intval', $entityIds));
            $select->where("cpe.entity_id IN({$ids})");
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('cpe.entity_id'),
                'website_field' => new \Zend_Db_Expr('s.website_id'),
                'store_field' => new \Zend_Db_Expr('s.store_id'),
            ]
        );
        $query = $select->insertFromSelect($idxTable);
        $connection->query($query);
        return $this;
    }

    /**
     * Prepare data index for indexable multiply select attributes
     *
     * @param array $entityIds the entity ids limitation
     * @param int $attributeId the attribute id limitation
     * @return $this
     */
    protected function _prepareMultiselectIndex($entityIds = null, $attributeId = null)
    {
        $connection = $this->getConnection();

        // prepare multiselect attributes
        $attrIds = $attributeId === null ? $this->_getIndexableAttributes(true) : [$attributeId];

        if (!$attrIds) {
            return $this;
        }
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();

        // load attribute options
        $options = [];
        $select = $connection->select()->from(
            $this->getTable('eav_attribute_option'),
            ['attribute_id', 'option_id']
        )->where('attribute_id IN(?)', $attrIds);
        $query = $select->query();
        while ($row = $query->fetch()) {
            $options[$row['attribute_id']][$row['option_id']] = true;
        }

        // Retrieve any custom source model options
        $sourceModelOptions = $this->getMultiSelectAttributeWithSourceModels($attrIds);
        $options = array_replace_recursive($options, $sourceModelOptions);

        // prepare get multiselect values query
        $productValueExpression = $connection->getCheckSql('pvs.value_id > 0', 'pvs.value', 'pvd.value');
        $select = $connection->select()->from(
            ['pvd' => $this->getTable('catalog_product_entity_varchar')],
            []
        )->join(
            ['cs' => $this->getTable('store')],
            '',
            []
        )->joinLeft(
            ['pvs' => $this->getTable('catalog_product_entity_varchar')],
            "pvs.{$productIdField} = pvd.{$productIdField} AND pvs.attribute_id = pvd.attribute_id"
            . ' AND pvs.store_id=cs.store_id',
            []
        )->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.{$productIdField} = pvd.{$productIdField}",
            []
        )->where(
            'pvd.store_id=?',
            $connection->getIfNullSql('pvs.store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        )->where(
            'cs.store_id!=?',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        )->where(
            'pvd.attribute_id IN(?)',
            $attrIds
        )->where(
            'cpe.entity_id IS NOT NULL'
        )->columns(
            [
                'entity_id' => 'cpe.entity_id',
                'attribute_id' => 'attribute_id',
                'store_id' => 'cs.store_id',
                'value' => $productValueExpression,
                'source_id' => 'cpe.entity_id',
            ]
        );

        $statusCond = $connection->quoteInto('=?', ProductStatus::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', "pvd.{$productIdField}", 'cs.store_id', $statusCond);

        if ($entityIds !== null) {
            $select->where('cpe.entity_id IN(?)', $entityIds);
        }
        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('cpe.entity_id'),
                'website_field' => new \Zend_Db_Expr('cs.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id'),
            ]
        );

        $this->saveDataFromSelect($select, $options);

        return $this;
    }

    /**
     * Get options for multiselect attributes using custom source models
     * Based on @maderlock's fix from:
     * https://github.com/magento/magento2/issues/417#issuecomment-265146285
     *
     * @param array $attrIds
     *
     * @return array
     */
    private function getMultiSelectAttributeWithSourceModels($attrIds)
    {
        // Add options from custom source models
        $this->criteriaBuilder
                ->addFilter('attribute_id', $attrIds, 'in')
                ->addFilter('source_model', true, 'notnull');
        $criteria = $this->criteriaBuilder->create();
        $attributes = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $criteria
        )->getItems();
        
        $options = [];
        foreach ($attributes as $attribute) {
            $sourceModelOptions = $attribute->getOptions();
            // Add options to list used below
            foreach ($sourceModelOptions as $option) {
                $options[$attribute->getAttributeId()][$option->getValue()] = true;
            }
        }

        return $options;
    }

    /**
     * Save a data to temporary source index table
     *
     * @param array $data
     * @return $this
     */
    protected function _saveIndexData(array $data)
    {
        if (!$data) {
            return $this;
        }
        $connection = $this->getConnection();
        $connection->insertArray(
            $this->getIdxTable(),
            ['entity_id', 'attribute_id', 'store_id', 'value', 'source_id'],
            $data
        );
        return $this;
    }

    /**
     * Retrieve temporary source index table name
     *
     * @param string|null $table
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName('catalog_product_index_eav');
    }

    /**
     * Save data from select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param array $options
     * @return void
     */
    private function saveDataFromSelect(\Magento\Framework\DB\Select $select, array $options)
    {
        $i = 0;
        $data = [];
        $query = $select->query();
        while ($row = $query->fetch()) {
            $values = explode(',', $row['value']);
            foreach ($values as $valueId) {
                if (isset($options[$row['attribute_id']][$valueId])) {
                    $data[] = [$row['entity_id'], $row['attribute_id'], $row['store_id'], $valueId, $row['source_id']];
                    $i++;
                    if ($i % 10000 == 0) {
                        $this->_saveIndexData($data);
                        $data = [];
                    }
                }
            }
        }

        $this->_saveIndexData($data);
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

        if (!$this->tableStrategy->getUseIdxTable()) {
            $additionalIdxTable = $connection->getTableName(self::TRANSIT_PREFIX . $this->getIdxTable());
            $connection->createTemporaryTableLike($additionalIdxTable, $idxTable);

            $query = $connection->insertFromSelect(
                $this->_prepareRelationIndexSelect($parentIds),
                $additionalIdxTable,
                []
            );
            $connection->query($query);

            $select = $connection->select()->from($additionalIdxTable);
            $query = $connection->insertFromSelect(
                $select,
                $idxTable,
                [],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
            );
            $connection->query($query);

            $connection->dropTemporaryTable($additionalIdxTable);
        } else {
            $query = $connection->insertFromSelect(
                $this->_prepareRelationIndexSelect($parentIds),
                $idxTable,
                [],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
            );
            $connection->query($query);
        }
        return $this;
    }
}
