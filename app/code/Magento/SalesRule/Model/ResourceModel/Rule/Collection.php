<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\ResourceModel\Rule;

use Magento\Quote\Model\Quote\Address;

/**
 * Sales Rules resource collection model.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\DateApplier
     */
    protected $dateApplier;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_date = $date;
        $this->_associatedEntitiesMap = $this->getAssociatedEntitiesMap();
    }

    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\SalesRule\Model\Rule::class, \Magento\SalesRule\Model\ResourceModel\Rule::class);
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }

    /**
     * @param string $entityType
     * @param string $objectField
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function mapAssociatedEntities($entityType, $objectField)
    {
        if (!$this->_items) {
            return;
        }

        $entityInfo = $this->_getAssociatedEntityInfo($entityType);
        $ruleIdField = $entityInfo['rule_id_field'];
        $entityIds = $this->getColumnValues($ruleIdField);

        $select = $this->getConnection()->select()->from(
            $this->getTable($entityInfo['associations_table'])
        )->where(
            $ruleIdField . ' IN (?)',
            $entityIds
        );

        $associatedEntities = $this->getConnection()->fetchAll($select);

        array_map(function ($associatedEntity) use ($entityInfo, $ruleIdField, $objectField) {
            $item = $this->getItemByColumnValue($ruleIdField, $associatedEntity[$ruleIdField]);
            $itemAssociatedValue = $item->getData($objectField) === null ? [] : $item->getData($objectField);
            $itemAssociatedValue[] = $associatedEntity[$entityInfo['entity_id_field']];
            $item->setData($objectField, $itemAssociatedValue);
        }, $associatedEntities);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _afterLoad()
    {
        $this->mapAssociatedEntities('website', 'website_ids');
        $this->mapAssociatedEntities('customer_group', 'customer_group_ids');

        $this->setFlag('add_websites_to_result', false);
        return parent::_afterLoad();
    }

    /**
     * Filter collection by specified website, customer group, coupon code, date.
     * Filter collection to use only active rules.
     * Involved sorting by sort_order column.
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $couponCode
     * @param string|null $now
     * @param Address $address allow extensions to further filter out rules based on quote address
     * @use $this->addWebsiteGroupDateFilter()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this
     */
    public function setValidationFilter(
        $websiteId,
        $customerGroupId,
        $couponCode = '',
        $now = null,
        Address $address = null
    ) {
        if (!$this->getFlag('validation_filter')) {
            /* We need to overwrite joinLeft if coupon is applied */
            $this->getSelect()->reset();
            parent::_initSelect();

            $this->addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now);
            $select = $this->getSelect();

            $connection = $this->getConnection();
            if (strlen($couponCode)) {
                $select->joinLeft(
                    ['rule_coupons' => $this->getTable('salesrule_coupon')],
                    $connection->quoteInto(
                        'main_table.rule_id = rule_coupons.rule_id AND main_table.coupon_type != ?',
                        \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON
                    ),
                    ['code']
                );

                $noCouponWhereCondition = $connection->quoteInto(
                    'main_table.coupon_type = ? ',
                    \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON
                );

                $autoGeneratedCouponCondition = [
                    $connection->quoteInto(
                        "main_table.coupon_type = ?",
                        \Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO
                    ),
                    $connection->quoteInto(
                        "rule_coupons.type = ?",
                        \Magento\SalesRule\Api\Data\CouponInterface::TYPE_GENERATED
                    ),
                ];

                $orWhereConditions = [
                    "(" . implode($autoGeneratedCouponCondition, " AND ") . ")",
                    $connection->quoteInto(
                        '(main_table.coupon_type = ? AND main_table.use_auto_generation = 1 AND rule_coupons.type = 1)',
                        \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC
                    ),
                    $connection->quoteInto(
                        '(main_table.coupon_type = ? AND main_table.use_auto_generation = 0 AND rule_coupons.type = 0)',
                        \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC
                    ),
                ];

                $andWhereConditions = [
                    $connection->quoteInto(
                        'rule_coupons.code = ?',
                        $couponCode
                    ),
                    $connection->quoteInto(
                        '(rule_coupons.expiration_date IS NULL OR rule_coupons.expiration_date >= ?)',
                        $this->_date->date()->format('Y-m-d')
                    ),
                ];

                $orWhereCondition = implode(' OR ', $orWhereConditions);
                $andWhereCondition = implode(' AND ', $andWhereConditions);

                $select->where(
                    $noCouponWhereCondition . ' OR ((' . $orWhereCondition . ') AND ' . $andWhereCondition . ')'
                );
            } else {
                $this->addFieldToFilter(
                    'main_table.coupon_type',
                    \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON
                );
            }
            $this->setOrder('sort_order', self::SORT_ORDER_ASC);
            $this->setFlag('validation_filter', true);
        }

        return $this;
    }

    /**
     * Filter collection by website(s), customer group(s) and date.
     * Filter collection to only active rules.
     * Sorting is not involved
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string|null $now
     * @use $this->addWebsiteFilter()
     * @return $this
     */
    public function addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now = null)
    {
        if (!$this->getFlag('website_group_date_filter')) {
            if ($now === null) {
                $now = $this->_date->date()->format('Y-m-d');
            }

            $this->addWebsiteFilter($websiteId);

            $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
            $connection = $this->getConnection();
            $this->getSelect()->joinInner(
                ['customer_group_ids' => $this->getTable($entityInfo['associations_table'])],
                $connection->quoteInto(
                    'main_table.' .
                    $entityInfo['rule_id_field'] .
                    ' = customer_group_ids.' .
                    $entityInfo['rule_id_field'] .
                    ' AND customer_group_ids.' .
                    $entityInfo['entity_id_field'] .
                    ' = ?',
                    (int)$customerGroupId
                ),
                []
            );

            $this->getDateApplier()->applyDate($this->getSelect(), $now);

            $this->addIsActiveFilter();

            $this->setFlag('website_group_date_filter', true);
        }

        return $this;
    }

    /**
     * Add primary coupon to collection
     *
     * @return $this
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['rule_coupons' => $this->getTable('salesrule_coupon')],
            'main_table.rule_id = rule_coupons.rule_id AND rule_coupons.is_primary = 1',
            ['code']
        );
        return $this;
    }

    /**
     * Find product attribute in conditions or actions
     *
     * @param string $attributeCode
     * @return $this
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr(serialize(['attribute' => $attributeCode]), 5, -1));
        $field = $this->_getMappedField('conditions_serialized');
        $cCond = $this->_getConditionSql($field, ['like' => $match]);
        $field = $this->_getMappedField('actions_serialized');
        $aCond = $this->_getConditionSql($field, ['like' => $match]);

        $this->getSelect()->where(
            sprintf('(%s OR %s)', $cCond, $aCond),
            null,
            \Magento\Framework\DB\Select::TYPE_CONDITION
        );

        return $this;
    }

    /**
     * Excludes price rules with generated specific coupon codes from collection
     *
     * @return $this
     */
    public function addAllowedSalesRulesFilter()
    {
        $this->addFieldToFilter('main_table.use_auto_generation', ['neq' => 1]);

        return $this;
    }

    /**
     * Limit rules collection by specific customer group
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
        if (!$this->getFlag('is_customer_group_joined')) {
            $this->setFlag('is_customer_group_joined', true);
            $this->getSelect()->join(
                ['customer_group' => $this->getTable($entityInfo['associations_table'])],
                $this->getConnection()
                    ->quoteInto('customer_group.' . $entityInfo['entity_id_field'] . ' = ?', $customerGroupId)
                . ' AND main_table.' . $entityInfo['rule_id_field'] . ' = customer_group.'
                . $entityInfo['rule_id_field'],
                []
            );
        }
        return $this;
    }

    /**
     * @return array
     * @deprecated
     */
    private function getAssociatedEntitiesMap()
    {
        if (!$this->_associatedEntitiesMap) {
            $this->_associatedEntitiesMap = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\SalesRule\Model\ResourceModel\Rule\AssociatedEntityMap::class)
                ->getData();
        }
        return $this->_associatedEntitiesMap;
    }

    /**
     * @return DateApplier
     * @deprecated
     */
    private function getDateApplier()
    {
        if (null === $this->dateApplier) {
            $this->dateApplier = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\SalesRule\Model\ResourceModel\Rule\DateApplier::class);
        }

        return $this->dateApplier;
    }
}
