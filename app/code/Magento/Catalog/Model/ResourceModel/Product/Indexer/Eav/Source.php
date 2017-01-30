<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

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

        /**@var $subSelect \Magento\Framework\DB\Select*/
        $subSelect = $connection->select()->from(
            ['s' => $this->getTable('store')],
            ['store_id', 'website_id']
        )->joinLeft(
            ['d' => $this->getTable('catalog_product_entity_int')],
            '1 = 1 AND (d.store_id = 0 OR d.store_id = s.store_id)',
            ['entity_id', 'attribute_id', 'value']
        )->joinLeft(
            ['d2' => $this->getTable('catalog_product_entity_int')],
            sprintf(
                'd.entity_id = d2.entity_id AND d2.attribute_id = %s AND d2.value = %s AND d.store_id = d2.store_id',
                $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status')->getId(),
                ProductStatus::STATUS_ENABLED
            ),
            []
        )->where(
            's.store_id != 0'
        )->where(
            'd.value IS NOT NULL'
        )->where(
            'd2.value IS NOT NULL'
        )->group([
            's.store_id', 's.website_id', 'd.entity_id', 'd.attribute_id', 'd.value',
        ]);

        if ($entityIds !== null) {
            $subSelect->where('d.entity_id IN(?)', $entityIds);
        }

        $ifNullSql = $connection->getIfNullSql('pis.value', 'pid.value');
        /**@var $select \Magento\Framework\DB\Select*/
        $select = $connection->select()->distinct(true)->from(
            ['pid' => new \Zend_Db_Expr(sprintf('(%s)', $subSelect->assemble()))],
            []
        )->joinLeft(
            ['pis' => $this->getTable('catalog_product_entity_int')],
            'pis.entity_id = pid.entity_id AND pis.attribute_id = pid.attribute_id AND pis.store_id = pid.store_id',
            []
        )->columns(
            [
                'pid.entity_id',
                'pid.attribute_id',
                'pid.store_id',
                'value' => $ifNullSql,
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
                'store_field' => new \Zend_Db_Expr('pid.store_id')
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
        if ($attributeId === null) {
            $attrIds = $this->_getIndexableAttributes(true);
        } else {
            $attrIds = [$attributeId];
        }

        if (!$attrIds) {
            return $this;
        }

        // load attribute options
        $options = [];
        $select = $connection->select()->from(
            $this->getTable('eav_attribute_option'),
            ['attribute_id', 'option_id']
        )->where(
            'attribute_id IN(?)',
            $attrIds
        );
        $query = $select->query();
        while ($row = $query->fetch()) {
            $options[$row['attribute_id']][$row['option_id']] = true;
        }

        // prepare get multiselect values query
        $productValueExpression = $connection->getCheckSql('pvs.value_id > 0', 'pvs.value', 'pvd.value');
        $select = $connection->select()->from(
            ['pvd' => $this->getTable('catalog_product_entity_varchar')],
            ['entity_id', 'attribute_id']
        )->join(
            ['cs' => $this->getTable('store')],
            '',
            ['store_id']
        )->joinLeft(
            ['pvs' => $this->getTable('catalog_product_entity_varchar')],
            'pvs.entity_id = pvd.entity_id AND pvs.attribute_id = pvd.attribute_id' . ' AND pvs.store_id=cs.store_id',
            ['value' => $productValueExpression]
        )->where(
            'pvd.store_id=?',
            $connection->getIfNullSql('pvs.store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        )->where(
            'cs.store_id!=?',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        )->where(
            'pvd.attribute_id IN(?)',
            $attrIds
        );

        $statusCond = $connection->quoteInto('=?', ProductStatus::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'pvd.entity_id', 'cs.store_id', $statusCond);

        if ($entityIds !== null) {
            $select->where('pvd.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('pvd.entity_id'),
                'website_field' => new \Zend_Db_Expr('cs.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            ]
        );

        $i = 0;
        $data = [];
        $query = $select->query();
        while ($row = $query->fetch()) {
            $values = explode(',', $row['value']);
            foreach ($values as $valueId) {
                if (isset($options[$row['attribute_id']][$valueId])) {
                    $data[] = [$row['entity_id'], $row['attribute_id'], $row['store_id'], $valueId];
                    $i++;
                    if ($i % 10000 == 0) {
                        $this->_saveIndexData($data);
                        $data = [];
                    }
                }
            }
        }

        $this->_saveIndexData($data);
        unset($options);
        unset($data);

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
        $connection->insertArray($this->getIdxTable(), ['entity_id', 'attribute_id', 'store_id', 'value'], $data);
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
}
