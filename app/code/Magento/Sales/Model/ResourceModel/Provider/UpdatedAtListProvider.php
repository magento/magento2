<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;

/**
 * Retrieves ID's of not synced by `updated_at` column entities.
 * The result should contain list of entities ID's from the main table which have `updated_at` column greater
 * than in the grid table.
 */
class UpdatedAtListProvider implements NotSyncedDataProviderInterface, NotSyncedDataProviderWithCutoffInterface
{
    private const XML_PATH_LOOKBACK = 'dev/grid/async_indexing_lookback';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var LastUpdateTimeCache
     */
    private $lastUpdateTimeCache;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ResourceConnection $resourceConnection
     * @param LastUpdateTimeCache $lastUpdateTimeCache
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LastUpdateTimeCache $lastUpdateTimeCache,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->connection = $resourceConnection->getConnection('sales');
        $this->resourceConnection = $resourceConnection;
        $this->lastUpdateTimeCache = $lastUpdateTimeCache;
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->sub(new \DateInterval('PT1S'))
            ->format('Y-m-d H:i:s');
        return $this->getIdsWithCutoff($mainTableName, $gridTableName, $cutoff);
    }

    /**
     * @inheritdoc
     */
    public function getIdsWithCutoff($mainTableName, $gridTableName, $cutoff)
    {
        $select = $this->connection->select()
            ->from(['main_table' => $this->resourceConnection->getTableName($mainTableName)], ['main_table.entity_id'])
            ->joinInner(
                ['grid_table' => $this->resourceConnection->getTableName($gridTableName)],
                'main_table.entity_id = grid_table.entity_id AND main_table.updated_at > grid_table.updated_at',
                []
            )->where('main_table.updated_at <= ?', $cutoff);

        $lastUpdatedAt = $this->lastUpdateTimeCache->get($gridTableName);
        if ($lastUpdatedAt) {
            $select->where('main_table.updated_at >= ?', $this->applyLookback($lastUpdatedAt));
        }

        return $this->connection->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
    }

    /**
     * Move the watermark back so rows committed after the previous run's read are still found.
     *
     * The `updated_at` value is set when the UPDATE statement runs, not when its transaction commits,
     * so a row can become visible with a timestamp that is already below the stored watermark.
     *
     * @param string $lastUpdatedAt
     * @return string
     */
    private function applyLookback(string $lastUpdatedAt): string
    {
        $lookback = (int)$this->scopeConfig->getValue(self::XML_PATH_LOOKBACK);
        $watermark = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $lastUpdatedAt,
            new \DateTimeZone('UTC')
        );
        if ($lookback <= 0 || $watermark === false) {
            return $lastUpdatedAt;
        }

        return $watermark->sub(new \DateInterval('PT' . $lookback . 'S'))->format('Y-m-d H:i:s');
    }
}
