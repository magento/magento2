<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Layer\Filter;

/**
 * Catalog Layer Price Filter resource model
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
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->layer = $layerResolver->get();
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        parent::__construct($resource);
    }

    /**
     * Retrieve array with products counts per price range
     *
     * @param int $range
     * @return array
     */
    public function getCount($range)
    {
        $select = $this->_getSelect();
        $priceExpression = $this->_getFullPriceExpression($select);

        /**
         * Check and set correct variable values to prevent SQL-injections
         */
        $range = floatval($range);
        if ($range == 0) {
            $range = 1;
        }
        $countExpr = new \Zend_Db_Expr('COUNT(*)');
        $rangeExpr = new \Zend_Db_Expr("FLOOR(({$priceExpression}) / {$range}) + 1");

        $select->columns(['range' => $rangeExpr, 'count' => $countExpr]);
        $select->group($rangeExpr)->order("({$rangeExpr}) ASC");

        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve clean select with joined price index table
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getSelect()
    {
        $collection = $this->layer->getProductCollection();
        $collection->addPriceData(
            $this->session->getCustomerGroupId(),
            $this->storeManager->getStore()->getWebsiteId()
        );

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
        $select->where($this->_getPriceExpression($select) . ' IS NOT NULL');

        return $select;
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
        $oldAlias = [
            \Magento\Catalog\Model\Resource\Product\Collection::INDEX_TABLE_ALIAS . '.',
            $adapter->quoteIdentifier(\Magento\Catalog\Model\Resource\Product\Collection::INDEX_TABLE_ALIAS) . '.',
        ];
        $newAlias = [
            \Magento\Catalog\Model\Resource\Product\Collection::MAIN_TABLE_ALIAS . '.',
            $adapter->quoteIdentifier(\Magento\Catalog\Model\Resource\Product\Collection::MAIN_TABLE_ALIAS) . '.',
        ];
        return str_replace($oldAlias, $newAlias, $conditionString);
    }

    /**
     * Price expression generated by products collection
     *
     * @param \Magento\Framework\DB\Select $select
     * @param bool $replaceAlias
     * @return string
     */
    protected function _getPriceExpression($select, $replaceAlias = true)
    {
        $priceExpression = $this->layer->getProductCollection()->getPriceExpression($select);
        $additionalPriceExpression = $this->layer->getProductCollection()->getAdditionalPriceExpression(
            $select
        );
        $result = empty($additionalPriceExpression)
            ? $priceExpression
            : "({$priceExpression} {$additionalPriceExpression})";
        if ($replaceAlias) {
            $result = $this->_replaceTableAlias($result);
        }

        return $result;
    }

    /**
     * Get full price expression generated by products collection
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Zend_Db_Expr
     */
    protected function _getFullPriceExpression($select)
    {
        return new \Zend_Db_Expr(
            'ROUND((' . $this->_getPriceExpression($select)
            . ') * ' . $this->layer->getProductCollection()->getCurrencyRate() . ', 2)'
        );
    }

    /**
     * Get comparing value sql part
     *
     * @param float $price
     * @param bool $decrease
     * @return float
     */
    protected function _getComparingValue($price, $decrease = true)
    {
        $currencyRate = $this->layer->getProductCollection()->getCurrencyRate();
        if ($decrease) {
            return ($price - self::MIN_POSSIBLE_PRICE / 2) / $currencyRate;
        }
        return ($price + self::MIN_POSSIBLE_PRICE / 2) / $currencyRate;
    }

    /**
     * Load range of product prices, preceding the price
     *
     * @param float $price
     * @param int $index
     * @param null|int $lowerPrice
     * @return array|false
     */
    public function loadPreviousPrices($price, $index, $lowerPrice = null)
    {
        $select = $this->_getSelect();
        $priceExpression = $this->_getPriceExpression($select);
        $select->columns('COUNT(*)')->where("{$priceExpression} < " . $this->_getComparingValue($price));
        if (!is_null($lowerPrice)) {
            $select->where("{$priceExpression} >= " . $this->_getComparingValue($lowerPrice));
        }
        $offset = $this->_getReadAdapter()->fetchOne($select);
        if (!$offset) {
            return false;
        }

        return $this->loadPrices($index - $offset + 1, $offset - 1, $lowerPrice);
    }

    /**
     * Load range of product prices
     *
     * @param int $limit
     * @param null|int $offset
     * @param null|int $lowerPrice
     * @param null|int $upperPrice
     * @return array
     */
    public function loadPrices($limit, $offset = null, $lowerPrice = null, $upperPrice = null)
    {
        $select = $this->_getSelect();
        $priceExpression = $this->_getPriceExpression($select);
        $select->columns(['min_price_expr' => $this->_getFullPriceExpression($select)]);
        if (!is_null($lowerPrice)) {
            $select->where("{$priceExpression} >= " . $this->_getComparingValue($lowerPrice));
        }
        if (!is_null($upperPrice)) {
            $select->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice));
        }
        $select->order("{$priceExpression} ASC")->limit($limit, $offset);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Load range of product prices, next to the price
     *
     * @param float $price
     * @param int $rightIndex
     * @param null|int $upperPrice
     * @return array|false
     */
    public function loadNextPrices($price, $rightIndex, $upperPrice = null)
    {
        $select = $this->_getSelect();

        $pricesSelect = clone $select;
        $priceExpression = $this->_getPriceExpression($pricesSelect);

        $select->columns(
            'COUNT(*)'
        )->where(
            "{$priceExpression} > " . $this->_getComparingValue($price, false)
        );
        if (!is_null($upperPrice)) {
            $select->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice));
        }
        $offset = $this->_getReadAdapter()->fetchOne($select);
        if (!$offset) {
            return false;
        }

        $pricesSelect->columns(
            ['min_price_expr' => $this->_getFullPriceExpression($pricesSelect)]
        )->where(
            "{$priceExpression} >= " . $this->_getComparingValue($price)
        );
        if (!is_null($upperPrice)) {
            $pricesSelect->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice));
        }
        $pricesSelect->order("{$priceExpression} DESC")->limit($rightIndex - $offset + 1, $offset - 1);

        return array_reverse($this->_getReadAdapter()->fetchCol($pricesSelect));
    }

    /**
     * Apply price range filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @param mixed $interval
     * @return $this
     */
    public function applyPriceRange(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter, $interval)
    {
        if (!$interval) {
            return $this;
        }

        list($from, $to) = $interval;
        if ($from === '' && $to === '') {
            return $this;
        }

        $select = $filter->getLayer()->getProductCollection()->getSelect();
        $priceExpr = $this->_getPriceExpression($select, false);

        if ($to !== '') {
            $to = (double)$to;
            if ($from == $to) {
                $to += self::MIN_POSSIBLE_PRICE;
            }
        }

        if ($from !== '') {
            $select->where($priceExpr . ' >= ' . $this->_getComparingValue($from));
        }
        if ($to !== '') {
            $select->where($priceExpr . ' < ' . $this->_getComparingValue($to));
        }

        return $this;
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
}
