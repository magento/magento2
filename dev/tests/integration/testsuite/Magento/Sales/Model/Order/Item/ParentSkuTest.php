<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Item;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Checks the parent_sku extension attribute of order items returned by the web API repositories
 *
 * @magentoAppArea webapi_rest
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ParentSkuTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testOrderRepositoryGetAddsParentSkuToParentItemOnly(): void
    {
        $order = $this->objectManager->create(OrderRepositoryInterface::class)
            ->get((int)$this->getOrder('100000001')->getEntityId());

        $this->assertParentSkus(['configurable' => 'configurable', 'simple' => null], $order->getItems());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testOrderRepositoryGetListAddsParentSku(): void
    {
        $searchCriteria = $this->objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter(OrderInterface::INCREMENT_ID, '100000001')
            ->create();
        $orders = $this->objectManager->create(OrderRepositoryInterface::class)->getList($searchCriteria)->getItems();

        $this->assertCount(1, $orders);
        $this->assertParentSkus(['configurable' => 'configurable', 'simple' => null], current($orders)->getItems());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testOrderItemRepositoryGetAddsParentSkuToSingleParentItem(): void
    {
        $parentItemId = 0;
        foreach ($this->getOrder('100000001')->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $parentItemId = (int)$item->getItemId();
            }
        }

        $item = $this->objectManager->create(OrderItemRepositoryInterface::class)->get($parentItemId);

        $this->assertSame('configurable', $item->getExtensionAttributes()->getParentSku());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testOrderItemRepositoryGetListAddsParentSku(): void
    {
        $searchCriteria = $this->objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter(OrderItemInterface::ORDER_ID, (int)$this->getOrder('100000001')->getEntityId())
            ->create();
        $items = $this->objectManager->create(OrderItemRepositoryInterface::class)->getList($searchCriteria);

        $this->assertParentSkus(['configurable' => 'configurable', 'simple' => null], $items->getItems());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testItemWithoutChildrenHasNoParentSku(): void
    {
        $order = $this->objectManager->create(OrderRepositoryInterface::class)
            ->get((int)$this->getOrder('100000001')->getEntityId());

        $this->assertNotEmpty($order->getItems());
        foreach ($order->getItems() as $item) {
            $this->assertNull($item->getExtensionAttributes()->getParentSku());
        }
    }

    /**
     * Assert the parent_sku of each item, keyed by product type
     *
     * @param array $expected
     * @param OrderItemInterface[] $items
     * @return void
     */
    private function assertParentSkus(array $expected, array $items): void
    {
        $actual = [];
        foreach ($items as $item) {
            $actual[$item->getProductType()] = $item->getExtensionAttributes()->getParentSku();
        }
        ksort($actual);
        ksort($expected);

        $this->assertSame($expected, $actual);
    }

    /**
     * Load an order by its increment ID
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        return $this->objectManager->get(OrderFactory::class)->create()->loadByIncrementId($incrementId);
    }
}
