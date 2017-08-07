<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Provides latest updated entities ids list
 * @since 2.2.0
 */
class UpdatedIdListProvider implements NotSyncedDataProviderInterface
{
    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     * @since 2.2.0
     */
    private $connection;

    /**
     * NotSyncedDataProvider constructor.
     * @param ResourceConnection $resourceConnection
     * @since 2.2.0
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $select = $this->getConnection()->select()
            ->from($this->getConnection()->getTableName($mainTableName), [$mainTableName . '.entity_id'])
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
            ->where($gridTableName . '.entity_id IS NULL');

        return $this->getConnection()->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
    }

    /**
     * Returns connection.
     *
     * @return AdapterInterface
     * @since 2.2.0
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection('sales');
        }

        return $this->connection;
    }
}
