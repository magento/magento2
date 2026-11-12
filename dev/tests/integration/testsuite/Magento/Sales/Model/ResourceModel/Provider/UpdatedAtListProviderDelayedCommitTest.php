<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;
use Magento\Sales\Model\GridAsyncInsert;
use Magento\Sales\Model\ResourceModel\Grid;
use Magento\Sales\Test\Fixture\TwoOrdersWithOrderItems;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdatedAtListProviderDelayedCommitTest extends TestCase
{
    private const SOURCE_TIMESTAMP = '2026-01-01 12:00:10';
    private const WATERMARK = '2026-01-01 12:00:20';
    private const ORIGINAL_TIMESTAMP = '2026-01-01 12:00:00';

    /** @var AdapterInterface */
    private AdapterInterface $connection;

    /** @var ResourceConnection */
    private ResourceConnection $resourceConnection;

    /** @var LastUpdateTimeCache */
    private LastUpdateTimeCache $lastUpdateTimeCache;

    /** @var array */
    private array $originalWatermarks = [];

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->connection = $this->resourceConnection->getConnection('sales');
        $this->lastUpdateTimeCache = $objectManager->get(LastUpdateTimeCache::class);
        foreach (['sales_order_grid', 'sales_invoice_grid', 'sales_shipment_grid', 'sales_creditmemo_grid'] as $table) {
            $this->originalWatermarks[$table] = $this->lastUpdateTimeCache->get($table);
        }
    }

    protected function tearDown(): void
    {
        try {
            foreach ($this->originalWatermarks as $table => $timestamp) {
                if ($timestamp === null) {
                    $this->lastUpdateTimeCache->remove($table);
                } else {
                    $this->lastUpdateTimeCache->save($table, $timestamp);
                }
            }
        } finally {
            parent::tearDown();
        }
    }

    #[
        Config('dev/grid/async_indexing', '1'),
        DataFixture('Magento/Sales/_files/order_with_invoice_shipment_creditmemo.php'),
        DataProvider('gridDataProvider')
    ]
    public function testRefreshRepairsUpdateOlderThanWatermark(
        string $gridType,
        string $mainTable,
        string $field,
        string $value
    ): void {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Grid $grid */
        $grid = $objectManager->get($gridType);
        $mainTableName = $this->resourceConnection->getTableName($mainTable);
        $gridTableName = $grid->getGridTable();
        $entityId = (int)$this->connection->fetchOne(
            $this->connection->select()->from($mainTableName, 'entity_id')->where('increment_id = ?', '100000111')
        );
        self::assertGreaterThan(0, $entityId);
        $grid->refresh($entityId);
        $originalValue = $this->connection->fetchOne(
            $this->connection->select()->from($gridTableName, $field)->where('entity_id = ?', $entityId)
        );
        self::assertNotSame($value, (string)$originalValue);

        $this->connection->update(
            $mainTableName,
            [$field => $value, 'updated_at' => self::SOURCE_TIMESTAMP],
            ['entity_id = ?' => $entityId]
        );
        $provider = $objectManager->get(UpdatedAtListProvider::class);
        $asyncInsert = $objectManager->create(GridAsyncInsert::class, ['entityGrid' => $grid]);

        foreach ([self::ORIGINAL_TIMESTAMP, self::WATERMARK, null] as $gridTimestamp) {
            $this->connection->update(
                $gridTableName,
                [$field => $originalValue, 'updated_at' => $gridTimestamp],
                ['entity_id = ?' => $entityId]
            );
            $this->lastUpdateTimeCache->save($mainTable . '_grid', self::WATERMARK);
            $asyncInsert->asyncInsert();

            $row = $this->connection->fetchRow(
                $this->connection->select()->from($gridTableName)->where('entity_id = ?', $entityId)
            );
            self::assertSame($value, (string)$row[$field], 'A committed timestamp mismatch must be repaired.');
            self::assertSame(self::SOURCE_TIMESTAMP, $row['updated_at']);
            self::assertNotContains(
                $entityId,
                array_map('intval', $provider->getIdsWithCutoff($mainTable, $gridTableName, self::WATERMARK))
            );
        }
    }

    public static function gridDataProvider(): array
    {
        return [
            'order' => ['Magento\Sales\Model\ResourceModel\Order\Grid', 'sales_order', 'status', 'holded'],
            'invoice' => ['Magento\Sales\Model\ResourceModel\Order\Invoice\Grid', 'sales_invoice', 'state', '3'],
            'shipment' => ['ShipmentGridAggregator', 'sales_shipment', 'shipment_status', '2'],
            'creditmemo' => ['CreditmemoGridAggregator', 'sales_creditmemo', 'state', '3'],
        ];
    }

    #[DataFixture('Magento/Sales/_files/order.php')]
    public function testTimestampMismatchIsDeferredUntilCutoff(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $mainTable = $this->resourceConnection->getTableName('sales_order');
        /** @var Grid $grid */
        $grid = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Grid');
        $entityId = (int)$this->connection->fetchOne(
            $this->connection->select()->from($mainTable, 'entity_id')->where('increment_id = ?', '100000001')
        );
        $grid->refresh($entityId);
        $this->connection->update(
            $mainTable,
            ['updated_at' => self::SOURCE_TIMESTAMP],
            ['entity_id = ?' => $entityId]
        );
        $this->connection->update(
            $grid->getGridTable(),
            ['updated_at' => self::ORIGINAL_TIMESTAMP],
            ['entity_id = ?' => $entityId]
        );
        $this->lastUpdateTimeCache->save('sales_order_grid', self::WATERMARK);
        $provider = $objectManager->get(UpdatedAtListProvider::class);

        self::assertNotContains(
            $entityId,
            array_map(
                'intval',
                $provider->getIdsWithCutoff('sales_order', $grid->getGridTable(), self::ORIGINAL_TIMESTAMP)
            )
        );
        self::assertContains(
            $entityId,
            array_map(
                'intval',
                $provider->getIdsWithCutoff('sales_order', $grid->getGridTable(), self::SOURCE_TIMESTAMP)
            )
        );
    }

    #[
        DbIsolation(false),
        Config('dev/grid/async_indexing', '1'),
        DataFixture(ProductFixture::class, ['sku' => 'async-grid-race-product'], as: 'product'),
        DataFixture(
            TwoOrdersWithOrderItems::class,
            ['product_sku' => '$product.sku$'],
            as: 'orders'
        )
    ]
    public function testRefreshRepairsTransactionCommittedAfterWatermarkAdvances(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $mainTable = $this->resourceConnection->getTableName('sales_order');
        /** @var Grid $grid */
        $grid = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Grid');
        $gridTable = $grid->getGridTable();
        $ids = $this->connection->fetchCol(
            $this->connection->select()->from($mainTable, 'entity_id')
                ->where('increment_id IN (?)', ['100000001', '100000002'])->order('increment_id')
        );
        self::assertCount(2, $ids);
        self::assertSame(
            0,
            $this->connection->getTransactionLevel(),
            'Fixtures must be committed to both connections.'
        );
        foreach ($ids as $entityId) {
            $this->connection->update(
                $mainTable,
                ['status' => 'pending', 'updated_at' => self::ORIGINAL_TIMESTAMP],
                ['entity_id = ?' => $entityId]
            );
            $grid->refresh($entityId);
        }
        $this->lastUpdateTimeCache->save('sales_order_grid', self::ORIGINAL_TIMESTAMP);
        $connectionConfig = $this->connection->getConfig();
        if (!empty($connectionConfig['port'])) {
            $connectionConfig['host'] .= ':' . $connectionConfig['port'];
        }
        unset($connectionConfig['port']);
        $writer = $objectManager->get(ConnectionFactory::class)->create($connectionConfig);
        $writer->beginTransaction();
        try {
            $writer->update(
                $mainTable,
                ['status' => 'holded', 'updated_at' => self::SOURCE_TIMESTAMP],
                ['entity_id = ?' => $ids[0]]
            );
            $this->connection->update(
                $mainTable,
                ['status' => 'holded', 'updated_at' => self::WATERMARK],
                ['entity_id = ?' => $ids[1]]
            );
            $asyncInsert = $objectManager->create(GridAsyncInsert::class, ['entityGrid' => $grid]);
            $asyncInsert->asyncInsert();
            $select = $this->connection->select()->from($gridTable, 'status')->where('entity_id = ?', $ids[0]);
            self::assertSame('pending', $this->connection->fetchOne($select));
            self::assertSame(
                'holded',
                $this->connection->fetchOne(
                    $this->connection->select()->from($gridTable, 'status')->where('entity_id = ?', $ids[1])
                )
            );
            self::assertGreaterThanOrEqual(
                self::WATERMARK,
                $this->lastUpdateTimeCache->get('sales_order_grid'),
                'The other order must advance the watermark before the delayed transaction commits.'
            );
            $writer->commit();
            $asyncInsert->asyncInsert();
            self::assertSame('holded', $this->connection->fetchOne($select));
            self::assertSame(
                self::SOURCE_TIMESTAMP,
                $this->connection->fetchOne(
                    $this->connection->select()->from($gridTable, 'updated_at')->where('entity_id = ?', $ids[0])
                )
            );
        } finally {
            if ($writer->getTransactionLevel() > 0) {
                $writer->rollBack();
            }
            $writer->closeConnection();
        }
    }
}
