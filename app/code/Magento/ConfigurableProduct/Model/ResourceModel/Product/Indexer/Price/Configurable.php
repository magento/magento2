<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Configurable Products Price Indexer Resource model
 */
class Configurable extends \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
{
    /**
     * @param null|int|array $entityIds
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     */
    protected function reindex($entityIds = null)
    {
        if ($this->hasEntity() || !empty($entityIds)) {
            $this->prepareFinalPriceDataForType($entityIds, $this->getTypeId());
            $this->_applyCustomOption();
            $this->_applyConfigurableOption($entityIds);
            $this->_movePriceDataToIndexTable($entityIds);
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
        return $this->tableStrategy->getTableName('catalog_product_index_price_cfg_opt_agr');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     */
    protected function _getConfigurableOptionPriceTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_cfg_opt');
    }

    /**
     * Prepare table structure for custom option temporary aggregation data
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     */
    protected function _prepareConfigurableOptionAggregateTable()
    {
        $this->getConnection()->delete($this->_getConfigurableOptionAggregateTable());
        return $this;
    }

    /**
     * Prepare table structure for custom option prices data
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     */
    protected function _prepareConfigurableOptionPriceTable()
    {
        $this->getConnection()->delete($this->_getConfigurableOptionPriceTable());
        return $this;
    }

    /**
     * Calculate minimal and maximal prices for configurable product options
     * and apply it to final price
     *
     * @param null|int|array $entityIds
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     */
    protected function _applyConfigurableOption($entityIds = null)
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $connection = $this->getConnection();
        $copTable = $this->_getConfigurableOptionPriceTable();
        $finalPriceTable = $this->_getDefaultFinalPriceTable();
        $linkField = $metadata->getLinkField();

        $this->_prepareConfigurableOptionPriceTable();

        $select = $connection->select()->from(
            ['i' => $this->getIdxTable()],
            []
        )->join(
            ['l' => $this->getTable('catalog_product_super_link')],
            'l.product_id = i.entity_id',
            []
        )->join(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.' . $linkField . ' = l.parent_id',
            []
        )->columns(
            [
                'le.entity_id',
                'customer_group_id',
                'website_id',
                'MIN(final_price)',
                'MAX(final_price)',
                'MIN(tier_price)',

            ]
        )->group(
            ['le.entity_id', 'customer_group_id', 'website_id']
        );
        if ($entityIds !== null) {
            $select->where('le.entity_id IN (?)', $entityIds);
        }

        $query = $select->insertFromSelect($copTable);
        $connection->query($query);

        $table = ['i' => $finalPriceTable];
        $select = $connection->select()->join(
            ['io' => $copTable],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        );
        // adds price of custom option, that was applied in DefaultPrice::_applyCustomOption
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price - i.orig_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price - i.orig_price + io.max_price'),
                'tier_price' => 'io.tier_price',
            ]
        );

        $query = $select->crossUpdateFromSelect($table);
        $connection->query($query);

        $connection->delete($copTable);

        return $this;
    }
}
