<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model\ResourceModel\Variable;

/**
 * Custom variable collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store Id
     *
     * @var int
     */
    protected $_storeId = 0;

    /**
     *  Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\Variable\Model\Variable::class, \Magento\Variable\Model\ResourceModel\Variable::class);
    }

    /**
     * Setter
     *
     * @param integer $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Getter
     *
     * @return integer
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Add store values to result
     *
     * @return $this
     */
    public function addValuesToResult()
    {
        $this->getSelect()->join(
            ['value_table' => $this->getTable('variable_value')],
            'value_table.variable_id = main_table.variable_id',
            ['value_table.value']
        );
        $this->addFieldToFilter('value_table.store_id', ['eq' => $this->getStoreId()]);
        return $this;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('code', 'name');
    }
}
