<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Zend_Db;

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

        $select = $connection->select()
            ->union(
                [
                    $this->getSelect($attrIds, $entityIds),
                    $this->getSelect($attrIds, $entityIds, true)
                ]
            );

        $query = $select->insertFromSelect($idxTable);
        $connection->query($query);

        return $this;
    }

    /**
     * Generate select with attribute values
     *
     * @param array $attributeIds
     * @param array|null $entityIds
     * @param bool $storeValuesOnly
     * @return Select
     * @throws \Exception
     */
    private function getSelect(array $attributeIds, ?array $entityIds, bool $storeValuesOnly = false): Select
    {
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['cs' => $this->getTable('store')],
                []
            );
        if ($storeValuesOnly) {
            $select->join(
                ['pdd' => $this->getTable('catalog_product_entity_decimal')],
                'pdd.store_id = cs.store_id',
                []
            );
            $productValueExpression = 'pdd.value';
        } else {
            $select->join(
                ['pdd' => $this->getTable('catalog_product_entity_decimal')],
                'pdd.store_id=' . Store::DEFAULT_STORE_ID,
                []
            )->joinLeft(
                ['pds' => $this->getTable('catalog_product_entity_decimal')],
                sprintf(
                    'pds.%s = pdd.%s AND pds.attribute_id = pdd.attribute_id' . ' AND pds.store_id=cs.store_id',
                    $productIdField,
                    $productIdField
                ),
                []
            );
            $productValueExpression = $connection->getCheckSql('pds.value_id > 0', 'pds.value', 'pdd.value');
        }
        $select->joinLeft(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.{$productIdField} = pdd.{$productIdField}",
            []
        )->where(
            'cs.store_id!=?',
            Store::DEFAULT_STORE_ID
        )->where(
            'pdd.attribute_id IN(?)',
            $attributeIds,
            Zend_Db::INT_TYPE
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
            $select->where(
                'cpe.entity_id IN(?)',
                $entityIds,
                Zend_Db::INT_TYPE
            );
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

        return $select;
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
