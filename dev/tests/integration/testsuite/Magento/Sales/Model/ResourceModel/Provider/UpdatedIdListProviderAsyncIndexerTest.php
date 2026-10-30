<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\GridAsyncInsert;
use Magento\Sales\Model\ResourceModel\Grid;
use Magento\Sales\Test\Fixture\TwoOrdersWithOrderItems as TwoOrdersWithOrderItemsFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for ACP2E-4893 / ACQE-9903.
 *
 * Reproduces the production failure mode where orders stopped appearing in the
 * admin Sales Grid after the async indexer cursor had caught up to the current
 * max entity_id. Unit tests mock DB and FlagManager; these tests exercise the
 * real persistence layer and end-to-end grid projection path.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[DbIsolation(true)]
class UpdatedIdListProviderAsyncIndexerTest extends TestCase
{
    private const CURSOR_FLAG_PREFIX = 'sales_grid_async_last_entity_id_';

    private const FIRST_ORDER_INCREMENT_ID = '100000001';

    private const SECOND_ORDER_INCREMENT_ID = '100000002';

    /** @var ObjectManagerInterface */
    private ObjectManagerInterface $objectManager;

    /** @var AdapterInterface */
    private AdapterInterface $connection;

    /** @var OrderRepositoryInterface */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var Grid
     */
    private Grid $grid;

    /** @var UpdatedIdListProvider */
    private UpdatedIdListProvider $updatedIdListProvider;

    /** @var FlagManager */
    private FlagManager $flagManager;

    /** @var GridAsyncInsert */
    private GridAsyncInsert $gridAsyncInsert;

    /** @var string */
    private string $cursorFlagCode;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $resourceConnection->getConnection('sales');
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->grid = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Grid');
        $this->updatedIdListProvider = $this->objectManager->get(UpdatedIdListProvider::class);
        $this->flagManager = $this->objectManager->get(FlagManager::class);
        $this->gridAsyncInsert = $this->objectManager->create(
            GridAsyncInsert::class,
            ['entityGrid' => $this->grid]
        );
        $this->cursorFlagCode = self::CURSOR_FLAG_PREFIX . $this->grid->getGridTable();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if (isset($this->flagManager, $this->cursorFlagCode)) {
            $this->flagManager->deleteFlag($this->cursorFlagCode);
        }
    }

    /**
     * Provider must return a new tail order when the persisted cursor equals the
     * previous max entity_id (not covered by unit tests — uses real SQL + flag).
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'name' => 'Simple Product'], as: 'product'),
        DataFixture(
            TwoOrdersWithOrderItemsFixture::class,
            ['product_sku' => '$product.sku$'],
            as: 'twoOrders'
        ),
    ]
    public function testGetIdsReturnsNewOrderWhenCursorCaughtUpAtPreviousMax(): void
    {
        $firstOrder = $this->loadOrder(self::FIRST_ORDER_INCREMENT_ID);
        $secondOrder = $this->loadOrder(self::SECOND_ORDER_INCREMENT_ID);
        $firstOrderId = (int)$firstOrder->getEntityId();
        $secondOrderId = (int)$secondOrder->getEntityId();

        self::assertGreaterThan(
            $firstOrderId,
            $secondOrderId,
            'precondition: second fixture order has a higher entity_id than the first'
        );

        $this->seedCaughtUpStateWithPendingTailOrder($firstOrderId, $secondOrderId);

        $ids = $this->updatedIdListProvider->getIds('sales_order', 'sales_order_grid');

        self::assertSame(
            [$secondOrderId],
            array_map('intval', $ids),
            'UpdatedIdListProvider must detect the new tail order when cursor equals the previous max entity_id'
        );
    }

    /**
     * End-to-end: async grid insert must project a new order into sales_order_grid
     * when the indexer cursor had already caught up (ACQE-9903 primary scenario).
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'name' => 'Simple Product'], as: 'product'),
        DataFixture(
            TwoOrdersWithOrderItemsFixture::class,
            ['product_sku' => '$product.sku$'],
            as: 'twoOrders'
        ),
    ]
    public function testAsyncInsertSyncsNewOrderWhenGridCursorCaughtUpAtPreviousMax(): void
    {
        $firstOrder = $this->loadOrder(self::FIRST_ORDER_INCREMENT_ID);
        $secondOrder = $this->loadOrder(self::SECOND_ORDER_INCREMENT_ID);
        $firstOrderId = (int)$firstOrder->getEntityId();
        $secondOrderId = (int)$secondOrder->getEntityId();

        $this->seedCaughtUpStateWithPendingTailOrder($firstOrderId, $secondOrderId);

        $this->gridAsyncInsert->asyncInsert();

        $gridRow = $this->getGridRow($secondOrderId);
        self::assertArrayHasKey(
            'increment_id',
            $gridRow,
            'New order must appear in sales_order_grid after one async insert when cursor was caught up'
        );
        self::assertSame(
            $secondOrder->getIncrementId(),
            $gridRow['increment_id'],
            'Grid row must reference the newly placed order'
        );
        self::assertSame(
            $secondOrder->getStatus(),
            $gridRow['status'],
            'Grid row status must match the source order'
        );
    }

    /**
     * Regression: when cursor equals max and no new orders exist, provider must
     * return empty without resetting the persisted cursor backwards.
     *
     * Unit tests assert the empty return value but cannot verify FlagManager
     * persistence across a real getIds() call.
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'name' => 'Simple Product'], as: 'product'),
        DataFixture(
            TwoOrdersWithOrderItemsFixture::class,
            ['product_sku' => '$product.sku$'],
            as: 'twoOrders'
        ),
    ]
    public function testGetIdsPreservesCaughtUpCursorWhenNoNewOrdersExist(): void
    {
        $firstOrder = $this->loadOrder(self::FIRST_ORDER_INCREMENT_ID);
        $secondOrder = $this->loadOrder(self::SECOND_ORDER_INCREMENT_ID);
        $maxEntityId = (int)$secondOrder->getEntityId();

        $this->grid->refresh($firstOrder->getEntityId());
        $this->grid->refresh($secondOrder->getEntityId());
        $this->flagManager->saveFlag($this->cursorFlagCode, $maxEntityId);

        $ids = $this->updatedIdListProvider->getIds('sales_order', 'sales_order_grid');

        self::assertSame([], $ids, 'No work remains when cursor has reached the current tail');
        self::assertSame(
            $maxEntityId,
            (int)$this->flagManager->getFlagData($this->cursorFlagCode),
            'Caught-up cursor must not be reset to a lower value after an empty getIds() call'
        );
    }

    /**
     * Regression: stale cursor stored above current max must not break provider.
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'name' => 'Simple Product'], as: 'product'),
        DataFixture(
            TwoOrdersWithOrderItemsFixture::class,
            ['product_sku' => '$product.sku$'],
            as: 'twoOrders'
        ),
    ]
    public function testGetIdsWithStaleCursorAboveMaxReturnsEmptyWithoutError(): void
    {
        $secondOrder = $this->loadOrder(self::SECOND_ORDER_INCREMENT_ID);
        $maxEntityId = (int)$secondOrder->getEntityId();

        $this->grid->refresh($secondOrder->getEntityId());
        $this->flagManager->saveFlag($this->cursorFlagCode, $maxEntityId + 10000);

        $ids = $this->updatedIdListProvider->getIds('sales_order', 'sales_order_grid');

        self::assertSame(
            [],
            $ids,
            'Stale cursor above max entity_id must short-circuit without SQL errors'
        );
    }

    /**
     * Seeds production-like state: first order indexed, cursor caught up at its
     * entity_id, second (newer) order present in sales_order but absent from grid.
     *
     * @param int $firstOrderId
     * @param int $secondOrderId
     * @return void
     */
    private function seedCaughtUpStateWithPendingTailOrder(int $firstOrderId, int $secondOrderId): void
    {
        $this->grid->purge($firstOrderId);
        $this->grid->purge($secondOrderId);
        $this->grid->refresh($firstOrderId);
        $this->flagManager->saveFlag($this->cursorFlagCode, $firstOrderId);

        self::assertArrayHasKey(
            'entity_id',
            $this->getGridRow($firstOrderId),
            'precondition: first order is indexed in sales_order_grid'
        );
        self::assertEmpty(
            $this->getGridRow($secondOrderId),
            'precondition: new tail order is not yet in sales_order_grid'
        );
        self::assertSame(
            $firstOrderId,
            (int)$this->flagManager->getFlagData($this->cursorFlagCode),
            'precondition: indexer cursor is caught up at the previous max entity_id'
        );
    }

    /**
     * Loads an order entity by increment ID.
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
     * Returns a sales_order_grid row for the given entity ID.
     *
     * @param int $entityId
     * @return array
     */
    private function getGridRow(int $entityId): array
    {
        $select = $this->connection->select()
            ->from($this->grid->getGridTable())
            ->where('entity_id = ?', $entityId);

        return $this->connection->fetchRow($select) ?: [];
    }
}
