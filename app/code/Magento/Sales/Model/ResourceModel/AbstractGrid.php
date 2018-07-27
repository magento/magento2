<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AbstractGrid
 */
abstract class AbstractGrid extends AbstractDb implements GridInterface
{
    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $gridTableName;

    /**
     * @var string
     */
    protected $orderTableName = 'sales_order';

    /**
     * @var string
     */
    protected $addressTableName = 'sales_order_address';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        //
    }

    /**
     * Returns connection
     *
     * @todo: make method protected
     * @return AdapterInterface
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->_resources->getConnection('sales');
        }
        return $this->connection;
    }

    /**
     * Returns grid table name
     *
     * @return string
     */
    public function getGridTable()
    {
        return $this->getTable($this->gridTableName);
    }
    /**
     * Purge grid row
     *
     * @param int|string $value
     * @param null|string $field
     * @return int
     */
    public function purge($value, $field = null)
    {
        return $this->getConnection()->delete(
            $this->getTable($this->gridTableName),
            [($field ?: 'entity_id') . ' = ?' => $value]
        );
    }

    /**
     * Returns update time of the last row in the grid.
     *
     * If there are no rows in the grid, default value will be returned.
     *
     * @param string $default
     * @return string
     */
    protected function getLastUpdatedAtValue($default = '0000-00-00 00:00:00')
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable($this->gridTableName), ['updated_at'])
            ->order('updated_at DESC')
            ->limit(1);

        $row = $this->getConnection()->fetchRow($select);

        return isset($row['updated_at']) ? $row['updated_at'] : $default;
    }
}
