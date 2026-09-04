<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Grid as AbstractGrid;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Class for testing asynchronous inserts into grid.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridAsyncInsertTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GridAsyncInsert
     */
    private $gridAsyncInsert;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AbstractGrid
     */
    private $grid;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $resourceConnection->getConnection('sales');
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->grid = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Order\Grid');

        $this->gridAsyncInsert = $this->objectManager->create(
            GridAsyncInsert::class,
            [
                'entityGrid' => $this->grid,
            ]
        );
    }

    /**
     * Checks a case when order's grid should be updated asynchronously.
     *
     * @magentoConfigFixture default/dev/grid/async_indexing 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testExecuteAsyncUpdateOrderGrid()
    {
        $order = $this->getOrder('100000001');
        $this->performUpdateAssertions($order);

        // to un-sync main table and grid table need to wait at least one second
        sleep(1);
        // Fixture order is already processing; hold it so main status diverges from grid until async insert.
        $order->setState(Order::STATE_HOLDED);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_HOLDED));
        $this->orderRepository->save($order);

        $gridRow = $this->getGridRow($order->getEntityId());
        self::assertNotEquals($order->getStatus(), $gridRow['status']);

        // refreshBySchedule() only indexes rows whose main.updated_at is at/before UTC (now-1s);
        // the hold save bumps updated_at to "now", so wait until the row is stale enough.
        sleep(2);
        $order = $this->getOrder('100000001');

        $expectedGridUpdatedAt = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->sub(new \DateInterval('PT1S'))
            ->format('Y-m-d H:i:s');
        $this->gridAsyncInsert->asyncInsert();

        $gridRow = $this->getGridRow($order->getEntityId());
        self::assertEquals($order->getStatus(), $gridRow['status']);
        self::assertEquals($expectedGridUpdatedAt, $gridRow['updated_at']);
    }

    /**
     * Loads order entity by provided order increment ID.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId) : OrderInterface
    {
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('increment_id', $incrementId)
            ->create();

        $items = $this->orderRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Gets row from `sales_order_grid` table by order's ID.
     *
     * @param int $entityId
     * @return array
     */
    private function getGridRow(int $entityId) : array
    {
        $tableName = $this->grid->getGridTable();
        $select = $this->connection->select()
            ->from($tableName)
            ->where($tableName . '.entity_id = ?', $entityId);

        return $this->connection->fetchRow($select);
    }

    /**
     * Perform assertions for updating grid test.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function performUpdateAssertions(OrderInterface $order)
    {
        $gridRow = $this->getGridRow($order->getEntityId());

        self::assertEquals($order->getStatus(), $gridRow['status']);
        self::assertEquals($order->getUpdatedAt(), $gridRow['updated_at']);
    }
}
