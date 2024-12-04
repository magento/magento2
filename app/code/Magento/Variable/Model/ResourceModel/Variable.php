<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Model\ResourceModel;

/**
 * Custom variable resource model
 */
class Variable extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('variable', 'variable_id');
    }

    /**
     * Load variable by code
     *
     * @param \Magento\Variable\Model\Variable $object
     * @param string $code
     * @return $this
     */
    public function loadByCode(\Magento\Variable\Model\Variable $object, $code)
    {
        if ($result = $this->getVariableByCode($code, true, $object->getStoreId())) {
            $object->setData($result);
        }
        return $this;
    }

    /**
     * Retrieve variable data by code
     *
     * @param string $code
     * @param bool $withValue
     * @param integer $storeId
     * @return array
     */
    public function getVariableByCode($code, $withValue = false, $storeId = 0)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            $this->getMainTable() . '.code = ?',
            $code
        );
        if ($withValue) {
            $this->_addValueToSelect($select, $storeId);
        }
        return $this->getConnection()->fetchRow($select);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);
        if ($object->getUseDefaultValue()) {
            /*
             * remove store value
             */
            $this->getConnection()->delete(
                $this->getTable('variable_value'),
                ['variable_id = ?' => $object->getId(), 'store_id = ?' => $object->getStoreId()]
            );
        } else {
            $data = [
                'variable_id' => $object->getId(),
                'store_id' => $object->getStoreId(),
                'plain_value' => $object->getPlainValue(),
                'html_value' => $object->getHtmlValue(),
            ];
            $data = $this->_prepareDataForTable(
                new \Magento\Framework\DataObject($data),
                $this->getTable('variable_value')
            );
            $this->getConnection()->insertOnDuplicate(
                $this->getTable('variable_value'),
                $data,
                ['plain_value', 'html_value']
            );
        }
        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $this->_addValueToSelect($select, $object->getStoreId());
        return $select;
    }

    /**
     * Add variable store and default value to select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param integer $storeId
     * @return \Magento\Variable\Model\ResourceModel\Variable
     */
    protected function _addValueToSelect(
        \Magento\Framework\DB\Select $select,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $connection = $this->getConnection();
        $ifNullPlainValue = $connection->getCheckSql(
            'store.plain_value IS NULL',
            'def.plain_value',
            'store.plain_value'
        );
        $ifNullHtmlValue = $connection->getCheckSql('store.html_value IS NULL', 'def.html_value', 'store.html_value');

        $select->joinLeft(
            ['def' => $this->getTable('variable_value')],
            'def.variable_id = ' . $this->getMainTable() . '.variable_id AND def.store_id = 0',
            []
        )->joinLeft(
            ['store' => $this->getTable('variable_value')],
            'store.variable_id = def.variable_id AND store.store_id = ' . $connection->quote($storeId),
            []
        )->columns(
            [
                'plain_value' => $ifNullPlainValue,
                'html_value' => $ifNullHtmlValue,
                'store_plain_value' => 'store.plain_value',
                'store_html_value' => 'store.html_value',
            ]
        );

        return $this;
    }
}
