<?php
/**
 * Grouped Products Price Indexer Resource model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Resource\Product\Indexer\Price;

class Grouped extends \Magento\Catalog\Model\Resource\Product\Indexer\Price\DefaultPrice
{
    /**
     * Reindex temporary (price result data) for all products
     *
     * @throws \Exception
     * @return \Magento\GroupedProduct\Model\Resource\Product\Indexer\Price\Grouped
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->_prepareGroupedProductPriceData();
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
     * @return \Magento\GroupedProduct\Model\Resource\Product\Indexer\Price\Grouped
     */
    public function reindexEntity($entityIds)
    {
        $this->_prepareGroupedProductPriceData($entityIds);

        return $this;
    }

    /**
     * Calculate minimal and maximal prices for Grouped products
     * Use calculated price for relation products
     *
     * @param int|array $entityIds  the parent entity ids limitation
     * @return \Magento\GroupedProduct\Model\Resource\Product\Indexer\Price\Grouped
     */
    protected function _prepareGroupedProductPriceData($entityIds = null)
    {
        if (!$this->hasEntity() && empty($entityIds)) {
            return $this;
        }
        $write = $this->_getWriteAdapter();
        $table = $this->getIdxTable();

        $select = $write->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            'entity_id'
        )->joinLeft(
            ['l' => $this->getTable('catalog_product_link')],
            'e.entity_id = l.product_id AND l.link_type_id=' .
            \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED,
            []
        )->join(
            ['cg' => $this->getTable('customer_group')],
            '',
            ['customer_group_id']
        );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $minCheckSql = $write->getCheckSql('le.required_options = 0', 'i.min_price', 0);
        $maxCheckSql = $write->getCheckSql('le.required_options = 0', 'i.max_price', 0);
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
                'tax_class_id' => $this->_getReadAdapter()->getCheckSql(
                    'MIN(i.tax_class_id) IS NULL',
                    '0',
                    'MIN(i.tax_class_id)'
                ),
                'price' => new \Zend_Db_Expr('NULL'),
                'final_price' => new \Zend_Db_Expr('NULL'),
                'min_price' => new \Zend_Db_Expr('MIN(' . $minCheckSql . ')'),
                'max_price' => new \Zend_Db_Expr('MAX(' . $maxCheckSql . ')'),
                'tier_price' => new \Zend_Db_Expr('NULL'),
                'group_price' => new \Zend_Db_Expr('NULL')
            ]
        )->group(
            ['e.entity_id', 'cg.customer_group_id', 'cw.website_id']
        )->where(
            'e.type_id=?',
            $this->getTypeId()
        );

        if (!is_null($entityIds)) {
            $select->where('l.product_id IN(?)', $entityIds);
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

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }
}
