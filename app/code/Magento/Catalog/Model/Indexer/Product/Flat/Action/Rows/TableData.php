<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows;

use Magento\Framework\App\ResourceConnection;

/**
 * Class TableData
 * @since 2.0.0
 */
class TableData implements \Magento\Catalog\Model\Indexer\Product\Flat\TableDataInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected $_connection;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     * @since 2.0.0
     */
    protected $_productIndexerHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productIndexerHelper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productIndexerHelper
    ) {
        $this->_resource = $resource;
        $this->_productIndexerHelper = $productIndexerHelper;
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
        if (!$connection->isTableExists($flatTable)) {
            $connection->dropTable($flatDropName);
            $connection->renameTablesBatch([['oldName' => $temporaryFlatTableName, 'newName' => $flatTable]]);
            $connection->dropTable($flatDropName);
        } else {
            $describe = $connection->describeTable($flatTable);
            $columns = $this->_productIndexerHelper->getFlatColumns();
            $columns = array_keys(array_intersect_key($describe, $columns));
            $select = $connection->select();

            $select->from(['tf' => sprintf('%s_tmp_indexer', $flatTable)], $columns);
            $sql = $select->insertFromSelect($flatTable, $columns);
            $connection->query($sql);

            $connection->dropTable(sprintf('%s_tmp_indexer', $flatTable));
        }
    }
}
