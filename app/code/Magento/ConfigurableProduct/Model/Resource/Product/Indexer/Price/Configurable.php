<?php
/**
 * Configurable Products Price Indexer Resource model
 *
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
namespace Magento\ConfigurableProduct\Model\Resource\Product\Indexer\Price;

class Configurable extends \Magento\Catalog\Model\Resource\Product\Indexer\Price\DefaultPrice
{
    /**
     * Reindex temporary (price result data) for all products
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->reindex();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Reindex temporary (price result data) for defined product(s)
     *
     * @param int|array $entityIds
     * @return \Magento\ConfigurableProduct\Model\Resource\Product\Indexer\Price\Configurable
     */
    public function reindexEntity($entityIds)
    {
        $this->reindex($entityIds);
        return $this;
    }

    /**
     * @param null|int|array $entityIds
     * @return \Magento\ConfigurableProduct\Model\Resource\Product\Indexer\Price\Configurable
     */
    protected function reindex($entityIds = null)
    {
        if ($this->hasEntity() || !empty($entityIds)) {
            $this->_prepareFinalPriceData($entityIds);
            $this->_applyCustomOption();
            $this->_applyConfigurableOption();
            $this->_movePriceDataToIndexTable();
        }
        return $this;
    }

    /**
     * Retrieve table name for custom option temporary aggregation data
     *
     * @return string
     */
    protected function _getConfigurableOptionAggregateTable()
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_price_cfg_opt_agr_idx');
        }
        return $this->getTable('catalog_product_index_price_cfg_opt_agr_tmp');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     */
    protected function _getConfigurableOptionPriceTable()
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_price_cfg_opt_idx');
        }
        return $this->getTable('catalog_product_index_price_cfg_opt_tmp');
    }

    /**
     * Prepare table structure for custom option temporary aggregation data
     *
     * @return \Magento\ConfigurableProduct\Model\Resource\Product\Indexer\Price\Configurable
     */
    protected function _prepareConfigurableOptionAggregateTable()
    {
        $this->_getWriteAdapter()->delete($this->_getConfigurableOptionAggregateTable());
        return $this;
    }

    /**
     * Prepare table structure for custom option prices data
     *
     * @return \Magento\ConfigurableProduct\Model\Resource\Product\Indexer\Price\Configurable
     */
    protected function _prepareConfigurableOptionPriceTable()
    {
        $this->_getWriteAdapter()->delete($this->_getConfigurableOptionPriceTable());
        return $this;
    }

    /**
     * Calculate minimal and maximal prices for configurable product options
     * and apply it to final price
     *
     * @return \Magento\ConfigurableProduct\Model\Resource\Product\Indexer\Price\Configurable
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _applyConfigurableOption()
    {
        $write = $this->_getWriteAdapter();
        $coaTable = $this->_getConfigurableOptionAggregateTable();
        $copTable = $this->_getConfigurableOptionPriceTable();

        $this->_prepareConfigurableOptionAggregateTable();
        $this->_prepareConfigurableOptionPriceTable();

        $select = $write->select()->from(
            array('i' => $this->_getDefaultFinalPriceTable()),
            array()
        )->join(
            array('l' => $this->getTable('catalog_product_super_link')),
            'l.parent_id = i.entity_id',
            array('parent_id', 'product_id')
        )->columns(
            array('customer_group_id', 'website_id'),
            'i'
        )->join(
            array('a' => $this->getTable('catalog_product_super_attribute')),
            'l.parent_id = a.product_id',
            array()
        )->join(
            array('cp' => $this->getTable('catalog_product_entity_int')),
            'l.product_id = cp.entity_id AND cp.attribute_id = a.attribute_id AND cp.store_id = 0',
            array()
        )->joinLeft(
            array('apd' => $this->getTable('catalog_product_super_attribute_pricing')),
            'a.product_super_attribute_id = apd.product_super_attribute_id' .
            ' AND apd.website_id = 0 AND cp.value = apd.value_index',
            array()
        )->joinLeft(
            array('apw' => $this->getTable('catalog_product_super_attribute_pricing')),
            'a.product_super_attribute_id = apw.product_super_attribute_id' .
            ' AND apw.website_id = i.website_id AND cp.value = apw.value_index',
            array()
        )->join(
            array('le' => $this->getTable('catalog_product_entity')),
            'le.entity_id = l.product_id',
            array()
        )->where(
            'le.required_options=0'
        )->group(
            array('l.parent_id', 'i.customer_group_id', 'i.website_id', 'l.product_id')
        );

        $priceExpression = $write->getCheckSql('apw.value_id IS NOT NULL', 'apw.pricing_value', 'apd.pricing_value');
        $percentExpr = $write->getCheckSql('apw.value_id IS NOT NULL', 'apw.is_percent', 'apd.is_percent');
        $roundExpr = "ROUND(i.price * ({$priceExpression} / 100), 4)";
        $roundPriceExpr = $write->getCheckSql("{$percentExpr} = 1", $roundExpr, $priceExpression);
        $priceColumn = $write->getCheckSql("{$priceExpression} IS NULL", '0', $roundPriceExpr);
        $priceColumn = new \Zend_Db_Expr("SUM({$priceColumn})");

        $tierPrice = $priceExpression;
        $tierRoundPriceExp = $write->getCheckSql("{$percentExpr} = 1", $roundExpr, $tierPrice);
        $tierPriceExp = $write->getCheckSql("{$tierPrice} IS NULL", '0', $tierRoundPriceExp);
        $tierPriceColumn = $write->getCheckSql("MIN(i.tier_price) IS NOT NULL", "SUM({$tierPriceExp})", 'NULL');

        $groupPrice = $priceExpression;
        $groupRoundPriceExp = $write->getCheckSql("{$percentExpr} = 1", $roundExpr, $groupPrice);
        $groupPriceExp = $write->getCheckSql("{$groupPrice} IS NULL", '0', $groupRoundPriceExp);
        $groupPriceColumn = $write->getCheckSql("MIN(i.group_price) IS NOT NULL", "SUM({$groupPriceExp})", 'NULL');

        $select->columns(
            array('price' => $priceColumn, 'tier_price' => $tierPriceColumn, 'group_price' => $groupPriceColumn)
        );

        $query = $select->insertFromSelect($coaTable);
        $write->query($query);

        $select = $write->select()->from(
            array($coaTable),
            array(
                'parent_id',
                'customer_group_id',
                'website_id',
                'MIN(price)',
                'MAX(price)',
                'MIN(tier_price)',
                'MIN(group_price)'
            )
        )->group(
            array('parent_id', 'customer_group_id', 'website_id')
        );

        $query = $select->insertFromSelect($copTable);
        $write->query($query);

        $table = array('i' => $this->_getDefaultFinalPriceTable());
        $select = $write->select()->join(
            array('io' => $copTable),
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            array()
        );
        $select->columns(
            array(
                'min_price' => new \Zend_Db_Expr('i.min_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price + io.max_price'),
                'tier_price' => $write->getCheckSql(
                    'i.tier_price IS NOT NULL',
                    'i.tier_price + io.tier_price',
                    'NULL'
                ),
                'group_price' => $write->getCheckSql(
                    'i.group_price IS NOT NULL',
                    'i.group_price + io.group_price',
                    'NULL'
                )
            )
        );

        $query = $select->crossUpdateFromSelect($table);
        $write->query($query);

        $write->delete($coaTable);
        $write->delete($copTable);

        return $this;
    }
}
