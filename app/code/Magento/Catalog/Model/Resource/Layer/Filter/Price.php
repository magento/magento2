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
namespace Magento\Catalog\Model\Resource\Layer\Filter;

/**
 * Catalog Layer Price Filter resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Price extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Minimal possible price
     */
    const MIN_POSSIBLE_PRICE = .01;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->_eventManager = $eventManager;
        parent::__construct($resource);
    }

    /**
     * Initialize connection and define main table name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_price', 'entity_id');
    }

    /**
     * Retrieve joined price index table alias
     *
     * @return string
     */
    protected function _getIndexTableAlias()
    {
        return 'price_index';
    }

    /**
     * Replace table alias in condition string
     *
     * @param string|null $conditionString
     * @return string|null
     */
    protected function _replaceTableAlias($conditionString)
    {
        if (is_null($conditionString)) {
            return null;
        }
        $adapter = $this->_getReadAdapter();
        $oldAlias = array(
            \Magento\Catalog\Model\Resource\Product\Collection::INDEX_TABLE_ALIAS . '.',
            $adapter->quoteIdentifier(\Magento\Catalog\Model\Resource\Product\Collection::INDEX_TABLE_ALIAS) . '.'
        );
        $newAlias = array(
            \Magento\Catalog\Model\Resource\Product\Collection::MAIN_TABLE_ALIAS . '.',
            $adapter->quoteIdentifier(\Magento\Catalog\Model\Resource\Product\Collection::MAIN_TABLE_ALIAS) . '.'
        );
        return str_replace($oldAlias, $newAlias, $conditionString);
    }

    /**
     * Retrieve clean select with joined price index table
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @return \Magento\Framework\DB\Select
     */
    protected function _getSelect($filter)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

        if (!is_null($collection->getCatalogPreparedSelect())) {
            $select = clone $collection->getCatalogPreparedSelect();
        } else {
            $select = clone $collection->getSelect();
        }

        // reset columns, order and limitation conditions
        $select->reset(\Zend_Db_Select::COLUMNS);
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);

        // remove join with main table
        $fromPart = $select->getPart(\Zend_Db_Select::FROM);
        if (!isset(
            $fromPart[\Magento\Catalog\Model\Resource\Product\Collection::INDEX_TABLE_ALIAS]
        ) || !isset(
            $fromPart[\Magento\Catalog\Model\Resource\Product\Collection::MAIN_TABLE_ALIAS]
        )
        ) {
            return $select;
        }

        // processing FROM part
        $priceIndexJoinPart = $fromPart[\Magento\Catalog\Model\Resource\Product\Collection::INDEX_TABLE_ALIAS];
        $priceIndexJoinConditions = explode('AND', $priceIndexJoinPart['joinCondition']);
        $priceIndexJoinPart['joinType'] = \Zend_Db_Select::FROM;
        $priceIndexJoinPart['joinCondition'] = null;
        $fromPart[\Magento\Catalog\Model\Resource\Product\Collection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;
        unset($fromPart[\Magento\Catalog\Model\Resource\Product\Collection::INDEX_TABLE_ALIAS]);
        $select->setPart(\Zend_Db_Select::FROM, $fromPart);
        foreach ($fromPart as $key => $fromJoinItem) {
            $fromPart[$key]['joinCondition'] = $this->_replaceTableAlias($fromJoinItem['joinCondition']);
        }
        $select->setPart(\Zend_Db_Select::FROM, $fromPart);

        // processing WHERE part
        $wherePart = $select->getPart(\Zend_Db_Select::WHERE);
        foreach ($wherePart as $key => $wherePartItem) {
            $wherePart[$key] = $this->_replaceTableAlias($wherePartItem);
        }
        $select->setPart(\Zend_Db_Select::WHERE, $wherePart);
        $excludeJoinPart = \Magento\Catalog\Model\Resource\Product\Collection::MAIN_TABLE_ALIAS . '.entity_id';
        foreach ($priceIndexJoinConditions as $condition) {
            if (strpos($condition, $excludeJoinPart) !== false) {
                continue;
            }
            $select->where($this->_replaceTableAlias($condition));
        }
        $select->where($this->_getPriceExpression($filter, $select) . ' IS NOT NULL');

        return $select;
    }

    /**
     * Prepare response object and dispatch prepare price event
     * Return response object
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\Object
     *
     * @deprecated since 1.7.0.0
     */
    protected function _dispatchPreparePriceEvent($filter, $select)
    {
        // prepare response object for event
        $response = new \Magento\Framework\Object();
        $response->setAdditionalCalculations(array());

        return $response;
    }

    /**
     * Retrieve maximal price for attribute
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @return float
     *
     * @deprecated since 1.7.0.0
     */
    public function getMaxPrice($filter)
    {
        return $filter->getLayer()->getProductCollection()->getMaxPrice();
    }

    /**
     * Price expression generated by products collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param \Magento\Framework\DB\Select $select
     * @param bool $replaceAlias
     * @return string
     */
    protected function _getPriceExpression($filter, $select, $replaceAlias = true)
    {
        $priceExpression = $filter->getLayer()->getProductCollection()->getPriceExpression($select);
        $additionalPriceExpression = $filter->getLayer()->getProductCollection()->getAdditionalPriceExpression(
            $select
        );
        $result = empty($additionalPriceExpression) ? $priceExpression : "({$priceExpression} {$additionalPriceExpression})";
        if ($replaceAlias) {
            $result = $this->_replaceTableAlias($result);
        }

        return $result;
    }

    /**
     * Get comparing value sql part
     *
     * @param float $price
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param bool $decrease
     * @return float
     */
    protected function _getComparingValue($price, $filter, $decrease = true)
    {
        $currencyRate = $filter->getLayer()->getProductCollection()->getCurrencyRate();
        if ($decrease) {
            return ($price - self::MIN_POSSIBLE_PRICE / 2) / $currencyRate;
        }
        return ($price + self::MIN_POSSIBLE_PRICE / 2) / $currencyRate;
    }

    /**
     * Get full price expression generated by products collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param \Magento\Framework\DB\Select $select
     * @return \Zend_Db_Expr
     */
    protected function _getFullPriceExpression($filter, $select)
    {
        return new \Zend_Db_Expr(
            'ROUND((' . $this->_getPriceExpression(
                $filter,
                $select
            ) . ') * ' . $filter->getLayer()->getProductCollection()->getCurrencyRate() . ', 2)'
        );
    }

    /**
     * Retrieve array with products counts per price range
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param int $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        $select = $this->_getSelect($filter);
        $priceExpression = $this->_getFullPriceExpression($filter, $select);

        /**
         * Check and set correct variable values to prevent SQL-injections
         */
        $range = floatval($range);
        if ($range == 0) {
            $range = 1;
        }
        $countExpr = new \Zend_Db_Expr('COUNT(*)');
        $rangeExpr = new \Zend_Db_Expr("FLOOR(({$priceExpression}) / {$range}) + 1");

        $select->columns(array('range' => $rangeExpr, 'count' => $countExpr));
        $select->group($rangeExpr)->order("{$rangeExpr} ASC");

        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Apply attribute filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param int $range
     * @param int $index the range factor
     * @return $this
     *
     * @deprecated since 1.7.0.0
     */
    public function applyFilterToCollection($filter, $range, $index)
    {
        $select = $filter->getLayer()->getProductCollection()->getSelect();
        $priceExpr = $this->_getPriceExpression($filter, $select);
        $filter->getLayer()->getProductCollection()->getSelect()->where(
            $priceExpr . ' >= ' . $this->_getComparingValue($range * ($index - 1), $filter)
        )->where(
            $priceExpr . ' < ' . $this->_getComparingValue($range * $index, $filter)
        );

        return $this;
    }

    /**
     * Load range of product prices
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param int $limit
     * @param null|int $offset
     * @param null|int $lowerPrice
     * @param null|int $upperPrice
     * @return array
     */
    public function loadPrices($filter, $limit, $offset = null, $lowerPrice = null, $upperPrice = null)
    {
        $select = $this->_getSelect($filter);
        $priceExpression = $this->_getPriceExpression($filter, $select);
        $select->columns(array('min_price_expr' => $this->_getFullPriceExpression($filter, $select)));
        if (!is_null($lowerPrice)) {
            $select->where("{$priceExpression} >= " . $this->_getComparingValue($lowerPrice, $filter));
        }
        if (!is_null($upperPrice)) {
            $select->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice, $filter));
        }
        $select->order("{$priceExpression} ASC")->limit($limit, $offset);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Load range of product prices, preceding the price
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param float $price
     * @param int $index
     * @param null|int $lowerPrice
     * @return array|false
     */
    public function loadPreviousPrices($filter, $price, $index, $lowerPrice = null)
    {
        $select = $this->_getSelect($filter);
        $priceExpression = $this->_getPriceExpression($filter, $select);
        $select->columns('COUNT(*)')->where("{$priceExpression} < " . $this->_getComparingValue($price, $filter));
        if (!is_null($lowerPrice)) {
            $select->where("{$priceExpression} >= " . $this->_getComparingValue($lowerPrice, $filter));
        }
        $offset = $this->_getReadAdapter()->fetchOne($select);
        if (!$offset) {
            return false;
        }

        return $this->loadPrices($filter, $index - $offset + 1, $offset - 1, $lowerPrice);
    }

    /**
     * Load range of product prices, next to the price
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param float $price
     * @param int $rightIndex
     * @param null|int $upperPrice
     * @return array|false
     */
    public function loadNextPrices($filter, $price, $rightIndex, $upperPrice = null)
    {
        $select = $this->_getSelect($filter);

        $pricesSelect = clone $select;
        $priceExpression = $this->_getPriceExpression($filter, $pricesSelect);

        $select->columns(
            'COUNT(*)'
        )->where(
            "{$priceExpression} > " . $this->_getComparingValue($price, $filter, false)
        );
        if (!is_null($upperPrice)) {
            $select->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice, $filter));
        }
        $offset = $this->_getReadAdapter()->fetchOne($select);
        if (!$offset) {
            return false;
        }

        $pricesSelect->columns(
            array('min_price_expr' => $this->_getFullPriceExpression($filter, $pricesSelect))
        )->where(
            "{$priceExpression} >= " . $this->_getComparingValue($price, $filter)
        );
        if (!is_null($upperPrice)) {
            $pricesSelect->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice, $filter));
        }
        $pricesSelect->order("{$priceExpression} DESC")->limit($rightIndex - $offset + 1, $offset - 1);

        return array_reverse($this->_getReadAdapter()->fetchCol($pricesSelect));
    }

    /**
     * Apply price range filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @return $this
     */
    public function applyPriceRange($filter)
    {
        $interval = $filter->getInterval();
        if (!$interval) {
            return $this;
        }

        list($from, $to) = $interval;
        if ($from === '' && $to === '') {
            return $this;
        }

        $select = $filter->getLayer()->getProductCollection()->getSelect();
        $priceExpr = $this->_getPriceExpression($filter, $select, false);

        if ($to !== '') {
            $to = (double)$to;
            if ($from == $to) {
                $to += self::MIN_POSSIBLE_PRICE;
            }
        }

        if ($from !== '') {
            $select->where($priceExpr . ' >= ' . $this->_getComparingValue($from, $filter));
        }
        if ($to !== '') {
            $select->where($priceExpr . ' < ' . $this->_getComparingValue($to, $filter));
        }

        return $this;
    }
}
