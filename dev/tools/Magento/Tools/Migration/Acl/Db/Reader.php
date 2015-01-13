<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Db adapter. Reader.
 * Get unique acl resource identifiers from source table
 */
namespace Magento\Tools\Migration\Acl\Db;

class Reader
{
    /**
     * Source table name
     *
     * @var string
     */
    protected $_tableName;

    /**
     * DB adapter
     *
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $_adapter;

    /**
     * @param \Zend_Db_Adapter_Abstract $adapter
     * @param string $tableName source table
     */
    public function __construct(\Zend_Db_Adapter_Abstract $adapter, $tableName)
    {
        $this->_tableName = $tableName;
        $this->_adapter = $adapter;
    }

    /**
     * Get list of unique resource identifiers
     * Format: [resource] => [count items]
     * @return array
     */
    public function fetchAll()
    {
        $select = $this->_adapter->select();
        $select->from(
            $this->_tableName,
            []
        )->columns(
            ['resource_id' => 'resource_id', 'itemsCount' => new \Zend_Db_Expr('count(*)')]
        )->group(
            'resource_id'
        );
        return $this->_adapter->fetchPairs($select);
    }
}
