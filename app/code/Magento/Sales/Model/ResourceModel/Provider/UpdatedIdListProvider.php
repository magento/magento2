<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

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
     * NotSyncedDataProvider constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $select = $this->getConnection()->select()
            ->from($this->getConnection()->getTableName($mainTableName), [$mainTableName.'.entity_id'])
            ->joinLeft(
                [$gridTableName => $this->getConnection()->getTableName($gridTableName)],
                sprintf(
                    '%s.%s = %s.%s',
                    $mainTableName,
                    'entity_id',
                    $gridTableName,
                    'entity_id'
                ),
                []
            )
            ->where($gridTableName.'.entity_id IS NULL');

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
