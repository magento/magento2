<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Catalog Product Eav Select and Multiply Select Attributes Indexer resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Source extends AbstractEav
{
    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        $connectionName = null
    ) {
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($context, $tableStrategy, $eavConfig, $eventManager, $connectionName);
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
            $select->where('ea.backend_type = ?', 'int')->where('ea.frontend_input = ?', 'select');
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
        if ($attributeId === null) {
            $attrIds = $this->_getIndexableAttributes(false);
        } else {
            $attrIds = [$attributeId];
        }

        if (!$attrIds) {
            return $this;
        }
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();

        /**@var $subSelect \Magento\Framework\DB\Select*/
        $subSelect = $connection->select()->from(
            ['s' => $this->getTable('store')],
            ['store_id', 'website_id']
        )->joinLeft(
            ['dd' => $this->getTable('catalog_product_entity_int')],
            'dd.store_id = 0',
            ['attribute_id']
        )->joinLeft(
            ['ds' => $this->getTable('catalog_product_entity_int')],
            "ds.store_id = s.store_id AND ds.attribute_id = dd.attribute_id AND " .
            "ds.{$productIdField} = dd.{$productIdField}",
            ['value' =>  new \Zend_Db_Expr('COALESCE(ds.value, dd.value)')]
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
            array_unique([$productIdField, 'entity_id'])
        )->where(
            's.store_id != 0'
        )->where(
            '(ds.value IS NOT NULL OR dd.value IS NOT NULL)'
        )->where(
            (new \Zend_Db_Expr('COALESCE(d2s.value, d2d.value)')) . ' = ' . ProductStatus::STATUS_ENABLED
        )->distinct(true);

        if ($entityIds !== null) {
            $subSelect->where('cpe.entity_id IN(?)', $entityIds);
        }

        $ifNullSql = $connection->getIfNullSql('pis.value', 'pid.value');
        /**@var $select \Magento\Framework\DB\Select*/
        $select = $connection->select()->distinct(true)->from(
            ['pid' => new \Zend_Db_Expr(sprintf('(%s)', $subSelect->assemble()))],
            []
        )->joinLeft(
            ['pis' => $this->getTable('catalog_product_entity_int')],
            "pis.{$productIdField} = pid.{$productIdField}"
            .' AND pis.attribute_id = pid.attribute_id AND pis.store_id = pid.store_id',
            []
        )->columns(
            [
                'pid.entity_id',
                'pid.attribute_id',
                'pid.store_id',
                'value' => $ifNullSql,
                'pid.entity_id',
            ]
        )->where(
            'pid.attribute_id IN(?)',
            $attrIds
        );

        $select->where($ifNullSql . ' IS NOT NULL');

        /**
         * Exclude attribute values that contains NULL
         */
        $select->where('NOT(pis.value IS NULL AND pis.value_id IS NOT NULL)');

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('pid.entity_id'),
                'website_field' => new \Zend_Db_Expr('pid.website_id'),
                'store_field' => new \Zend_Db_Expr('pid.store_id'),
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
            ['']
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
     * Prepares data from select to save.
     *
     * @param \Magento\Framework\DB\Select $select
     * @param array $options
     *
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
}
