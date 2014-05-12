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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product\Indexer\Eav;

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
        $write = $this->_getWriteAdapter();
        $idxTable = $this->getIdxTable();
        // prepare select attributes
        if (is_null($attributeId)) {
            $attrIds = $this->_getIndexableAttributes();
        } else {
            $attrIds = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }

        $productValueExpression = $write->getCheckSql('pds.value_id > 0', 'pds.value', 'pdd.value');
        $select = $write->select()->from(
            array('pdd' => $this->getTable('catalog_product_entity_decimal')),
            array('entity_id', 'attribute_id')
        )->join(
            array('cs' => $this->getTable('store')),
            '',
            array('store_id')
        )->joinLeft(
            array('pds' => $this->getTable('catalog_product_entity_decimal')),
            'pds.entity_id = pdd.entity_id AND pds.attribute_id = pdd.attribute_id' . ' AND pds.store_id=cs.store_id',
            array('value' => $productValueExpression)
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
        );

        $statusCond = $write->quoteInto('=?', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'pdd.entity_id', 'cs.store_id', $statusCond);

        if (!is_null($entityIds)) {
            $select->where('pdd.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            array(
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('pdd.entity_id'),
                'website_field' => new \Zend_Db_Expr('cs.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            )
        );

        $query = $select->insertFromSelect($idxTable);
        $write->query($query);

        return $this;
    }

    /**
     * Retrieve decimal indexable attributes
     *
     * @return array
     */
    protected function _getIndexableAttributes()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            array('ca' => $this->getTable('catalog_eav_attribute')),
            'attribute_id'
        )->join(
            array('ea' => $this->getTable('eav_attribute')),
            'ca.attribute_id = ea.attribute_id',
            array()
        )->where(
            'ea.attribute_code != ?',
            'price'
        )->where(
            $this->_getIndexableAttributesCondition()
        )->where(
            'ea.backend_type=?',
            'decimal'
        );

        return $adapter->fetchCol($select);
    }

    /**
     * Retrieve temporary decimal index table name
     *
     * @param string $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_eav_decimal_idx');
        }
        return $this->getTable('catalog_product_index_eav_decimal_tmp');
    }
}
