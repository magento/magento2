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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Layer Price Filter resource model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Layer_Filter_Price extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table name
     *
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
     * Retrieve clean select with joined price index table
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return Varien_Db_Select
     */
    protected function _getSelect($filter)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

        // clone select from collection with filters
        $select = clone $collection->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        return $select;
    }

    /**
     * Prepare response object and dispatch prepare price event
     * Return response object
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param Varien_Db_Select $select
     * @return Varien_Object
     */
    protected function _dispatchPreparePriceEvent($filter, $select)
    {
        // prepare response object for event
        $response = new Varien_Object();
        $response->setAdditionalCalculations(array());

        // prepare event arguments
        $eventArgs = array(
            'select'          => $select,
            'table'           => $this->_getIndexTableAlias(),
            'store_id'        => $filter->getStoreId(),
            'response_object' => $response
        );

        /**
         * @since 1.4
         */
        Mage::dispatchEvent('catalog_prepare_price_select', $eventArgs);

        return $response;
    }

    /**
     * Retrieve maximal price for attribute
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return float
     */
    public function getMaxPrice($filter)
    {
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table = $this->_getIndexTableAlias();

        $additional   = join('', $response->getAdditionalCalculations());
        $maxPriceExpr = new Zend_Db_Expr("MAX({$table}.min_price {$additional})");

        $select->columns(array($maxPriceExpr));

        return $connection->fetchOne($select) * $filter->getCurrencyRate();
    }

    /**
     * Retrieve array with products counts per price range
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param int $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);
        $table      = $this->_getIndexTableAlias();

        $additional = join('', $response->getAdditionalCalculations());
        $rate       = $filter->getCurrencyRate();

        /**
         * Check and set correct variable values to prevent SQL-injections
         */
        $rate       = floatval($rate);
        $range      = floatval($range);
        if ($range == 0) {
            $range = 1;
        }
        $countExpr  = new Zend_Db_Expr('COUNT(*)');
        $rangeExpr  = new Zend_Db_Expr("FLOOR((({$table}.min_price {$additional}) * {$rate}) / {$range}) + 1");

        $select->columns(array(
            'range' => $rangeExpr,
            'count' => $countExpr
        ));
        $select->group($rangeExpr)->order("$rangeExpr ASC");

        return $connection->fetchPairs($select);
    }

    /**
     * Prepare filter apply
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return array
     */
    protected function _prepareApply($filter)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

        $select     = $collection->getSelect();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table      = $this->_getIndexTableAlias();
        $additional = join('', $response->getAdditionalCalculations());
        $rate       = $filter->getCurrencyRate();
        $priceExpr  = new Zend_Db_Expr("ROUND(({$table}.min_price {$additional}) * {$rate}, 2)");

        return array($select, $priceExpr);
    }

    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param int $range
     * @param int $index    the range factor
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Price
     */
    public function applyFilterToCollection($filter, $range, $index)
    {
        list($select, $priceExpr) = $this->_prepareApply($filter);
        $select
            ->where($priceExpr . ' >= ?', ($range * ($index - 1)))
            ->where($priceExpr . ' < ?', ($range * $index));

        return $this;
    }

    /**
     * Load all product prices to algorithm model
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price_Algorithm $algorithm
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return array
     */
    public function loadAllPrices($algorithm, $filter)
    {
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table = $this->_getIndexTableAlias();

        $additional   = join('', $response->getAdditionalCalculations());
        $maxPriceExpr = new Zend_Db_Expr(
            "ROUND(({$table}.min_price {$additional}) * " . $connection->quote($filter->getCurrencyRate()) . ", 2)"
        );

        $select->columns(array($maxPriceExpr))->order("$maxPriceExpr ASC");

        $prices = $connection->fetchCol($select);
        if ($filter->getInterval() && count($prices) <= $filter->getIntervalDivisionLimit()) {
            $algorithm->setPrices(array());
        } else {
            $algorithm->setPrices($prices);
        }

        return $prices;
    }

    /**
     * Apply price range filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Price
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

        list($select, $priceExpr) = $this->_prepareApply($filter);

        if ($from == $to && !empty($to)) {
            $select->where($priceExpr . ' = ?', $from);
        } else {
            if ($from !== '') {
                $select->where($priceExpr . ' >= ?', $from);
            }
            if ($to !== '') {
                $select->where($priceExpr . ' < ?', $to);
            }
        }

        return $this;

    }
}
