<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;
use Magento\Sales\Model\GridAsyncInsert;
use Magento\Sales\Model\ResourceModel\Grid;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Covers cutoff eligibility and avoiding rewrites of already-synced equal timestamps.
 *
 * Timestamp comparisons alone cannot detect differing data with identical timestamps.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdatedAtListProviderTiedTimestampTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var GridAsyncInsert */
    private $gridAsyncInsert;

    /** @var AdapterInterface */
    private $connection;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * Order grid indexer (configured virtual type; see Magento\Sales\Model\ResourceModel\Order\Grid in di.xml).
     *
     * @var Grid
     */
    private $grid;

    /** @var LastUpdateTimeCache */
    private $lastUpdateTimeCache;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $resourceConnection->getConnection('sales');
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->grid = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Grid');
        $this->lastUpdateTimeCache = $this->objectManager->get(LastUpdateTimeCache::class);

        $this->gridAsyncInsert = $this->objectManager->create(
            GridAsyncInsert::class,
            ['entityGrid' => $this->grid]
        );
    }

    /**
     * Rows newer than the eligibility cutoff must be deferred.
     *
     * Test sequence:
     *   1. Settle the fixture order (set updated_at to 5s ago) so the
     *      initial cron run will pick it up under the 1s buffer.
     *   2. Run the cron once to populate the grid row.
     *   3. Bump main.updated_at to a clearly-fresh value (now + 5s) to
     *      simulate a write that just happened. Status changes too.
     *   4. Run the cron again.
     *   5. Assert the grid row is unchanged — the cron must have
     *      skipped the in-flight row.
     *
     * @magentoConfigFixture default/dev/grid/async_indexing 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testCronSkipsInFlightRow(): void
    {
        $order = $this->loadOrder('100000001');
        $entityId = (int)$order->getEntityId();

        // Step 1: settle the fixture order — main.updated_at clearly in the
        // past so the initial cron run will pick it up under the 1s buffer.
        $settledTimestamp = (new \DateTimeImmutable('-5 seconds'))->format('Y-m-d H:i:s');
        $this->connection->update(
            $this->connection->getTableName('sales_order'),
            ['updated_at' => $settledTimestamp],
            ['entity_id = ?' => $entityId]
        );

        // Step 2: initial sync — populates the grid row.
        $this->gridAsyncInsert->asyncInsert();
        $beforeCron = $this->getGridRow($entityId);
        self::assertNotEmpty($beforeCron, 'precondition: initial sync created the grid row');

        // Step 3: bump main.updated_at to a clearly-fresh value and change
        // the status. "now + 5s" is guaranteed to be greater than any
        // cron's cutoff (now - 1s), regardless of test-execution timing
        // or PHP/DB clock drift.
        $freshTimestamp = (new \DateTimeImmutable('+5 seconds'))->format('Y-m-d H:i:s');
        $this->connection->update(
            $this->connection->getTableName('sales_order'),
            ['status' => 'complete', 'state' => 'complete', 'updated_at' => $freshTimestamp],
            ['entity_id = ?' => $entityId]
        );

        // Sanity: precondition reflects an in-flight, status-changed order.
        $orderRow = $this->getOrderRow($entityId);
        self::assertSame(
            'complete',
            $orderRow['status'],
            'precondition: order status moved to complete'
        );
        self::assertNotSame(
            $orderRow['status'],
            $beforeCron['status'],
            'precondition: grid status differs from order status'
        );

        // Step 4: run cron. With the 1s buffer, this fresh row must be skipped.
        $this->gridAsyncInsert->asyncInsert();

        // Step 5: assert the grid row is unchanged.
        $afterCron = $this->getGridRow($entityId);
        self::assertSame(
            $beforeCron['status'],
            $afterCron['status'],
            'cron must skip in-flight rows whose main.updated_at is within 1s of cron start; '
            . 'requires the new <= cutoff filter in '
            . 'Magento/Sales/Model/ResourceModel/Provider/UpdatedAtListProvider.php'
        );
    }

    /**
     * Regression guard against over-fetch.
     *
     * When sales_order and sales_order_grid are genuinely in sync — same
     * status, tied updated_at — the cron must NOT touch the grid row.
     * Selecting equal timestamps would repeatedly re-project already-synced rows.
     *
     * @magentoConfigFixture default/dev/grid/async_indexing 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testCronDoesNotResyncOrderAlreadyInSyncWithTiedTimestamps(): void
    {
        $order = $this->loadOrder('100000001');
        $entityId = (int)$order->getEntityId();
        $tiedTimestamp = $order->getUpdatedAt();

        // Step 0: initial sync — grid row matches order.
        $this->gridAsyncInsert->asyncInsert();

        // Force the in-sync state with explicitly tied timestamps and
        // worst-case cache value. Status remains the same on both sides.
        $this->connection->update(
            $this->connection->getTableName('sales_order'),
            ['updated_at' => $tiedTimestamp],
            ['entity_id = ?' => $entityId]
        );
        $this->connection->update(
            $this->grid->getGridTable(),
            ['updated_at' => $tiedTimestamp],
            ['entity_id = ?' => $entityId]
        );
        $this->lastUpdateTimeCache->save($this->grid->getGridTable(), $tiedTimestamp);

        // Sanity: tied timestamps, matching status (in sync).
        $orderRow = $this->getOrderRow($entityId);
        $beforeCron = $this->getGridRow($entityId);
        self::assertSame(
            $orderRow['updated_at'],
            $beforeCron['updated_at'],
            'precondition: timestamps tied'
        );
        self::assertSame(
            $orderRow['status'],
            $beforeCron['status'],
            'precondition: statuses match — grid is in sync'
        );

        // Act.
        $this->gridAsyncInsert->asyncInsert();

        // Assert: grid row byte-identical to before the cron run.
        $afterCron = $this->getGridRow($entityId);
        self::assertSame(
            $beforeCron,
            $afterCron,
            'in-sync grid row should not be touched by the cron — guards against over-fetch'
        );
    }

    /**
     * Smoke test for blast-radius safety.
     *
     * UpdatedAtListProvider::getIds() is shared by four cron jobs that
     * operate on different (main_table, grid_table) pairs:
     *   - sales_order      / sales_order_grid
     *   - sales_invoice    / sales_invoice_grid
     *   - sales_shipment   / sales_shipment_grid
     *   - sales_creditmemo / sales_creditmemo_grid
     *
     * This test exercises every pair through the provider and asserts no SQL
     * crash — a regression guard against future patch changes accidentally
     * breaking non-order grids.
     *
     * @return void
     */
    public function testGetIdsExecutesForEveryGridWithoutSqlError(): void
    {
        $this->expectNotToPerformAssertions();

        /** @var NotSyncedDataProviderInterface $provider */
        $provider = $this->objectManager->get(NotSyncedDataProviderInterface::class);

        $pairs = [
            ['sales_order',      'sales_order_grid'],
            ['sales_invoice',    'sales_invoice_grid'],
            ['sales_shipment',   'sales_shipment_grid'],
            ['sales_creditmemo', 'sales_creditmemo_grid'],
        ];

        foreach ($pairs as [$mainTable, $gridTable]) {
            try {
                $provider->getIds($mainTable, $gridTable);
            } catch (\Throwable $e) {
                self::fail(
                    "UpdatedAtListProvider::getIds threw for ({$mainTable}, {$gridTable}): "
                    . $e->getMessage()
                );
            }
        }
    }

    /**
     * Loads an order entity by its increment ID.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function loadOrder(string $incrementId): OrderInterface
    {
        $criteria = $this->objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('increment_id', $incrementId)
            ->create();
        $items = $this->orderRepository->getList($criteria)->getItems();
        self::assertNotEmpty(
            $items,
            sprintf('Order with increment_id %s not found.', $incrementId)
        );

        return array_values($items)[0];
    }

    /**
     * @param int $entityId
     * @return array
     */
    private function getGridRow(int $entityId): array
    {
        $tableName = $this->grid->getGridTable();
        $select = $this->connection->select()
            ->from($tableName)
            ->where('entity_id = ?', $entityId);

        return $this->connection->fetchRow($select) ?: [];
    }

    /**
     * @param int $entityId
     * @return array
     */
    private function getOrderRow(int $entityId): array
    {
        $tableName = $this->connection->getTableName('sales_order');
        $select = $this->connection->select()
            ->from($tableName, ['entity_id', 'status', 'state', 'updated_at'])
            ->where('entity_id = ?', $entityId);

        return $this->connection->fetchRow($select) ?: [];
    }
}
