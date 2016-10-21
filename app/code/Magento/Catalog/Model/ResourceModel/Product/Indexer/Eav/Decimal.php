<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Catalog Product Eav Decimal Attributes Indexer resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Decimal extends AbstractEav
{
    /**
     * Initialize connection and define main index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav_decimal', 'entity_id');
    }

    /**
     * Prepare data index for indexable attributes
     *
     * @param array $entityIds the entity ids limitation
     * @param int $attributeId the attribute id limitation
     * @return $this
     */
    protected function _prepareIndex($entityIds = null, $attributeId = null)
    {
        $connection = $this->getConnection();
        $idxTable = $this->getIdxTable();
        // prepare select attributes
        if ($attributeId === null) {
            $attrIds = $this->_getIndexableAttributes();
        } else {
            $attrIds = [$attributeId];
        }

        if (!$attrIds) {
            return $this;
        }

        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $productValueExpression = $connection->getCheckSql('pds.value_id > 0', 'pds.value', 'pdd.value');

        $select = $connection->select()->from(
            ['pdd' => $this->getTable('catalog_product_entity_decimal')],
            []
        )->join(
            ['cs' => $this->getTable('store')],
            '',
            []
        )->joinLeft(
            ['pds' => $this->getTable('catalog_product_entity_decimal')],
            sprintf(
                'pds.%s = pdd.%s AND pds.attribute_id = pdd.attribute_id'.' AND pds.store_id=cs.store_id',
                $productIdField,
                $productIdField
            ),
            []
        )->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.{$productIdField} = pdd.{$productIdField}",
            []
        )->where(
            'pdd.store_id=?',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        )->where(
            'cs.store_id!=?',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        )->where(
            'pdd.attribute_id IN(?)',
            $attrIds
        )->where(
            "{$productValueExpression} IS NOT NULL"
        )->columns(
            [
                'cpe.entity_id',
                'pdd.attribute_id',
                'cs.store_id',
                'value' => $productValueExpression,
                'source_id' => 'cpe.entity_id',
            ]
        );

        $statusCond = $connection->quoteInto(
            '=?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        $this->_addAttributeToSelect(
            $select,
            'status',
            sprintf(
                'pdd.%s',
                $productIdField
            ),
            'cs.store_id',
            $statusCond
        );

        if ($entityIds !== null) {
            $select->where('cpe.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('cpe.entity_id'),
                'website_field' => new \Zend_Db_Expr('cs.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id'),
            ]
        );

        $query = $select->insertFromSelect($idxTable);
        $connection->query($query);

        return $this;
    }

    /**
     * Retrieve decimal indexable attributes
     *
     * @return array
     */
    protected function _getIndexableAttributes()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['ca' => $this->getTable('catalog_eav_attribute')],
            'attribute_id'
        )->join(
            ['ea' => $this->getTable('eav_attribute')],
            'ca.attribute_id = ea.attribute_id',
            []
        )->where(
            'ea.attribute_code != ?',
            'price'
        )->where(
            $this->_getIndexableAttributesCondition()
        )->where(
            'ea.backend_type=?',
            'decimal'
        );

        return $connection->fetchCol($select);
    }

    /**
     * Retrieve temporary decimal index table name
     *
     * @param string $table
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName('catalog_product_index_eav_decimal');
    }
}
