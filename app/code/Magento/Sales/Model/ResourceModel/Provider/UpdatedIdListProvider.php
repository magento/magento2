<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\FlagManager;
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
     * Number of entity IDs scanned per reconciliation range.
     */
    private const ENTITY_ID_SCAN_RANGE = 10000;

    /**
     * Prefix for persisted per-grid cursor flag.
     */
    private const GRID_CURSOR_FLAG_PREFIX = 'sales_grid_async_last_entity_id_';

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
     * @var FlagManager
     */
    private $flagManager;

    /**
     * NotSyncedDataProvider constructor.
     * @param ResourceConnection $resourceConnection
     * @param IdListBuilder|null $idListQueryBuilder
     * @param FlagManager|null $flagManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ?IdListBuilder $idListQueryBuilder = null,
        ?FlagManager $flagManager = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->idListQueryBuilder = $idListQueryBuilder ?? ObjectManager::getInstance()->get(IdListBuilder::class);
        $this->flagManager = $flagManager ?? ObjectManager::getInstance()->get(FlagManager::class);
    }

    /**
     * @inheritdoc
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $mainTableName = $this->resourceConnection->getTableName($mainTableName);
        $gridTableName = $this->resourceConnection->getTableName($gridTableName);
        $maxEntityId = $this->getMaxEntityId($mainTableName);
        if ($maxEntityId === 0) {
            return [];
        }

        $cursorFlagCode = self::GRID_CURSOR_FLAG_PREFIX . $gridTableName;
        $lastProcessedEntityId = $this->getLastProcessedEntityId($cursorFlagCode, $maxEntityId);
        $scanUntilEntityId = min($lastProcessedEntityId + self::ENTITY_ID_SCAN_RANGE, $maxEntityId);
        $select = $this->idListQueryBuilder->build(
            $mainTableName,
            $gridTableName,
            $lastProcessedEntityId,
            $scanUntilEntityId
        );
        $ids = $this->getConnection()->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
        if (empty($ids)) {
            $this->flagManager->saveFlag($cursorFlagCode, $scanUntilEntityId);
        }

        return $ids;
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

    /**
     * Returns maximum entity ID for source table.
     *
     * @param string $mainTableName
     * @return int
     */
    private function getMaxEntityId(string $mainTableName): int
    {
        $select = $this->getConnection()->select()->from(
            ['main_table' => $mainTableName],
            ['max_entity_id' => new \Zend_Db_Expr('MAX(main_table.entity_id)')]
        );
        $maxEntityId = (int)$this->getConnection()->fetchOne($select);

        return max(0, $maxEntityId);
    }

    /**
     * Returns last processed entity ID for a grid cursor.
     *
     * @param string $cursorFlagCode
     * @param int $maxEntityId
     * @return int
     */
    private function getLastProcessedEntityId(string $cursorFlagCode, int $maxEntityId): int
    {
        $storedCursor = $this->flagManager->getFlagData($cursorFlagCode);
        if (!is_numeric($storedCursor)) {
            return max(0, $maxEntityId - self::ENTITY_ID_SCAN_RANGE);
        }

        $lastProcessedEntityId = (int)$storedCursor;
        if ($lastProcessedEntityId < 0 || $lastProcessedEntityId >= $maxEntityId) {
            return 0;
        }

        return $lastProcessedEntityId;
    }
}
