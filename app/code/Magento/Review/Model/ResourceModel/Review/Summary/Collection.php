<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel\Review\Summary;

/**
 * Review summery collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Review\Summary', 'Magento\Review\Model\ResourceModel\Review\Summary');
    }

    /**
     * Add entity filter
     *
     * @param int|string $entityId
     * @param int $entityType
     * @return $this
     */
    public function addEntityFilter($entityId, $entityType = 1)
    {
        $this->_select->where('entity_pk_value IN(?)', $entityId)->where('entity_type = ?', $entityType);
        return $this;
    }

    /**
     * Add store filter
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this->_select->where('store_id = ?', $storeId);
        return $this;
    }
}
