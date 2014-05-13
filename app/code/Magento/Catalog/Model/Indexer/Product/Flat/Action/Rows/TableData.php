<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows;

/**
 * Class TableData
 */
class TableData implements \Magento\Catalog\Model\Indexer\Product\Flat\TableDataInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_productIndexerHelper;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productIndexerHelper
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
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
     */
    public function move($flatTable, $flatDropName, $temporaryFlatTableName)
    {
        $connection = $this->_resource->getConnection('write');
        if (!$connection->isTableExists($flatTable)) {
            $connection->dropTable($flatDropName);
            $connection->renameTablesBatch(array('oldName' => $temporaryFlatTableName, 'newName' => $flatTable));
            $connection->dropTable($flatDropName);
        } else {
            $describe = $connection->describeTable($flatTable);
            $columns = $this->_productIndexerHelper->getFlatColumns();
            $columns = array_keys(array_intersect_key($describe, $columns));
            $select = $connection->select();

            $select->from(array('tf' => sprintf('%s_tmp_indexer', $flatTable)), $columns);
            $sql = $select->insertFromSelect($flatTable, $columns);
            $connection->query($sql);

            $connection->dropTable(sprintf('%s_tmp_indexer', $flatTable));
        }
    }
}
