<?php
/**
 * Grouped Products Price Indexer Resource model
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
            array('e' => $this->getTable('catalog_product_entity')),
            'entity_id'
        )->joinLeft(
            array('l' => $this->getTable('catalog_product_link')),
            'e.entity_id = l.product_id AND l.link_type_id=' .
            \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED,
            array()
        )->join(
            array('cg' => $this->getTable('customer_group')),
            '',
            array('customer_group_id')
        );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $minCheckSql = $write->getCheckSql('le.required_options = 0', 'i.min_price', 0);
        $maxCheckSql = $write->getCheckSql('le.required_options = 0', 'i.max_price', 0);
        $select->columns(
            'website_id',
            'cw'
        )->joinLeft(
            array('le' => $this->getTable('catalog_product_entity')),
            'le.entity_id = l.linked_product_id',
            array()
        )->joinLeft(
            array('i' => $table),
            'i.entity_id = l.linked_product_id AND i.website_id = cw.website_id' .
            ' AND i.customer_group_id = cg.customer_group_id',
            array(
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
            )
        )->group(
            array('e.entity_id', 'cg.customer_group_id', 'cw.website_id')
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
            array(
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            )
        );

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }
}
