<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\ResourceModel\Provider\Query\IdListBuilder;

/**
 * Provides latest updated entities ids list
 */
class UpdatedIdListProvider implements NotSyncedDataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var IdListBuilder
     */
    private $idListQueryBuilder;

    /**
     * NotSyncedDataProvider constructor.
     * @param ResourceConnection $resourceConnection
     * @param IdListBuilder|null $idListQueryBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ?IdListBuilder $idListQueryBuilder = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->idListQueryBuilder = $idListQueryBuilder ?? ObjectManager::getInstance()->get(IdListBuilder::class);
    }

    /**
     * @inheritdoc
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $mainTableName = $this->resourceConnection->getTableName($mainTableName);
        $gridTableName = $this->resourceConnection->getTableName($gridTableName);
        $select = $this->idListQueryBuilder->build($mainTableName, $gridTableName);
        return $this->getConnection()->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
    }

    /**
     * Returns connection.
     *
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection('sales');
        }

        return $this->connection;
    }
}
