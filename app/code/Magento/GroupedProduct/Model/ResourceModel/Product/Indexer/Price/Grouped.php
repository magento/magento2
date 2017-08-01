<?php
/**
 * Grouped Products Price Indexer Resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class \Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price\Grouped
 *
 * @since 2.0.0
 */
class Grouped extends DefaultPrice implements GroupedInterface
{
    /**
     * Prefix for temporary table support.
     */
    const TRANSIT_PREFIX = 'transit_';

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function reindex($entityIds = null)
    {
        $this->_prepareGroupedProductPriceData($entityIds);
    }

    /**
     * Calculate minimal and maximal prices for Grouped products
     * Use calculated price for relation products
     *
     * @param int|array $entityIds  the parent entity ids limitation
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareGroupedProductPriceData($entityIds = null)
    {
        if (!$this->hasEntity() && empty($entityIds)) {
            return $this;
        }

        $connection = $this->getConnection();
        $table = $this->getIdxTable();

        if (!$this->tableStrategy->getUseIdxTable()) {
            $additionalIdxTable = $connection->getTableName(self::TRANSIT_PREFIX . $this->getIdxTable());
            $connection->createTemporaryTableLike($additionalIdxTable, $table);
            $query = $connection->insertFromSelect(
                $this->_prepareGroupedProductPriceDataSelect($entityIds),
                $additionalIdxTable,
                []
            );
            $connection->query($query);

            $select = $connection->select()->from($additionalIdxTable);
            $query = $connection->insertFromSelect(
                $select,
                $table,
                [],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $connection->query($query);
            $connection->dropTemporaryTable($additionalIdxTable);
        } else {
            $query = $this->_prepareGroupedProductPriceDataSelect($entityIds)->insertFromSelect($table);
            $connection->query($query);
        }
        return $this;
    }

    /**
     * Prepare data index select for Grouped products prices
     *
     * @param int|array $entityIds  the parent entity ids limitation
     * @return \Magento\Framework\DB\Select
     * @since 2.2.0
     */
    protected function _prepareGroupedProductPriceDataSelect($entityIds = null)
    {
        $connection = $this->getConnection();
        $table = $this->getIdxTable();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            'entity_id'
        )->joinLeft(
            ['l' => $this->getTable('catalog_product_link')],
            'e.' . $linkField . ' = l.product_id AND l.link_type_id=' .
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED,
            []
        )->join(
            ['cg' => $this->getTable('customer_group')],
            '',
            ['customer_group_id']
        );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $minCheckSql = $connection->getCheckSql('le.required_options = 0', 'i.min_price', 0);
        $maxCheckSql = $connection->getCheckSql('le.required_options = 0', 'i.max_price', 0);
        $select->columns(
            'website_id',
            'cw'
        )->joinLeft(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.entity_id = l.linked_product_id',
            []
        )->joinLeft(
            ['i' => $table],
            'i.entity_id = l.linked_product_id AND i.website_id = cw.website_id' .
            ' AND i.customer_group_id = cg.customer_group_id',
            [
                'tax_class_id' => $this->getConnection()->getCheckSql(
                    'MIN(i.tax_class_id) IS NULL',
                    '0',
                    'MIN(i.tax_class_id)'
                ),
                'price' => new \Zend_Db_Expr('NULL'),
                'final_price' => new \Zend_Db_Expr('NULL'),
                'min_price' => new \Zend_Db_Expr('MIN(' . $minCheckSql . ')'),
                'max_price' => new \Zend_Db_Expr('MAX(' . $maxCheckSql . ')'),
                'tier_price' => new \Zend_Db_Expr('NULL'),
            ]
        )->group(
            ['e.entity_id', 'cg.customer_group_id', 'cw.website_id']
        )->where(
            'e.type_id=?',
            $this->getTypeId()
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'catalog_product_prepare_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            ]
        );
        return $select;
    }
}
