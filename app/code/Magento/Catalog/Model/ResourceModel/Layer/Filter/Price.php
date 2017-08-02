<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Layer\Filter;

/**
 * Catalog Layer Price Filter resource model
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Price extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Minimal possible price
     */
    const MIN_POSSIBLE_PRICE = .01;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    private $layer;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    private $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->layer = $layerResolver->get();
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Retrieve array with products counts per price range
     *
     * @param int $range
     * @return array
     * @since 2.0.0
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
        $select->group($rangeExpr)->order(new \Zend_Db_Expr("({$rangeExpr}) ASC"));

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * Retrieve clean select with joined price index table
     *
     * @return \Magento\Framework\DB\Select
     * @since 2.0.0
     */
    protected function _getSelect()
    {
        $collection = $this->layer->getProductCollection();
        $collection->addPriceData(
            $this->session->getCustomerGroupId(),
            $this->storeManager->getStore()->getWebsiteId()
        );

        if ($collection->getCatalogPreparedSelect() !== null) {
            $select = clone $collection->getCatalogPreparedSelect();
        } else {
            $select = clone $collection->getSelect();
        }

        // reset columns, order and limitation conditions
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        // remove join with main table
        $fromPart = $select->getPart(\Magento\Framework\DB\Select::FROM);
        if (!isset(
            $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS]
        ) || !isset(
            $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS]
        )
        ) {
            return $select;
        }

        // processing FROM part
        $priceIndexJoinPart = $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS];
        $priceIndexJoinConditions = explode('AND', $priceIndexJoinPart['joinCondition']);
        $priceIndexJoinPart['joinType'] = \Magento\Framework\DB\Select::FROM;
        $priceIndexJoinPart['joinCondition'] = null;
        $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;
        unset($fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS]);
        $select->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
        foreach ($fromPart as $key => $fromJoinItem) {
            $fromPart[$key]['joinCondition'] = $this->_replaceTableAlias($fromJoinItem['joinCondition']);
        }
        $select->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);

        // processing WHERE part
        $wherePart = $select->getPart(\Magento\Framework\DB\Select::WHERE);
        foreach ($wherePart as $key => $wherePartItem) {
            $wherePart[$key] = $this->_replaceTableAlias($wherePartItem);
        }
        $select->setPart(\Magento\Framework\DB\Select::WHERE, $wherePart);
        $excludeJoinPart = \Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS . '.entity_id';
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
     * @since 2.0.0
     */
    protected function _replaceTableAlias($conditionString)
    {
        if ($conditionString === null) {
            return null;
        }
        $connection = $this->getConnection();
        $oldAlias = [
            \Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS . '.',
            $connection->quoteIdentifier(
                \Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS
            ) . '.',
        ];
        $newAlias = [
            \Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS . '.',
            $connection->quoteIdentifier(
                \Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS
            ) . '.',
        ];
        return str_replace($oldAlias, $newAlias, $conditionString);
    }

    /**
     * Price expression generated by products collection
     *
     * @param \Magento\Framework\DB\Select $select
     * @param bool $replaceAlias
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function loadPreviousPrices($price, $index, $lowerPrice = null)
    {
        $select = $this->_getSelect();
        $priceExpression = $this->_getPriceExpression($select);
        $select->columns('COUNT(*)')->where("{$priceExpression} < " . $this->_getComparingValue($price));
        if ($lowerPrice !== null) {
            $select->where("{$priceExpression} >= " . $this->_getComparingValue($lowerPrice));
        }
        $offset = $this->getConnection()->fetchOne($select);
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
     * @since 2.0.0
     */
    public function loadPrices($limit, $offset = null, $lowerPrice = null, $upperPrice = null)
    {
        $select = $this->_getSelect();
        $priceExpression = $this->_getPriceExpression($select);
        $select->columns(['min_price_expr' => $this->_getFullPriceExpression($select)]);
        if ($lowerPrice !== null) {
            $select->where("{$priceExpression} >= " . $this->_getComparingValue($lowerPrice));
        }
        if ($upperPrice !== null) {
            $select->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice));
        }
        $select->order("{$priceExpression} ASC")->limit($limit, $offset);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Load range of product prices, next to the price
     *
     * @param float $price
     * @param int $rightIndex
     * @param null|int $upperPrice
     * @return array|false
     * @since 2.0.0
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
        if ($upperPrice !== null) {
            $select->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice));
        }
        $offset = $this->getConnection()->fetchOne($select);
        if (!$offset) {
            return false;
        }

        $pricesSelect->columns(
            ['min_price_expr' => $this->_getFullPriceExpression($pricesSelect)]
        )->where(
            "{$priceExpression} >= " . $this->_getComparingValue($price)
        );
        if ($upperPrice !== null) {
            $pricesSelect->where("{$priceExpression} < " . $this->_getComparingValue($upperPrice));
        }
        $pricesSelect->order("{$priceExpression} DESC")->limit($rightIndex - $offset + 1, $offset - 1);

        return array_reverse($this->getConnection()->fetchCol($pricesSelect));
    }

    /**
     * Apply price range filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @param mixed $interval
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_price', 'entity_id');
    }

    /**
     * Retrieve joined price index table alias
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getIndexTableAlias()
    {
        return 'price_index';
    }
}
