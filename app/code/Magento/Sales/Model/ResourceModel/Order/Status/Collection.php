<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Status;

/**
 * Flat sales order status history collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order\Status', 'Magento\Sales\Model\ResourceModel\Order\Status');
    }

    /**
     * Get collection data as options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('status', 'label');
    }

    /**
     * Get collection data as options hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('status', 'label');
    }

    /**
     * Join order states table
     *
     * @return $this
     */
    public function joinStates()
    {
        if (!$this->getFlag('states_joined')) {
            $this->_idFieldName = 'status_state';
            $this->getSelect()->joinLeft(
                ['state_table' => $this->getTable('sales_order_status_state')],
                'main_table.status=state_table.status',
                ['state', 'is_default', 'visible_on_front']
            );
            $this->setFlag('states_joined', true);
        }
        return $this;
    }

    /**
     * Add state code filter to collection
     *
     * @param string $state
     * @return $this
     */
    public function addStateFilter($state)
    {
        $this->joinStates();
        $this->getSelect()->where('state_table.state=?', $state);
        return $this;
    }

    /**
     * Define label order
     *
     * @param string $dir
     * @return $this
     */
    public function orderByLabel($dir = 'ASC')
    {
        $this->getSelect()->order('main_table.label ' . $dir);
        return $this;
    }
}
