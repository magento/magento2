<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel\Entity\Attribute;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * EAV attribute resource collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends AbstractCollection
{
    /**
     * Add attribute set info flag
     *
     * @var bool
     */
    protected $_addSetInfoFlag = false;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     * @codeCoverageIgnore
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Resource model initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Eav\Model\Entity\Attribute::class,
            Attribute::class
        );
    }

    /**
     * Return array of fields to load attribute values
     *
     * @return string[]
     * @codeCoverageIgnore
     */
    protected function _getLoadDataFields()
    {
        return [
            'attribute_id',
            'entity_type_id',
            'attribute_code',
            'attribute_model',
            'backend_model',
            'backend_type',
            'backend_table',
            'frontend_input',
            'source_model'
        ];
    }

    /**
     * Specify select columns which are used for load attribute values
     *
     * @return $this
     */
    public function useLoadDataFields()
    {
        $this->getSelect()->reset(Select::COLUMNS);
        $this->getSelect()->columns($this->_getLoadDataFields());

        return $this;
    }

    /**
     * Specify attribute entity type filter
     *
     * @param  Type|int $type
     * @return $this
     */
    public function setEntityTypeFilter($type)
    {
        if ($type instanceof Type) {
            $additionalTable = $type->getAdditionalAttributeTable();
            $id = $type->getId();
        } else {
            $additionalTable = $this->getResource()->getAdditionalAttributeTable($type);
            $id = $type;
        }
        $this->addFieldToFilter('main_table.entity_type_id', $id);
        if ($additionalTable) {
            $this->join(
                ['additional_table' => $additionalTable],
                'additional_table.attribute_id = main_table.attribute_id'
            );
        }

        return $this;
    }

    /**
     * Specify attribute set filter
     *
     * @param int|int[] $setId
     * @return $this
     */
    public function setAttributeSetFilter($setId)
    {
        if (is_array($setId)) {
            if (!empty($setId)) {
                $this->join(
                    ['entity_attribute' => $this->getTable('eav_entity_attribute')],
                    'entity_attribute.attribute_id = main_table.attribute_id',
                    'attribute_id'
                );
                $this->addFieldToFilter('entity_attribute.attribute_set_id', ['in' => $setId]);
                $this->addAttributeGrouping();
            }
        } elseif ($setId) {
            $this->join(
                ['entity_attribute' => $this->getTable('eav_entity_attribute')],
                'entity_attribute.attribute_id = main_table.attribute_id'
            );
            $this->addFieldToFilter('entity_attribute.attribute_set_id', $setId);
            $this->setOrder('entity_attribute.sort_order', self::SORT_ORDER_ASC);
        }

        return $this;
    }

    /**
     * Add attribute set filter to collection based on attribute set name and corresponding entity type.
     *
     * @param string $attributeSetName
     * @param string $entityTypeCode
     * @return void
     */
    public function setAttributeSetFilterBySetName($attributeSetName, $entityTypeCode)
    {
        //@codeCoverageIgnoreStart
        $entityTypeId = $this->eavConfig->getEntityType($entityTypeCode)->getId();
        $this->join(
            ['entity_attribute' => $this->getTable('eav_entity_attribute')],
            'entity_attribute.attribute_id = main_table.attribute_id'
        );
        $this->join(
            ['attribute_set' => $this->getTable('eav_attribute_set')],
            'attribute_set.attribute_set_id = entity_attribute.attribute_set_id',
            []
        );
        $this->addFieldToFilter('attribute_set.entity_type_id', $entityTypeId);
        $this->addFieldToFilter('attribute_set.attribute_set_name', $attributeSetName);
        $this->setOrder('entity_attribute.sort_order', self::SORT_ORDER_ASC);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Specify multiple attribute sets filter
     *
     * Result will be ordered by sort_order
     *
     * @param array $setIds
     * @return $this
     */
    public function setAttributeSetsFilter(array $setIds)
    {
        $this->getSelect()->distinct(true);
        $this->join(
            ['entity_attribute' => $this->getTable('eav_entity_attribute')],
            'entity_attribute.attribute_id = main_table.attribute_id',
            'attribute_id'
        );
        $this->addFieldToFilter('entity_attribute.attribute_set_id', ['in' => $setIds]);
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * Filter for selecting of attributes that is in all sets
     *
     * @param int[] $setIds
     * @return $this
     */
    public function setInAllAttributeSetsFilter(array $setIds)
    {
        if (!empty($setIds)) {
            $this->getSelect()
                ->join(
                    ['entity_attribute' => $this->getTable('eav_entity_attribute')],
                    'entity_attribute.attribute_id = main_table.attribute_id',
                    ['count' => new \Zend_Db_Expr('COUNT(*)')]
                )
                ->where(
                    'entity_attribute.attribute_set_id IN (?)',
                    $setIds,
                    \Zend_Db::INT_TYPE
                )
                ->group('entity_attribute.attribute_id')
                ->having(new \Zend_Db_Expr('COUNT(*)') . ' = ' . count($setIds));
        }

        $this->setOrder('is_user_defined', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * Exclude attributes filter
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributesExcludeFilter($attributes)
    {
        return $this->addFieldToFilter('main_table.attribute_id', ['nin' => $attributes]);
    }

    /**
     * Specify exclude attribute set filter
     *
     * @param int $setId
     * @return $this
     */
    public function setExcludeSetFilter($setId)
    {
        $existsSelect = $this->getConnection()->select()->from(
            ['entity_attribute' => $this->getTable('eav_entity_attribute')]
        )->where(
            'entity_attribute.attribute_set_id = ?',
            $setId
        );
        $this->getSelect()->order('attribute_id ' . self::SORT_ORDER_DESC);

        $this->getSelect()->exists($existsSelect, 'entity_attribute.attribute_id = main_table.attribute_id', false);
        return $this;
    }

    /**
     * Filter by attribute group id
     *
     * @param int $groupId
     * @return $this
     */
    public function setAttributeGroupFilter($groupId)
    {
        $this->join(
            ['entity_attribute' => $this->getTable('eav_entity_attribute')],
            'entity_attribute.attribute_id = main_table.attribute_id'
        );
        $this->addFieldToFilter('entity_attribute.attribute_group_id', $groupId);
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * Declare group by attribute id condition for collection select
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function addAttributeGrouping()
    {
        $this->getSelect()->group('main_table.attribute_id');
        return $this;
    }

    /**
     * Specify "is_unique" filter as true
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function addIsUniqueFilter()
    {
        return $this->addFieldToFilter('is_unique', ['gt' => 0]);
    }

    /**
     * Specify "is_unique" filter as false
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function addIsNotUniqueFilter()
    {
        return $this->addFieldToFilter('is_unique', 0);
    }

    /**
     * Specify filter to select just attributes with options
     *
     * @return $this
     */
    public function addHasOptionsFilter()
    {
        $connection = $this->getConnection();
        $orWhere = implode(
            ' OR ',
            [
                $connection->quoteInto('(main_table.frontend_input = ? AND ao.option_id > 0)', 'select'),
                $connection->quoteInto('(main_table.frontend_input <> ?)', 'select'),
                '(main_table.is_user_defined = 0)'
            ]
        );

        $this->getSelect()->joinLeft(
            ['ao' => $this->getTable('eav_attribute_option')],
            'ao.attribute_id = main_table.attribute_id',
            'option_id'
        )->group(
            'main_table.attribute_id'
        )->where(
            $orWhere
        );
        return $this;
    }

    /**
     * Apply filter by attribute frontend input type
     *
     * @param string $frontendInputType
     * @return $this
     * @codeCoverageIgnore
     */
    public function setFrontendInputTypeFilter($frontendInputType)
    {
        return $this->addFieldToFilter('frontend_input', $frontendInputType);
    }

    /**
     * Flag for adding information about attributes sets to result
     *
     * @param bool $flag
     * @return $this
     * @codeCoverageIgnore
     */
    public function addSetInfo($flag = true)
    {
        $this->_addSetInfoFlag = (bool)$flag;
        return $this;
    }

    /**
     * Ad information about attribute sets to collection result data
     *
     * @return $this
     */
    protected function _addSetInfo()
    {
        if ($this->_addSetInfoFlag) {
            $attributeIds = [];
            foreach ($this->_data as &$dataItem) {
                $attributeIds[] = $dataItem['attribute_id'];
            }
            $attributeToSetInfo = [];

            $connection = $this->getConnection();
            if (count($attributeIds) > 0) {
                $select = $connection->select()->from(
                    ['entity' => $this->getTable('eav_entity_attribute')],
                    ['attribute_id', 'attribute_set_id', 'attribute_group_id', 'sort_order']
                )->joinLeft(
                    ['attribute_group' => $this->getTable('eav_attribute_group')],
                    'entity.attribute_group_id = attribute_group.attribute_group_id',
                    ['group_sort_order' => 'sort_order']
                )->where(
                    'attribute_id IN (?)',
                    $attributeIds,
                    \Zend_Db::INT_TYPE
                );
                $result = $connection->fetchAll($select);

                foreach ($result as $row) {
                    $data = [
                        'group_id' => $row['attribute_group_id'],
                        'group_sort' => $row['group_sort_order'],
                        'sort' => $row['sort_order'],
                    ];
                    $attributeToSetInfo[$row['attribute_id']][$row['attribute_set_id']] = $data;
                }
            }

            foreach ($this->_data as &$attributeData) {
                $setInfo = [];
                if (isset($attributeToSetInfo[$attributeData['attribute_id']])) {
                    $setInfo = $attributeToSetInfo[$attributeData['attribute_id']];
                }

                $attributeData['attribute_set_info'] = $setInfo;
            }

            unset($attributeToSetInfo);
            unset($attributeIds);
        }
        return $this;
    }

    /**
     * Ad information about attribute sets to collection result data
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function _afterLoadData()
    {
        $this->_addSetInfo();

        return parent::_afterLoadData();
    }

    /**
     * Specify collection attribute codes filter
     *
     * @param string|array $code
     * @return $this
     */
    public function setCodeFilter($code)
    {
        if (empty($code)) {
            return $this;
        }
        if (!is_array($code)) {
            $code = [$code];
        }

        return $this->addFieldToFilter('attribute_code', ['in' => $code]);
    }

    /**
     * Add store label to attribute by specified store id
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreLabel($storeId)
    {
        $connection = $this->getConnection();
        $joinExpression = $connection->quoteInto(
            'al.attribute_id = main_table.attribute_id AND al.store_id = ?',
            (int)$storeId
        );
        $this->getSelect()->joinLeft(
            ['al' => $this->getTable('eav_attribute_label')],
            $joinExpression,
            ['store_label' => $connection->getIfNullSql('al.value', 'main_table.frontend_label')]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Select::COLUMNS);
        $countSelect->columns('COUNT(DISTINCT main_table.attribute_id)');
        return $countSelect;
    }

    /**
     * Join table to collection select
     *
     * @param string $table
     * @param string $cond
     * @param string $cols
     * @return $this
     * @since 100.1.0
     */
    public function joinLeft($table, $cond, $cols = '*')
    {
        if (is_array($table)) {
            foreach ($table as $k => $v) {
                $alias = $k;
                $table = $v;
                break;
            }
        } else {
            $alias = $table;
        }

        if (!isset($this->_joinedTables[$alias])) {
            $this->getSelect()->joinLeft([$alias => $this->getTable($table)], $cond, $cols);
            $this->_joinedTables[$alias] = true;
        }
        return $this;
    }
}
