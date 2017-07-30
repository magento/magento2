<?php

namespace Magento\Inventory\Model\Indexer\StockItem;


use Magento\Framework\Exception\LocalizedException;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\App\ResourceConnection;

abstract class AbstractAction
{

    const TABLE_NAME_STOCK_ITEM_INDEX = 'inventory_stock_item_index';
    const TABLE_NAME_SOURCE_ITEM = 'inventory_source_item';
    const TABLE_NAME_STOCK_SOURCE_LINK = 'inventory_source_stock_link';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     */
    public function __construct(ResourceConnection $resource, LoggerInterface $logger)
    {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->logger = $logger;
    }

    /**
     * Reindex function for rows stock item rows.
     *
     * @param int[] $sourceItems
     * @throws LocalizedException
     * @return void
     */
    protected function reindexRows(array $sourceItems = [])
    {

        //@todo implement a bunching system in state of memory and performance
        try {
            $indexItems = $this->fetchIndexItems($sourceItems);
            if (is_array($indexItems) && count($indexItems) > 0) {
                $this->cleanIndexTable($sourceItems);
                $this->updateIndexTable($indexItems);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception);
            throw new LocalizedException(__('Something went wrong while reindex stock items'));
        }
    }

    /**
     * @param array $sourceItems
     * @return  void
     */
    private function updateIndexTable(array $sourceItems)
    {
        $columns = ['sku', 'quantity', 'status', 'stock_id'];
        $this->connection->insertArray($this->getIndexTableName(), $columns, $sourceItems);
    }

    /**
     * @param array $sourceItems
     * @return void
     */
    private function cleanIndexTable(array $sourceItems)
    {
        $where = ['stock_id = ?' => '*'];
        if (count($sourceItems) > 0) {
            $where = ['stock_id IN(?)' => implode(',', $sourceItems)];
        }
        $this->connection->delete($this->getIndexTableName(), $where);
    }

    /**
     * @return string
     */
    private function getIndexTableName()
    {
        return $this->connection->getTableName(self::TABLE_NAME_STOCK_ITEM_INDEX);
    }

    /**
     * @return string
     */
    private function getSourceItemTableName()
    {
        return $this->connection->getTableName(self::TABLE_NAME_SOURCE_ITEM);
    }

    /**
     * @return string
     */
    private function getLinkTableName()
    {
        return $this->connection->getTableName(self::TABLE_NAME_STOCK_SOURCE_LINK);
    }

    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param array $sourceItems
     * @return array
     */
    private function fetchIndexItems(array $sourceItems)
    {
        $columns = ['sku', 'quantity', 'status'];
        $select = $this->connection
            ->select()->from(['main' => $this->getSourceItemTableName()], $columns)
            ->joinLeft(
                ['link_table' => $this->getLinkTableName()],
                'main.source_id = link_table.source_id',
                ['stock_id' => 'stock_id']
            );

        if (count($sourceItems) > 0) {
            $select->where('stock_id IN(?)', implode(',', $sourceItems));
        }

        return $this->connection->query($select)->fetchAll();
    }
}
