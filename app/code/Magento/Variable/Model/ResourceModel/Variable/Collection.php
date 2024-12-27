<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model\ResourceModel\Variable;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Variable\Model\ResourceModel\Variable as ResourceVariable;
use Magento\Variable\Model\Variable as ModelVariable;

/**
 * Custom variable collection
 */
class Collection extends AbstractCollection
{
    /**
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
        $this->_init(ModelVariable::class, ResourceVariable::class);
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
            ['value_table.plain_value', 'value_table.html_value']
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
