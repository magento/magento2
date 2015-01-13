<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * Class TableData
 */
class TableData implements TableDataInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * Move data from temporary tables to flat
     *
     * @param string $flatTable
     * @param string $flatDropName
     * @param string $temporaryFlatTableName
     * @return void
     */
    public function move($flatTable, $flatDropName, $temporaryFlatTableName)
    {
        $connection = $this->_resource->getConnection('write');
        $renameTables = [];

        if ($connection->isTableExists($flatTable)) {
            $renameTables[] = ['oldName' => $flatTable, 'newName' => $flatDropName];
        }
        $renameTables[] = ['oldName' => $temporaryFlatTableName, 'newName' => $flatTable];

        $connection->dropTable($flatDropName);
        $connection->renameTablesBatch($renameTables);
        $connection->dropTable($flatDropName);
    }
}
