<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Report;

/**
 * Sales report coupons collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
{
    /**
     * Period format for report (day, month, year)
     *
     * @var string
     */
    protected $_periodFormat;

    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'salesrule_coupon_aggregated';

    /**
     * Array of columns that should be aggregated
     *
     * @var array
     */
    protected $_selectedColumns = [];

    /**
     * Array where rules ids stored
     *
     * @var array
     */
    protected $_rulesIdsFilter;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Report\RuleFactory $ruleFactory
     */
    protected $_ruleFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\SalesRule\Model\ResourceModel\Report\RuleFactory $ruleFactory
     * @param \Magento\Sales\Model\ResourceModel\Report $resource
     * @param mixed $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Report $resource,
        \Magento\SalesRule\Model\ResourceModel\Report\RuleFactory $ruleFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->_ruleFactory = $ruleFactory;
        $resource->init($this->_aggregationTable);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);
    }

    /**
     * Collect columns for collection
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $connection->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $connection->getDateExtractSql(
                'period',
                \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_YEAR
            );
        } else {
            $this->_periodFormat = $connection->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = [
                'period' => $this->_periodFormat,
                'coupon_code',
                'rule_name',
                'coupon_uses' => 'SUM(coupon_uses)',
                'subtotal_amount' => 'SUM(subtotal_amount)',
                'discount_amount' => 'SUM(discount_amount)',
                'total_amount' => 'SUM(total_amount)',
                'subtotal_amount_actual' => 'SUM(subtotal_amount_actual)',
                'discount_amount_actual' => 'SUM(discount_amount_actual)',
                'total_amount_actual' => 'SUM(total_amount_actual)',
            ];
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + ['period' => $this->_periodFormat];
        }

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return Collection
     */
    protected function _applyAggregatedTable()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());
        if ($this->isSubTotals()) {
            $this->getSelect()->group($this->_periodFormat);
        } elseif (!$this->isTotals()) {
            $this->getSelect()->group(
                [
                    $this->_periodFormat,
                    'coupon_code',
                ]
            );
        }

        return parent::_applyAggregatedTable();
    }

    /**
     * Add filtering by rules ids
     *
     * @param array $rulesList
     * @return Collection
     */
    public function addRuleFilter(array $rulesList)
    {
        $this->_rulesIdsFilter = $rulesList;
        return $this;
    }

    /**
     * Apply filtering by rules ids
     *
     * @return $this
     */
    protected function _applyRulesFilter()
    {
        if (empty($this->_rulesIdsFilter) || !is_array($this->_rulesIdsFilter)) {
            return $this;
        }

        $rulesList = $this->_ruleFactory->create()->getUniqRulesNamesList();

        $rulesFilterSqlParts = [];
        foreach ($this->_rulesIdsFilter as $ruleId) {
            if (!isset($rulesList[$ruleId])) {
                continue;
            }
            $ruleName = $rulesList[$ruleId];
            $rulesFilterSqlParts[] = $this->getConnection()->quoteInto('rule_name = ?', $ruleName);
        }

        if (!empty($rulesFilterSqlParts)) {
            $this->getSelect()->where(implode(' OR ', $rulesFilterSqlParts));
        }
        return $this;
    }

    /**
     * Apply collection custom filter
     *
     * @return \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
     */
    protected function _applyCustomFilter()
    {
        $this->_applyRulesFilter();
        return parent::_applyCustomFilter();
    }
}
