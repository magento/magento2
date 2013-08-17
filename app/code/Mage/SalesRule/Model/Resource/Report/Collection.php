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
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales report coupons collection
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Resource_Report_Collection extends Mage_Sales_Model_Resource_Report_Collection_Abstract
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
     * array of columns that should be aggregated
     *
     * @var array
     */
    protected $_selectedColumns    = array();

    /**
     * array where rules ids stored
     *
     * @var array
     */
    protected $_rulesIdsFilter;

    /**
     * Initialize custom resource model
     *
     */
    public function __construct(
        Varien_Data_Collection_Db_FetchStrategyInterface $fetchStrategy,
        Mage_Sales_Model_Resource_Report $resource
    ) {
        $resource->init($this->_aggregationTable);
        parent::__construct($fetchStrategy, $resource);
    }

    /**
     * collect columns for collection
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $adapter = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat =
                $adapter->getDateExtractSql('period', Varien_Db_Adapter_Interface::INTERVAL_YEAR);
        } else {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period'                  => $this->_periodFormat,
                'coupon_code',
                'rule_name',
                'coupon_uses'             => 'SUM(coupon_uses)',
                'subtotal_amount'         => 'SUM(subtotal_amount)',
                'discount_amount'         => 'SUM(discount_amount)',
                'total_amount'            => 'SUM(total_amount)',
                'subtotal_amount_actual'  => 'SUM(subtotal_amount_actual)',
                'discount_amount_actual'  => 'SUM(discount_amount_actual)',
                'total_amount_actual'     => 'SUM(total_amount_actual)',
            );
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns =
                $this->getAggregatedColumns() +
                    array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return Mage_SalesRule_Model_Resource_Report_Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());
        if ($this->isSubTotals()) {
            $this->getSelect()->group($this->_periodFormat);
        } else if (!$this->isTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat,
                'coupon_code'
            ));
        }

        return parent::_initSelect();
    }

    /**
     * Add filtering by rules ids
     *
     * @param array $rulesList
     * @return Mage_SalesRule_Model_Resource_Report_Collection
     */
    public function addRuleFilter($rulesList)
    {
        $this->_rulesIdsFilter = $rulesList;
        return $this;
    }

    /**
     * Apply filtering by rules ids
     *
     * @return Mage_SalesRule_Model_Resource_Report_Collection
     */
    protected function _applyRulesFilter()
    {
        if (empty($this->_rulesIdsFilter) || !is_array($this->_rulesIdsFilter)) {
            return $this;
        }

        $rulesList = Mage::getResourceModel('Mage_SalesRule_Model_Resource_Report_Rule')->getUniqRulesNamesList();

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
    }

    /**
     * Apply collection custom filter
     *
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    protected function _applyCustomFilter()
    {
        $this->_applyRulesFilter();
        return parent::_applyCustomFilter();
    }
}
