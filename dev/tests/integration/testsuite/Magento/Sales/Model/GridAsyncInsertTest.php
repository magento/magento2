<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Grid as AbstractGrid;
use Magento\Sales\Model\ResourceModel\Order\Grid;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Class for testing asynchronous inserts into grid.
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
        $this->grid = $this->objectManager->get(Grid::class);

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
        $order->setStatus('complete');
        $this->orderRepository->save($order);

        $gridRow = $this->getGridRow($order->getEntityId());
        self::assertNotEquals($order->getStatus(), $gridRow['status']);

        $this->gridAsyncInsert->asyncInsert();
        $this->performUpdateAssertions($order);
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
