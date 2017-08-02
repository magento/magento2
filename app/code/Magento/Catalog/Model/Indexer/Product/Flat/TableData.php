<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

use Magento\Framework\App\ResourceConnection;

/**
 * Class TableData
 * @since 2.0.0
 */
class TableData implements TableDataInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected $_connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
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
     * @since 2.0.0
     */
    public function move($flatTable, $flatDropName, $temporaryFlatTableName)
    {
        $connection = $this->_resource->getConnection();
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
