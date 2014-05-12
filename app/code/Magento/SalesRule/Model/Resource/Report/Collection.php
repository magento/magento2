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
namespace Magento\SalesRule\Model\Resource\Report;

/**
 * Sales report coupons collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\Resource\Report\Collection\AbstractCollection
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
    protected $_aggregationTable = 'coupon_aggregated';

    /**
     * Array of columns that should be aggregated
     *
     * @var array
     */
    protected $_selectedColumns = array();

    /**
     * Array where rules ids stored
     *
     * @var array
     */
    protected $_rulesIdsFilter;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Resource\Report $resource
     * @param \Magento\SalesRule\Model\Resource\Report\RuleFactory $ruleFactory
     * @param mixed $connection
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Resource\Report $resource,
        \Magento\SalesRule\Model\Resource\Report\RuleFactory $ruleFactory,
        $connection = null
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
        $adapter = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $adapter->getDateExtractSql(
                'period',
                \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_YEAR
            );
        } else {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period' => $this->_periodFormat,
                'coupon_code',
                'rule_name',
                'coupon_uses' => 'SUM(coupon_uses)',
                'subtotal_amount' => 'SUM(subtotal_amount)',
                'discount_amount' => 'SUM(discount_amount)',
                'total_amount' => 'SUM(total_amount)',
                'subtotal_amount_actual' => 'SUM(subtotal_amount_actual)',
                'discount_amount_actual' => 'SUM(discount_amount_actual)',
                'total_amount_actual' => 'SUM(total_amount_actual)'
            );
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());
        if ($this->isSubTotals()) {
            $this->getSelect()->group($this->_periodFormat);
        } elseif (!$this->isTotals()) {
            $this->getSelect()->group(
                array(
                    $this->_periodFormat,
                    'coupon_code'
                )
            );
        }

        return parent::_initSelect();
    }

    /**
     * Add filtering by rules ids
     *
     * @param array $rulesList
     * @return $this
     */
    public function addRuleFilter($rulesList)
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

        $rulesList = $this->_ruleFactory->getUniqRulesNamesList();

        $rulesFilterSqlParts = array();
        foreach ($this->_rulesIdsFilter as $ruleId) {
            if (!isset($rulesList[$ruleId])) {
                continue;
            }
            $ruleName = $rulesList[$ruleId];
            $rulesFilterSqlParts[] = $this->getConnection()->quoteInto('rule_name = ?', $ruleName);
        }

        if (!empty($rulesFilterSqlParts)) {
            $this->getSelect()->where(implode($rulesFilterSqlParts, ' OR '));
        }
        return $this;
    }

    /**
     * Apply collection custom filter
     *
     * @return \Magento\Sales\Model\Resource\Report\Collection\AbstractCollection
     */
    protected function _applyCustomFilter()
    {
        $this->_applyRulesFilter();
        return parent::_applyCustomFilter();
    }
}
