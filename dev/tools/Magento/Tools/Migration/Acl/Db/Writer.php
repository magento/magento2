<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db;

class Writer
{
    /**
     * DB adapter
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_adapter;

    /**
     * Source table name
     *
     * @var string
     */
    protected $_tableName;

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $adapter
     * @param string $tableName source table
     */
    public function __construct(\Magento\Framework\DB\Adapter\AdapterInterface $adapter, $tableName)
    {
        $this->_tableName = $tableName;
        $this->_adapter = $adapter;
    }

    /**
     * Update records in database
     *
     * @param string $oldKey
     * @param string $newKey
     * @return void
     */
    public function update($oldKey, $newKey)
    {
        $this->_adapter->update(
            $this->_tableName,
            ['resource_id' => $newKey],
            ['resource_id = ?' => $oldKey]
        );
    }
}
