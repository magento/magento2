<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Inventory\Setup\InstallSchema;
use Magento\InventoryApi\Api\Command\AssignSourcesToStockInterface;
use Magento\InventoryApi\Api\Data\SourceStockLinkInterface;
use Psr\Log\LoggerInterface;


/**
 * @inheritdoc
 */
class AssignSourcesToStock implements AssignSourcesToStockInterface
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $connection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $connection,
        LoggerInterface $logger
    ) {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceIds, $stockId)
    {
        if (empty($sourceIds)) {
            return;
        }

        try {
            $this->executeQuery($sourceIds, $stockId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source Item'), $e);
        }
    }

    /**
     * Assign the source ids a stock.
     *
     * @param int[] $sourceIds
     * @param int $stockId
     *
     * @throws \Exception
     * @return void
     */
    private function executeQuery(array $sourceIds, $stockId)
    {
        /** @var AdapterInterface $connection */
        $connection = $this->connection->getConnection();
        $tableName = $connection->getTableName(InstallSchema::TABLE_NAME_SOURCE_STOCK_LINK);

        $columns = [
            SourceStockLinkInterface::SOURCE_ID,
            SourceStockLinkInterface::STOCK_ID
        ];

        $data = [];
        foreach ($sourceIds as $sourceId) {
            $data[] = [$sourceId, $stockId];
        }

        $connection->insertArray($tableName, $columns, $data);
    }
}
