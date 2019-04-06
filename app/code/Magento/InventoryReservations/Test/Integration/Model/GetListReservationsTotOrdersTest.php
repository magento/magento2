<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Test\Integration\Model;

use Magento\Catalog\Model\Product;
use Magento\InventoryReservations\Model\ResourceModel\GetListReservationsTotOrder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;

class GetListReservationsTotOrdersTest extends TestCase
{

    /**
     * @magentoDataFixture Magento/Sales/_files/order_new.php
     */
    public function testShouldReturnEmptyArray(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var GetListReservationsTotOrder $subject */
        $subject = $objectManager->get(GetListReservationsTotOrder::class);

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $objectManager->create(OrderRepositoryInterface::class);

        /** @var OrderCollection $orderCollection  */
        $orderCollection = $objectManager->create(OrderCollection::class);
        $orderCollection->addFieldToFilter('increment_id', '100000001');

        /** @var Order $order */
        $order = $orderCollection->getFirstItem();

        $order->setStatus(Order::STATE_COMPLETE);
        $order->setState(Order::STATE_COMPLETE);
        $orderRepository->save($order);

        /** @var array $result */
        $result = $subject->execute();

        $this->assertSame([], $result);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_new.php
     * @magentoDataFixture Magento/Sales/_files/order_shipping.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservations/Test/Integration/_fixtures/broken_reservation.php
     * @magentoDbIsolation enabled
     */
    public function testShouldReturnOneReservationInconsistency(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var GetListReservationsTotOrder $subject */
        $subject = $objectManager->get(GetListReservationsTotOrder::class);

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $objectManager->create(OrderRepositoryInterface::class);

        /** @var OrderCollection $orderCollection  */
        $orderCollection = $objectManager->create(OrderCollection::class);
        $orderCollection->addFieldToFilter('increment_id', '100000001');

        /** @var Order $order */
        $order = $orderCollection->getFirstItem();

        $order->setStatus(Order::STATE_COMPLETE);
        $order->setState(Order::STATE_COMPLETE);
        $orderRepository->save($order);

        /** @var array $result */
        $result = $subject->execute();

        self::assertCount(1, $result);
    }



    /**
     * @throws \Exception
     */
    public function tearDown()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(Registry::class)->register('isSecureArea', true);

        /** @var OrderCollection $orderCollection  */
        $orderCollection = $objectManager->create(OrderCollection::class);
        /** @var Order $order */
        foreach ($orderCollection->getItems() as $order) {
            $order->getResource()->delete($order);
        }

        /** @var ProductCollection $productCollection */
        $productCollection = $objectManager->create(ProductCollection::class);

        /** @var Product $product */
        foreach ($productCollection->getItems() as $product) {
            $product->getResource()->delete($product);
        }
    }
}
